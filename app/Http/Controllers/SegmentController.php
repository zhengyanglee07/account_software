<?php

namespace App\Http\Controllers;

use App\AccountOauth;
use App\AdAudience;
use App\Currency;
use App\Email;
use App\EcommercePage;
use App\funnel;
use App\LandingPageForm;
use App\peopleCustomFieldName;
use App\ProcessedContact;
use App\ProcessedTag;
use App\Segment;
use App\Services\AdAudiences\GoogleCustomerMatch;
use App\Services\RefKeyService;
use App\Services\SegmentService;
use App\SocialMediaProvider;
use App\Traits\AuthAccountTrait;
use App\UsersProduct;
use App\Utils\SegmentFormatters\SegmentFormatterFactory;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class SegmentController extends Controller
{
    use AuthAccountTrait;

    private $segmentService;

    public function __construct(SegmentService $segmentService)
    {
        $this->segmentService = $segmentService;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index()
    {
        $account = Auth::user()->currentAccount();
        $accountRandomId = $account->accountRandomId;

        $displaySegments = [];
        $finalConditionData = [];

        $availableSocialMedias = AccountOauth::where('account_id', $account->id)
            ->with('socialMediaProvider')
            ->get()
            ->pluck('socialMediaProvider.name');

        foreach ($account->segments as $segment) {
            $conditionFilters = $segment->conditions;
            $peopleCount = $segment->contacts()->count();

            foreach ($conditionFilters as $ORCondition) {
                $finalConditionData[] = "OR";

                foreach ($ORCondition as $andIdx => $ANDCondition) {
                    $combineSubConditionData = [];
                    $conditionName = $ANDCondition['name'];

                    foreach ($ANDCondition['subConditions'] as $subCondition) {
                        $combineSubConditionData[] = SegmentFormatterFactory
                            ::createFormatter($conditionName)
                            ->format($subCondition);
                    }

                    // TODO: try whether can lowercase > one word name or not
                    //       e.g. Total Sales -> Total sales
                    $finalConditionData[] = ($andIdx > 0 ? 'AND ' : '')
                        . $conditionName
                        . " "
                        . implode($combineSubConditionData);
                }
            }

            $storeData = $segment;
            $storeData['people'] = $peopleCount;
            $storeData['used_social_medias'] = $segment->adAudiences->map(function ($adAudience) {
                return [
                    'name' => $adAudience->accountOauth->socialMediaProvider->name,
                    'isSynced' => $adAudience->sync_status === 'synced'
                ];
            });
            $storeData['formattedConditionFilters'] = $finalConditionData;

            $displaySegments[] = $storeData;
            $finalConditionData = [];
        }

        $isProd = app()->environment('production');
        return Inertia::render('people/pages/Segments', compact(
            'accountRandomId',
            'availableSocialMedias',
            'displaySegments',
            'isProd',
        ));
    }

    /**
     * Return segments' name for a given contact
     *
     * @param $contactRandomId
     * @return \Illuminate\Http\JsonResponse
     */
    public function contactSegmentNames($contactRandomId): JsonResponse
    {
        $segments = Segment::where('account_id', $this->getCurrentAccountId())->get();

        if ($segments->count() === 0) {
            return response()->json([
                'segmentNames' => []
            ]);
        }

        $contact = ProcessedContact::where('contactRandomId', $contactRandomId)->firstOrFail();
        $segmentNames = $this->segmentService->getContactSegmentNames($contact, $segments);

        return response()->json([
            'segmentNames' => $segmentNames,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Services\RefKeyService $refKeyService
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function store(Request $request, RefKeyService $refKeyService): JsonResponse
    {
        $accountId = $this->getCurrentAccountId();

        $validatedData = $request->validate([
            'name' => 'required|unique:segments,segmentName,NULL,id,account_id,' . $accountId,
            'conditionFilters' => 'required'
        ]);

        $conditionFilters = $validatedData['conditionFilters'];
        $name = $validatedData['name'];

        $newSegment = Segment::create([
            'reference_key' => $refKeyService->getRefKey(new Segment),
            'account_id' => $accountId,
            'segmentName' => $name,
            'conditions' => $conditionFilters,
            'people' => 0, // NOTE: this field will be deprecated, DON'T USE THIS
            'contactsID' => '[]' // NOTE: this field will be deprecated, DON'T USE THIS
        ]);

        return response()->json([
            'id' => $newSegment->id,
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param int $reference_key
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Http\Response|\Illuminate\View\View
     */
    public function show($reference_key)
    {
        $user = Auth::user();
        $account = $user->currentAccount();
        $currentAccountId = $user->currentAccountId;
        $accountRandomId = $account->accountRandomId;

        $currency = $account->currency;
        $allForms = LandingPageForm::where('account_id', $currentAccountId)->get();
        $allFunnels = funnel::with('landingpages')->select('id', 'funnel_name')->get();
        $allEcommercePages = EcommercePage::all();

        $conditionJson = $this->segmentService->getConditionsJson();
        $segment = Segment::where('reference_key', $reference_key)->firstOrFail();

        $dbConditionFilters = $segment->conditions;
        $contacts = $segment->contacts();

        $processedTags = ProcessedTag::where('account_id', $currentAccountId)->where('typeOfTag', 'contact')->get();
        $tags = [];

        foreach ($processedTags as $processedTag) {
            $storeTag['tagId'] = $processedTag->id;
            $storeTag['tagName'] = $processedTag->tagName;
            $tags[] = $storeTag;
        }

        $customFieldNames = peopleCustomFieldName::whereAccountId($currentAccountId)
            ->pluck('custom_field_name')
            ->filter()
            ->unique();

        $usersProducts = UsersProduct::where('account_id', $currentAccountId)->get();
        $exchangeRate = Currency::firstWhere(['account_id' => $currentAccountId, 'currency' => $account->currency])->exchangeRate ?? 1;

        return Inertia::render('people/pages/SegmentDetails', compact(
            "accountRandomId",
            "contacts",
            "segment",
            "dbConditionFilters",
            "conditionJson",
            "tags",
            "currency",
            "customFieldNames",
            "usersProducts",
            'exchangeRate',
            "allFunnels",
            "allEcommercePages",
            "allForms",
        ));
    }

    /**
     * Update segment (conditions) from view segment page
     *
     * @param \App\Segment $segment
     * @param \Illuminate\Http\Request $request
     * @return Response
     */
    public function update(Segment $segment, Request $request): Response
    {
        $conditionFilters = $request->conditionFilters;

        $segment->update([
            'conditions' => $conditionFilters
        ]);

        return response()->noContent();
    }

    /**
     * Rename segment
     *
     * Note:
     * Segment name is unique per account, and it ignores self, which
     * means user won't get name exists error for current segment
     *
     * @param \App\Segment $segment
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function renameSegment(Segment $segment, Request $request): JsonResponse
    {
        $validatedData = $request->validate([
            'segmentName' => 'required|unique:segments,segmentName,' . $segment->id . ',id,account_id,' . $this->getCurrentAccountId(),
        ]);

        $segmentName = $validatedData['segmentName'];

        $segment->update(compact('segmentName'));

        return response()->json([
            'id' => $segment->id
        ]);
    }

    /**
     * Close linked ads account user list & remove segment
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $segment = Segment::with('adAudiences')->find($id);

        // close synced list if exists
        foreach ($segment->adAudiences as $adAudience) {
            $accountOauth = $adAudience->accountOauth;
            $provider = $accountOauth->socialMediaProvider->name;

            if (strtolower($provider) === 'google') {
                try {
                    $success = (new GoogleCustomerMatch(
                        $accountOauth->refresh_token,
                        $accountOauth->account_id
                    ))->closeCrmBasedUserList($segment, $adAudience);

                    if (!$success) {
                        return response()->json([
                            'message' => 'Failed due to unknown reason when unsync. Please check your linked ads account.'
                        ], 500);
                    }

                } catch (ClientException $clientException) {
                    return response()->json([
                        'message' => 'Your connected Google Ads Account is disconnected from Hypershapes. Please check and reconnect if necessary.'
                    ], 400);
                }

            }

            if (strtolower($provider) === 'facebook') {
                // TODO
            }
        }

        // A dirty workaround to settle the issue of segment deletion on email
        // ON DELETE CASCADE set null couldn't be used due to legacy "Everyone" value in emails.segment_id
        $segmentEmails = Email::where('segment_id', $segment->id)->get();
        foreach ($segmentEmails as $segmentEmail) {
            $segmentEmail->segment_id = null;
            $segmentEmail->save();
        }

        try {
            $segment->delete();
            return response()->noContent();
        } catch (\Exception $e) {
            \Log::error('Failed to delete segment: account = ' . $segment->account_id . ', id = ' . $id);
            return response()->json(null, Response::HTTP_CONFLICT);
        }
    }

    /**
     * Sync segment with social media account oauth.
     *
     * @param Segment $segment
     * @param Request $request
     * @return JsonResponse
     */
    public function syncSocialMedia(Segment $segment, Request $request): JsonResponse
    {
        $adAudienceName = $request->adAudienceName;
        $socialMediaName = strtolower($request->socialMediaName);
        $providerId = SocialMediaProvider
            ::where('name', $socialMediaName)
            ->firstOrFail()
            ->id;
        $accountOauth = AccountOauth
            ::where('account_id', $this->getCurrentAccountId())
            ->where('social_media_provider_id', $providerId)
            ->first();

        if (!$accountOauth) {
            return response()->json([
                'message' => 'No linked account found'
            ], 422);
        }

        $adAudience = AdAudience::firstOrCreate([
            'segment_id' => $segment->id,
            'account_oauth_id' => $accountOauth->id
        ], [
            'list_name' => $adAudienceName,
            'list_id' => null,
            'sync_status' => 'pending',
        ]);

        // sync to google ads
        $google = new GoogleCustomerMatch(
            $accountOauth->refresh_token,
            $segment->account_id
        );
        $google->addCrmBasedUserList($segment, $adAudience);

        return response()->json([
            'media' => [
                'isSynced' => 0,
                'name' => $socialMediaName
            ]
        ]);
    }

    /**
     * To override the problematic getCurrentAccountId in AuthAccountTrait
     *
     * @return mixed
     */
    private function getCurrentAccountId()
    {
        return Auth::user()->currentAccountId;
    }
}
