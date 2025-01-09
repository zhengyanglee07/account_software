<?php

namespace App\Http\Controllers;

use App\Account;
use App\AccountDomain;
use App\EcommerceVisitor;
use App\EcommercePage;
use App\EcommerceTrackingLog;
use App\EcommerceAbandonedCart;
use App\ProcessedContact;
use App\UsersProduct;
use App\Page;
use App\funnel;

use App\Traits\AuthAccountTrait;
use App\Http\Controllers\Controller;
use App\Services\RefKeyService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Traits\ReferralCampaignTrait;

use App\Traits\PublishedPageTrait;

class OnlineStoreTrackingController extends Controller
{
    use AuthAccountTrait, ReferralCampaignTrait, PublishedPageTrait;

    /**
     * Get the record created since visitor last visit
     * @param string|int $visitorRefKey
     */
    private function getVisitor($visitorRefKey)
    {
        return EcommerceVisitor::where('reference_key', $visitorRefKey)->firstOrFail();
    }

    /**
     * Record conversion activity (form submission and order placement), 
     * then assign the acitvities to a contact
     * 
     * @param Request $request
     * @param bool $isAPI
     * @return JsonResponse|int
     */
    public function recordConversion(Request $request, $isAPI = true)
    {
        $visitor = $this->getVisitor($request['visitorId']);
        $isInFunnel = $visitor->sales_channel === 'funnel' && $visitor->funnel_id;

        EcommerceTrackingLog::create(
            [
                'visitor_id' => $visitor->id,
                'type' => $request['type'],
                'value' => $request['value'],
                'is_conversion' => true,
            ]
        );

        $visitor->update([
            'is_completed' => true,
            'processed_contact_id' => $request['processed_contact_id'],
        ]);

        $visitor->abandonedCart->update([
            'cart_products' => null,
        ]);

        if ($isAPI) {
            return response()->json([
                'email' => ProcessedContact::find($request['processed_contact_id'])->email,
                'isInFunnel' => $isInFunnel,
            ]);
        }

        return [
            'id' => $visitor->id,
            'isInFunnel' => $isInFunnel,
        ];
    }

    /**
     * Create new record in ecommerce_visitors and ecommerce_abandoned_carts table
     * for OS/MS activities tracking and abandoned cart identification respectively
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function create(Request $request)
    {
        $domain = AccountDomain::ignoreAccountIdScope()->whereDomain($request->hostname)->firstOrFail();

        $landingpageQuery = Page::where([
            ['funnel_id', '!=', null],
            ['account_id', '=', $domain->account_id],
            ['path', '=', trim($request->path, '/')],
        ]);

        $salesChannel = $request->path !== '/' && $landingpageQuery->exists()
            ? 'funnel'
            : $domain->type;

        if ($request->email) {
            $processedContactId = ProcessedContact::where([
                'email' => $request->email,
                'account_id' => $domain->account_id,
            ])->value('id') ?? null;
        }

        $visitor = EcommerceVisitor::create(
            [
                'account_id' => $domain->account_id,
                'processed_contact_id' => $processedContactId ?? null,
                'sales_channel' => $salesChannel,
                'reference_key' => $this->getRandomId('ecommerce_visitors', 'reference_key'),
                'funnel_id' => $salesChannel === 'funnel' ? ($landingpageQuery->value('funnel_id') ?? $domain?->type_id) : null,
            ]
        );

        EcommerceAbandonedCart::create([
            'visitor_id' => $visitor->id,
        ]);

        return response()->json([
            'visitorRefKey' => $visitor->reference_key,
            'salesChannel' => $salesChannel,
        ]);
    }

    /**
     * Record visitor's visit for every funnel/online-store/mini-store pages
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function trackNewActivity(Request $request)
    {
        $value = null;
        $path = $request->path ?? null;
        $pageType = $request->pageType ?? 'store-page';
        $visitor = $this->getVisitor($request->visitorRefKey);
        $domain = AccountDomain::ignoreAccountIdScope()->whereDomain($request->hostname)->firstOrFail();

        switch ($pageType) {
            case 'product':
                $value = UsersProduct::where([
                    'account_id' => $domain->account_id,
                    'path' => $path,
                ])->value('id');
                break;
            case 'builder-page': // page published from builder
                if ($path === '/' && $domain->type === 'funnel') {
                    if ($request?->pageId) {
                        $value = $request?->pageId;
                    } else {
                        $funnel = funnel::findOrFail($domain->type_id);
                        $value = $funnel->landingpages()->ofPublished()->orderBy('index')->firstOrFail()->id;
                    }
                } else if ($path === '/' && $domain->type === 'online-store') {
                    $value = $domain->type_id;
                } else {
                    $value = Page::where([
                        'account_id' => $domain->account_id,
                        'is_published' => true,
                        'path' => $path,
                    ])->firstOrFail()->id;
                }
                break;
            case 'store-page': // page in online store or mini store that is not published from builder
                $value = EcommercePage::where('name', $request->pageName)->value('id');
                break;
        }

        if (!$value && !$request->value) {
            return response()->json([
                'message' => 'Page not found',
            ], 404);
        }

        EcommerceTrackingLog::create(
            [
                'visitor_id' => $visitor->id,
                'type' => $pageType,
                'value' => $value ?? $request->value,
            ]
        );

        $visitor->abandonedCart->update([
            'cart_products' => $request->cartProducts,
        ]);

        return response()->noContent();
    }

    public function updateCustomerInfo(Request $request, RefKeyService $refKeyService)
    {
        $accountId = $request->accountId;
        $customerInfo = $request->customerInfo;
        $constrains = isset($customerInfo['email']) ? ['email' => $customerInfo['email']] : ['phone_number' => $customerInfo['phoneNumber']];
        $processedContact = ProcessedContact::where('account_id', $request->accountId)
            ->firstOrNew($constrains);
        $processedContact->account_id = $accountId;
        $processedContact->contactRandomId = $refKeyService->getRefKey(new ProcessedContact, 'contactRandomId');
        $processedContact->fname = empty($customerInfo['fullName']) ? $processedContact->fname : $customerInfo['fullName'];
        $processedContact->email = empty($customerInfo['email']) ? $processedContact->email : $customerInfo['email'];
        $processedContact->phone_number = empty($customerInfo['phoneNumber']) ? $processedContact->phone_number : $customerInfo['phoneNumber'];
        $processedContact->dateCreated = $this->datetimeToSelectedTimezone($accountId, date('Y-m-d H:i:s'));
        $processedContact->acquisition_channel = $request->channel ? Str::of($request->channel)->title() : null;
        $processedContact->save();

        // if ($request->isTracking) {
        //     $this->setVisitorEmail($request);
        // }

        $newSignUp = $this->newSignUp($processedContact,  $request->funnelId);
        $user = $this->referralUser($processedContact, $request->funnelId);
        if ($request->hasCookie('referral') && $newSignUp && $request->channel === 'funnel') {
            $this->checkReferralCampaignAction($request->getHost(), 'sign-up', $request->funnelId, $processedContact);
        }

        return response()->json([
            'status' => 'Success',
            'user' => $user,
        ]);
    }

    private function datetimeToSelectedTimezone($accountId, $datetime): string
    {
        $accountTimeZone = Account::find($accountId)->timeZone;
        $datetime = new Carbon($datetime);
        return $datetime->timezone($accountTimeZone)->isoFormat('YYYY-MM-DD\\ H:mm:ss');
    }
}
