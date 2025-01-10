<?php

namespace App\Http\Middleware;

use Closure;
use Auth;
use Event;
use App\Account;
use App\Page;
use App\Permission;
use App\SubplansPermission;
use App\Subscription;
use App\SubscriptionPlan;
use Request;

class PermissionChecker
{
    private $permission = [];
    private $account;
    private $dataArray;

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $request['permissionData'] = [
            "subscription" => [],
            "showLimitModal" => false,
            "context" => "",
            "modalTitle" => "You've Reach The Limit",
            "isSubscriptionPage" => false,
            "featureForPaidPlan" => [],
            "permissionDetail" =>  [
                "Free" =>  [],
                "Square" => [],
                "Triangle" => [],
                "Circle" => []
            ],
            "currentPlan" => "Free",
        ];
        return $next($request);
        if (!Auth::check()) return $next($request);

        $this->account = Account::find(Auth::user()->currentAccountId);
        $crrUrl = request()->path();

        if (!$this->currentSubscriptionPlan() || $crrUrl === 'updateSubscriptionPlan') {
            return $next($request);
        }

        $this->setupShareData();
        $this->setupPermission();

        $this->checkFeaturesPermission();

        switch ($crrUrl) {
            case 'domain/check':
                $postData = \Request::post();
                if (!$postData['isSkipPermissionChecker'])
                    $this->checkPermission('add-domain');
                break;
            case 'image/store':
                $this->checkPermission('upload-image');
                break;
            case ((preg_match('/\/emails\/standard\/\d+\/send/s', $crrUrl) ? true : false)):
                if (!$this->checkPermission('send-email', false)) {
                    $allEmails = Email::where('account_id', Auth::user()->currentAccountId)->get();
                    foreach ($allEmails as $singleEmail) {
                        $singleEmail->schedule = null;
                        $singleEmail->save();
                    }
                }
                break;
            case 'subscription/plan/upgrade':
                $data = $this->uncontrollableQuotaCheck();
                if (!$data['isPermit']) {
                    $this->setupShareData(true, true, $data['slug'], true);
                }
                break;
            case 'affiliates/signup':
                if (!\Request::isMethod('post')) return;
                $this->checkPermission('add-affiliate-member');
                break;
            default:
                Event::listen(['eloquent.creating: *', 'eloquent.replicating: *'], function ($model) {
                    $modelPath = substr($model, strpos($model, ":") + 2);
                    $modelName = str_replace('App\\', '', $modelPath);

                    $filteredPermission = array_filter($this->permission, function ($permission) use ($modelName) {
                        return in_array($modelName, $permission['model']);
                    });

                    if ($modelName === 'Page') {
                        $postData = \Request::post();
                        $isOSLanding = $postData['sectionType'] ?? '' === 'page';
                        $slug = $isOSLanding ? 'create-os-page' : 'add-landing-page';
                        $totalLandingPage = Page::where('account_id', Auth::user()->currentAccountId)
                            ->where(function ($query) use ($isOSLanding) {
                                if ($isOSLanding) return $query->where('is_landing', false);
                                return $query->where('is_landing', true);
                            })->count();
                        $max = $this->account->permissionMaxValue($slug);
                        if ($totalLandingPage >= $max)
                            $this->errorHandling($slug);
                    } else {
                        foreach ($filteredPermission as $slug => $value) {
                            $this->checkPermission($slug);
                        }
                    }
                });
                break;
        }

        $permissionDetail = [];
        $accountPlanTotal = $this->account->accountPlanTotal;
        SubscriptionPlan::whereIn('id', [1, 2, 3, 4])->each(function ($row) use (&$permissionDetail, $accountPlanTotal) {
            $filteredPermission = array_filter($row->permissions->toArray(), function ($row) {
                return $row['type'] === 'integer';
            });
            $permissionDetail[$row->plan] = array_map(function ($item) use ($accountPlanTotal) {
                return [
                    'slug' => $item['slug'],
                    'max' => $item['pivot']['max_value'],
                    'total' => $accountPlanTotal[$this->permission[$item['slug']]['totalProperty']],
                    'context' => $this->permission[$item['slug']]['data']['context'],
                ];
            }, $filteredPermission);
        });

        $this->dataArray['permissionDetail'] = $permissionDetail;
        $this->dataArray['currentPlan'] = $this->account->subscriptionPlan->plan;
        $request['permissionData'] = $this->dataArray;
        return $next($request);
    }

    private function setupPermission()
    {
        $this->addPermission('add-people', 'total_people', 'people', ['ProcessedContact']);
        $this->addPermission('add-product', 'total_product', 'product', ['UsersProduct']);
        $this->addPermission('upload-image', 'total_image_storage', 'image storage');
        $this->addPermission('add-form', 'total_form', 'form submissions');
        $this->addPermission('add-domain', 'total_domain', 'domain', ['AccountDomain']);
        $this->addPermission('add-funnel', 'total_funnel', 'funnel', ['funnel']);
        $this->addPermission('add-landing-page', 'total_landingpage', 'landing page', ['Page']);
        $this->addPermission('add-segment', 'total_segment', 'segment', ['Segment']);
        $this->addPermission('send-email', 'total_email', 'email sent');
        $this->addPermission('add-automation', 'total_automation', 'automation', ['Automation']);
        $this->addPermission('add-customfield', 'total_customfield', 'custom field', ['peopleCustomFieldName']);
        $this->addPermission('invite-role', 'total_role_invite', 'assign role', ['RoleInvitationEmail']);
        $this->addPermission('add-referral-campaign', 'total_referral_campaign', 'referral campaign', ['ReferralCampaign']);
        $this->addPermission('add-affiliate-campaign', 'total_affiliate_campaign', 'affiliate campaign', ['AffiliateMemberCampaign']);
        $this->addPermission('add-affiliate-member', 'total_affiliate_member', 'affiliate member');
        $this->addPermission('create-os-page', 'total_online_store_pages', 'online store page', ['Page']);
        $this->addPermission('add-social-proof', 'total_social_proof', 'social proof', ['Notification']);
    }

    private function addPermission($slug, $totalProperty, $context, $relationModel = [])
    {
        $this->permission[$slug] = [
            'totalProperty' => $totalProperty,
            'data' => [
                'exceed_limit' => true,
                'modal_title' => "You've Reach The Limit",
                'context' => $context
            ],
            'model' => $relationModel,
        ];
    }

    private function checkPermission($slug, $isAbort = true)
    {
        $accountPlanTotal = $this->account->accountPlanTotal;
        $permissionType = Permission::firstWhere('slug', $slug)->type;
        $isPermit = true;
        if ($permissionType === 'integer') {
            $total = $accountPlanTotal[$this->permission[$slug]['totalProperty']];
            $max = $this->account->permissionMaxValue($slug);
            $isPermit = $total <= $max;
        }
        if ($isPermit) return true;
        if ($isAbort) $this->errorHandling($slug);
        return $isPermit;
    }

    private function checkFeaturesPermission()
    {
        $featureForPaidPlan = [];
        $permissions = Permission::where('type', 'boolean')->get();
        foreach ($permissions as $permission) {
            if (!$this->account->permissionMaxValue($permission->slug))
                array_push($featureForPaidPlan, $permission->slug);
        }
        $this->dataArray['featureForPaidPlan'] = $featureForPaidPlan;
    }

    private function getContext($slug)
    {
        $context = $slug ?  $this->permission[$slug]['data']['context'] : '';
        return  $context;
    }

    private function currentSubscriptionPlan()
    {
        return Subscription
            ::with('subscriptionPlan', 'subscriptionPlanPrice')
            ->where('account_id', Auth::user()->currentAccountId)
            ->first();
    }

    private function getSubscription($slug, $isUncontrollable)
    {
        $currentSubscriptionPlan = $this->currentSubscriptionPlan();
        if ($slug) {
            $currentPermission = Permission::firstWhere('slug', $slug);
            $planTypes = ['Free', 'Square', 'Triangle', 'Circle'];
            $quotaLimit = [];
            foreach ($planTypes as  $index => $type) {
                $limit = SubplansPermission::firstWhere(['plan_id' => $index + 1, 'permission_id' => $currentPermission->id])->max_value;
                $quotaLimit[$type] = $limit;
            }
            return [
                'planName' => $currentSubscriptionPlan->subscriptionPlan->plan,
                'quotaLimit' => $quotaLimit,
                'isInteger' => $currentPermission->type === 'integer',
                'isUncontrollable' => $isUncontrollable,
            ];
        }
        return [];
    }

    private function setupShareData($showModal = false, $isSubscriptionPage = false, $slug = null, $isUncontrollable = false)
    {
        $this->dataArray = [
            'subscription' => $this->getSubscription($slug, $isUncontrollable),
            'showLimitModal' => $showModal,
            'context' => $this->getContext($slug) ?? '',
            'modalTitle' => "You've Reach The Limit",
            'isSubscriptionPage' => $isSubscriptionPage,
        ];
    }

    private function uncontrollableQuotaCheck()
    {
        $uncontrollableSlug = ['add-people', 'send-email', 'add-form', 'add-affiliate-member'];

        $currentSubscriptionPlan = $this->currentSubscriptionPlan();
        if ($currentSubscriptionPlan) {
            foreach ($uncontrollableSlug as $slug) {
                $isFreePlan = $currentSubscriptionPlan->subscription_plan_id === 1;
                $isPlanAllowAffiliateMember = $currentSubscriptionPlan->subscription_plan_id > 2;
                $isPermit = $this->checkPermission($slug, false);
                if (
                    !$isPermit &&
                    ($slug !== 'add-affiliate-member' || $isPlanAllowAffiliateMember) &&
                    ($slug !== 'add-form' || $isFreePlan)
                ) {
                    return ['isPermit' => false, 'slug' => $slug,];
                }
            }
            return ['isPermit' => true, 'slug' => ''];
        }
    }

    private function errorHandling($slug)
    {
        $data = [
            'exceed_limit' => true,
            'modal_title' => $modalTitle ?? "You've Reach The Limit",
            'context' => $this->getContext($slug),
            'upgradeButton' => true,
            'subscription' => $this->getSubscription($slug, false),
        ];
        abort(422, json_encode($data));
    }
}
