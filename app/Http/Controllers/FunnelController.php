<?php

namespace App\Http\Controllers;

use App\AccountDomain;
use App\funnel;
use App\Page;
use Illuminate\Http\Request;
use App\AccountPlanTotal;
use App\Currency;
use App\User;
use App\SubscriptionPlan;
use App\Subscription;
use Auth;
use Inertia\Inertia;

class FunnelController extends Controller
{

    public function currentAccountId()
    {
        $user = Auth::user();
        return $user->currentAccountId;
    }

    public function isFunnelNameDuplicated($funnelName)
    {
        return funnel::where([
            'funnel_name' => $funnelName
        ])->exists();
    }

    public function generateReferenceKey()
    {
        do {
            $randomId = random_int(100000000001, 999999999999);
            $condition = funnel::ignoreAccountIdScope()->where('reference_key', $randomId)->exists();
        } while ($condition);

        return $randomId;
    }

    public function index(Request $request)
    {
        $referenceKey = $request->query('reference_key');
        $popOut = $request->query('popOut');
        $allFunnels = funnel::all();
        $user = User::where('currentAccountId', $this->currentAccountId())->first();
        $subscription = Subscription::where('account_id', $this->currentAccountId())->first();
        $subscriptionPlan = SubscriptionPLan::where('id', $subscription->subscription_plan_id)->first();
        $accountPlanCalculation = AccountPlanTotal::where('account_id', $this->currentAccountId())->first();

        foreach ($allFunnels as $data) {
            $landingpages = $data->landingpages;
            if (count($landingpages) == 0) {
                $lastModified = $data->updated_at;
            } else {
                $lastestUpdatedAt = $data->landingpages->sortByDesc('updated_at')->first()->updated_at;
                $lastModified = $lastestUpdatedAt > $data->updated_at ? $lastestUpdatedAt : $data->updated_at;
            }
            $data['last_modified'] = $lastModified;
        }

        return Inertia::render('funnel/pages/Index', compact('allFunnels', 'referenceKey', 'popOut'));
    }

    public function create(Request $request)
    {

        if ($this->isFunnelNameDuplicated($request->funnelname)) {
            return response()->json([
                'duplicated' => true
            ], 500);
        }

        $accountId = $this->currentAccountId();
        $matchAccountId = ['account_id' => $accountId];

        $subdomain = AccountDomain::where($matchAccountId)->firstWhere([
            'is_subdomain' => 1
        ])->domain;

        $defaultCurrency = Currency::where($matchAccountId)->firstWhere([
            'isDefault' => '1'
        ])->currency;

        $newFunnel = funnel::create([
            'funnel_name' => $request->funnelname,
            'currency' => $defaultCurrency,
            'domain_name' => $subdomain,
            'font_size' => 15,
            'text_family' => 'Default',
            'reference_key' => $this->generateReferenceKey()
        ]);

        return response()->json([
            'id' => $newFunnel->id,
            'reference_key' => $newFunnel->reference_key,
        ]);
    }

    /**
     * @param $referenceKey
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function show($referenceKey)
    {
        $currentAccountId = $this->currentAccountId();
        $allDomain = AccountDomain::where('account_id', $currentAccountId)->get();

        $funnel = funnel::firstWhere([
            'reference_key' => $referenceKey
        ]);

        $this->authorize('view', $funnel);

        $funnelLandingPage = $funnel->landingpages;

        $currencies = Currency::where([
            ['account_id', $currentAccountId],
            ['exchangeRate', '!=', null]
        ])->pluck('currency');

        return Inertia::render('funnel/pages/Show', compact(
            'allDomain',
            'funnel',
            'funnelLandingPage',
            'currencies',
        ));
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function delete(funnel $funnel)
    {
        $this->authorize('delete', $funnel);

        $funnel->delete();

        foreach ($funnel->landingpages as $landingpage) {
            $landingpage->delete();
        };

        return response()->json([
            'message' => "Funnel Successfully Deleted"
        ]);
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function update($funnelId, Request $request)
    {
        $funnel = funnel::find($funnelId);

        $this->authorize('update', $funnel);

        if ($funnel->funnel_name != $request->funnelName) {
            if ($this->isFunnelNameDuplicated($request->funnelName)) {
                return response()->json(['duplicated' => true]);
            }
        }

        $funnel->update([
            'funnel_name' => $request->funnelName,
            'currency' => $request->json('funnelDetails.currency'),
            'domain_name' => $request->json('funnelDetails.domain_name'),
            'tracking_header' => $request->json('funnelDetails.tracking_header'),
            'tracking_body' => $request->json('funnelDetails.tracking_body'),
            'has_affiliate_badge' => $request->canDisableBadges ? $request->json('funnelDetails.has_affiliate_badge') : 1
        ]);

        return response()->json($funnel);
    }

    public function reorderLandingPages(Request $request)
    {
        $funnel_id = $request->funnelId;
        $matchFunnel = ['funnel_id' => $funnel_id];

        //* the sequences below are important, don't change them
        $firstLanding = Page::where($matchFunnel)->firstWhere('index', $request->toIndex);
        $secondLanding = Page::where($matchFunnel)->firstWhere('index', $request->fromIndex);

        $firstLanding->index = $request->fromIndex;
        $firstLanding->save();

        $secondLanding->index = $request->toIndex;
        $secondLanding->save();

        return response()->json(Page::where($matchFunnel)->get());
    }

    public function generateUniquePath($path)
    {
        $count = 0;
        $path =  preg_replace("/[^a-z0-9-]/", "", strtolower($path));
        do {
            $newPath = $count == 0 ? $path : $path . "-" . $count;
            $isExisted = Page::where([
                'account_id' => $this->currentAccountId(),
                'path' => $newPath,
            ])->exists();
            $count++;
        } while ($isExisted);

        return $newPath;
    }

    public function generateUniqueFunnelName($name)
    {
        $newName = $name;
        do {
            $newName = "Copy of " . $newName;
            $isExisted = funnel::where([
                'funnel_name' => $newName,
            ])->exists();
        } while ($isExisted);

        return $newName;
    }

    public function generateLandingReferenceKey()
    {
        do {
            $randomId = random_int(100000000001, 999999999999);
            $condition = Page::where('reference_key', $randomId)->exists();
        } while ($condition);

        return $randomId;
    }

    public function saveSharedFunnel(Request $request)
    {
        $subdomain = AccountDomain::firstWhere([
            'account_id' => $this->currentAccountId(),
            'is_subdomain' => 1
        ])->domain;

        $defaultCurrency = Currency::firstWhere([
            'account_id' => $this->currentAccountId(),
            'isDefault' => '1'
        ])->currency;

        $sharedFunnel = funnel::ignoreAccountIdScope()->where('reference_key', $request->reference_key)->firstOrFail();

        $newFunnel = funnel::create([
            'funnel_name' => $this->generateUniqueFunnelName($sharedFunnel->funnel_name),
            'domain_name' => $subdomain,
            'currency' => $defaultCurrency,
            'reference_key' => $this->generateReferenceKey()
        ]);

        $this->saveLandingPage($sharedFunnel->id, $newFunnel->id, true);

        return response()->json([
            'status' => "Success"
        ]);
    }

    public function duplicate(funnel $funnel)
    {
        $newFunnel = funnel::create([
            'funnel_name' => 'Copy of ' . $funnel->funnel_name,
            'domain_name' => $funnel->domain_name,
            'currency' => $funnel->currency,
            'tracking_header' => $funnel->tracking_header,
            'tracking_body' => $funnel->tracking_body,
            'reference_key' => $this->generateReferenceKey(),
        ]);

        $this->saveLandingPage($funnel->id, $newFunnel->id, false);

        return response()->json([
            'status' => "Success",
            'message' => "Funnel '{$funnel->funnel_name}' duplicated successfully"
        ]);
    }

    private function saveLandingPage($funnelId, $newFunnelId, $isFromSharedFunnel)
    {
        $landingpages = funnel::ignoreAccountIdScope()->find($funnelId)->landingpages;

        foreach ($landingpages as $landing) {
            $newPage = Page::create([
                'funnel_id' => $newFunnelId,
                'account_id' => $this->currentAccountId(),
                'is_landing' => true,
                'is_published' => false,
                'element' => $landing->element,
                'name' => $landing->name,
                'index' => $landing->index,
                'path' => $this->generateUniquePath($landing->path),
                'reference_key' => $this->generateLandingReferenceKey()
            ]);
        }
    }
}
