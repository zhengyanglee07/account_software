<?php

namespace App\Http\Controllers;

use App\SubscriptionPlanPrice;
use App\Tax;
use App\User;
use App\Order;
use App\Account;
use App\Currency;
use App\ShopType;
use App\PromoCode;
use Carbon\Carbon;
use App\PaymentAPI;
use App\AccountRole;
use App\AccountUser;
use App\Page;
use App\Subscription;
use App\AccountDomain;
use App\AccountPlanTotal;
use App\CreditCardDetail;
use App\SubscriptionLogs;
use App\SubscriptionPlan;
use App\NotifiableSetting;
use App\RoleInvitationEmail;
use Illuminate\Http\Request;
use App\AffiliateReferralLog;
use App\EcommerceNavBar;
use App\Location;
use App\EcommercePreferences;
use App\ProductReviewSetting;
use App\MiniStoreChecklistHeader;
use Illuminate\Support\Facades\Auth;
use App\Traits\CurrencyConversionTraits;
use Illuminate\Support\Facades\Redirect;
use PhpOffice\PhpSpreadsheet\Calculation\Database;
use App\Email;
use App\Services\EmailService;
use App\Jobs\SendOnboardingEmailJob;
use App\Permission;
use Inertia\Inertia;

class AccountController extends Controller
{
    use CurrencyConversionTraits;

    public function currentAccountId()
    {
        return Auth::user()->currentAccountId;
    }

    public function redirectToDashboard()
    {
        // header( "refresh:60;url=/dashboard" );
    }

    //after arrange //zh
    public function emailConfirmation()
    {
        $user = Auth::user();
        $email = $user->email;

        return view('confirmEmail', compact('email'));
    }

    public function saveUserName(Request $request)
    {
        // dd($request->name);
        $user = Auth::user();
        $findUser = User::where('email', $user->email)->first();
        $findUser->firstName = $request->name;
        $findUser->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Suite Numbers retrieved!',
            'datas' => '',

        ]);
    }

    public function saveAccountDetails(Request $request)
    {
        $account = Auth::user()->currentAccount();
        $domain = app()->environment() === "production"
            ? ".myhypershapes.com"
            : ".salesmultiplier.asia";
        $subdomain = $request->subdomainName . $domain;

        $newCurrency = Currency::firstOrNew(['account_id' => $account->id]);
        $newCurrency->currency = $request->currency;
        $newCurrency->prefix = $request->currency === 'MYR' ? 'RM' : $request->currency;
        $newCurrency->exchangeRate = "1";
        $newCurrency->isDefault = true;
        $newCurrency->rounding = false;
        $newCurrency->decimal_places = 2;
        $newCurrency->separator_type = ',';
        $newCurrency->save();

        // create navigation menu
        $menuItems = [
            [
                'id' => 1,
                'name' => "Home",
                'link' => [
                    'type' => "Page",
                    'name' => "Home",
                    'path' => "/home"
                ],
                'refKey' => random_int(100000000001, 999999999999),
                'elements' => []
            ],
            [
                'id' => 2,
                'name' => "Catelog",
                'link' => [
                    'type' => "Product",
                    'name' => "All Products",
                    'path' => "/products/all"
                ],
                'refKey' => random_int(100000000001, 999999999999),
                'elements' => []
            ]
        ];
        EcommerceNavBar::updateOrCreate(
            [
                'account_id' => $account->id,
                'title' => 'Main Menu',
            ],
            [
                'menu_items' => json_encode($menuItems),
            ]
        );

        $this->createFreePlan();

        do {
            $randomId = random_int(100000000001, 999999999999);
            $condition = Account::where('accountRandomId', '=', $randomId)->exists();
        } while ($condition);

        $account->domain = $subdomain;
        $account->company_logo = $request->companyLogo;
        $account->store_name = $request->storeName;
        $account->company = $request->storeName;
        $account->address = $request->address;
        $account->city = $request->city;
        $account->state = $request->state;
        $account->country = $request->countryName;
        $account->currency = $request->currency;
        $account->zip = $request->zip;
        $account->timeZone = $request->timezone;
        $account->accountRandomId = $randomId;
        $account->was_selected_goal = false;
        $account->status = 'pending';
        $account->save();

        Location::updateOrCreate(
            [
                'account_id' => $account->id,
            ],
            [
                'name' => $request->storeName,
                'address1' => $request->address,
                'city' => $request->city,
                'zip' => $request->zip,
                'state' => $request->state,
                'country' => $request->countryName,
                'email' => Auth::user()->email,
                'phone_number' => $request->mobileNumber,
            ]
        );

        $onlineStoreHomepageId = $this->createOnlineStorePage();

        AccountDomain::updateOrCreate(
            [
                'account_id' => $account->id
            ],
            [
                'domain' => $subdomain,
                'type' => 'online-store',
                'is_subdomain' => 1,
                'is_verified' => 1,
                'type_id' => $onlineStoreHomepageId,
            ]
        );

        $accountPlanTotal = AccountPlanTotal::firstOrNew(['account_id' => $account->id]);
        $accountPlanTotal->total_user = 1;
        $accountPlanTotal->save();

        ProductReviewSetting::create(['account_id' => $account->id]);

        return response()->json([
            'status' => 'success',
        ]);
    }

    public function createFreePlan()
    {
        $account = Account::firstOrNew(['id' => Auth::user()->currentAccountId]);
        $account->subscription_plan_id = 1;
        $account->subscription_status = 'active';
        $account->save();
        $freePlanPrice = SubscriptionPlanPrice
            ::where([
                'subscription_plan_id' => 1,
                'subscription_plan_type' => 'yearly'
            ])
            ->firstOrFail();

        Subscription::create([
            'account_id' => $account->id,
            'subscription_plan_id' => 1,
            'subscription_plan_price_id' => $freePlanPrice->id,
            'user_id' => Auth::user()->id,
            'status' => 'active',
            'current_plan_start' => date('Y/m/d H:i:s'),
            'current_plan_end' => '1970-01-01 07:30:00',
            'cancel_at' => null,
            'last_email_reset' => date('Y/m/d H:i:s'),
            'trial_start' => '1970-01-01 07:30:00',
            'trial_end' => '1970-01-01 07:30:00',
        ]);
    }

    private function createOnlineStorePage()
    {
        $currentAccountId = $this->currentAccountId();

        if (Page::where('account_id', $currentAccountId)->first() != null) {
            return;
        }

        do {
            $randomId = random_int(100000000001, 999999999999);
            $condition = Page::where('reference_key', $randomId)->exists();
        } while ($condition);

        $path = preg_replace("/[^a-z0-9]/", "", strtolower("Home"));

        $newLandingPage = Page::create([
            'element' => '[]',
            'account_id' => $currentAccountId,
            'funnel_id' => null,
            'name' => "Home",
            'is_published' => true,
            'path' => $path,
            'is_landing' => false,
            'reference_key' => $randomId,
            'duplicated_count' => 0,
        ]);

        return $newLandingPage->id;
    }

    /**
     * @deprecated
     *
     * DON'T USE THIS FUNC
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function saveApiAndPassword(Request $request)
    {
        trigger_error("Deprecated function called.", E_USER_DEPRECATED);

        $shopType = $request->shopType;

        // check if store credentials are correct before proceeding to sync data
        try {
            //            $this->checkCredentials($shopType, $request->apiKey, $request->password, $request->domain);
        } catch (\Exception $ex) {
            return response()->json([
                'status' => 'failed',
                'message' => $ex->getMessage()
            ], 401);
        }

        $user = Auth::user();
        $account = Account::find($user->currentAccountId);
        $accountId = $account->id;
        $account->shoptype_id = $request->shopType;
        $account->api = $request->apiKey;
        $account->password = $request->password;
        $account->domain = $request->domain;
        $account->shopName = $request->shopName;
        // $account->accountRandomId = rand(1000, 99999) . $accountId . rand(1000, 99999);
        $account->save();

        // directly sync data after saved account
        $this->loadingToDashboard($accountId);

        return response()->json([
            'status' => 'success',
            'message' => 'Suite Numbers retrieved!',
            'datas' => $accountId,
        ]);
    }

    public function loadingToDashboard($accountId)
    {
        // update status of account to syncing when syncing online store
        $account = Account::find($accountId);
        $account->status = 'syncing';
        $account->save();

        // get user before dispatch, since in job we cannot access auth user
        SyncDataJob::dispatch($accountId, Auth::user());
    }

    public function currentAccount()
    {
        return Auth::user()->currentAccount();
    }

    public function checkGeneralSetting()
    {
        $account = Auth::user()->currentAccount();
        if ($account->notifiableSetting == null) {
            NotifiableSetting::create(['account_id' => $account->id, 'notification_email' => Auth::user()->email]);
        }

        Location::updateOrCreate(
            [
                'account_id' => $account->id,
            ],
            [
                'name' => $account->company,
                'address1' => $account->address,
                'city' => $account->city,
                'zip' => $account->zip,
                'state' => $account->state,
                'country' => $account->country,
                'email' => Auth::user()->email
            ]
        );

        $accountPlanTotal = AccountPlanTotal::where('account_id', $account->id)->first();
        if ($accountPlanTotal == null) {
            $newPlanTotal = new AccountPlanTotal();
            $newPlanTotal->account_id = $account->id;
            $newPlanTotal->save();
        }
    }

    /**
     * create new account for user and assign current account
     * if user has not account previously (first time login)
     *
     * @return void
     */
    public function afterVerifyCreateShop()
    {

        $user = Auth::user();
        // dd($user);
        $accounts = $user->accounts->where('pivot.account_role_id', 1)->first();

        if ($accounts == null) {
            $newAccount = Account::create([]);
            $user->currentAccountId = $newAccount->id;
            $user->save();

            // save an entry in account_user table
            $newAccount->user()->attach([
                $user->id => [
                    'role' => "owner",
                    'account_role_id' => 1
                ]
            ]);
        }

        $account = $this->currentAccount();
        $taxSetting = Tax::where('account_id', $account->id)->first();
        if ($taxSetting == null) {
            $newTaxSetting = new Tax();
            $newTaxSetting->account_id = $account->id;
            $newTaxSetting->save();
        }

        $accountPlanTotal = AccountPlanTotal::where('account_id', $user->currentAccountId)->first();
        if ($accountPlanTotal == null) {
            $newPlanTotal = new AccountPlanTotal();
            $newPlanTotal->account_id = $user->currentAccountId;
            $newPlanTotal->save();
        }

        $isAffiliate = AffiliateReferralLog::where('user_id', $user->id)->exists();
        if ($isAffiliate) {
            $account = Account::where('id', $user->currentAccountId)->first();
            $affiliateReferral = AffiliateReferralLog::where('user_id', $user->id)->first();
            $account->refer_by = $affiliateReferral->refer_from_affiliate_id;
            $account->save();
        }
    }

    /**
     * Previously used to check whether the data synced or not using refresh + ajax
     * TODO: pending to be removed
     *
     * @return void
     */
    public function onboardingLoadingData()
    {
        $user = Auth::user();
        $currentAccountId = $user->currentAccountId;
        $account = Account::where('id', $currentAccountId)->first();
        $accountStatus = $account->status;

        if ($accountStatus == 'complete') {
            return response()->json([
                'status' => 'success',
                'message' => 'Suite Numbers retrieved!',
            ]);
        }

        return response()->json([
            'status' => 'failed',
            'message' => 'Suite Numbers retrieved!',
        ], 500);  // donno what status code to put, so 500
    }

    public function companyName()
    {
        $user = Auth::user();
        $findUser = User::where('email', $user->email)->first();
        $name = $findUser->firstName;
        $env = app()->environment();
        // $userId = $findUser->id;

        return Inertia::render('onboarding/pages/Account', compact('name', 'env'));
    }

    /**
     * Redirect user to either dashboard or create store page, depending on the account status
     *
     */
    public function dashboard()
    {
        $user = Auth::user();
        $account = $user->accounts()->find($user->currentAccountId);
        $isOwner = $user->ownAccountId() === $account->id;
        $accountRandomId = $account->accountRandomId;
        $allorders = $this->filteredOrders();
        $totalSales = $this->getTotalPrice($allorders);
        $currencyArray = $this->getCurrencyArray(null);
        $miniStore = MiniStoreChecklistHeader::where('account_id', $user->currentAccountId)->first();
        $miniStoreCheckedlist =  $miniStore ? json_decode($miniStore->checked_list) : null;
        $subscriptionPlan = SubscriptionPlan::where('id', $account->subscription_plan_id)->first();
        $accountPlanTotal = AccountPlanTotal::where('account_id', $user->currentAccountId)->first();

        $saleChannel = AccountDomain::where('account_id', $account->id)->first()->type;
        // forget previously set session used to sync data
        session()->forget(['accountRandomId', 'id', 'shopType', 'api', 'password', 'domain']);

        $permissionQuota = [];
        $permission = Permission::with('subplans')->where('type', 'integer')->each(function ($row) use (&$permissionQuota, $subscriptionPlan) {
            array_push($permissionQuota, [
                'slug' => $row->slug,
                'max' => $subscriptionPlan->maxValue($row->slug),
            ]);
        });

        return Inertia::render('shared/pages/Dashboard', compact(
            'account',
            'subscriptionPlan',
            'accountPlanTotal',
            'totalSales',
            'allorders',
            'isOwner',
            'currencyArray',
            'miniStoreCheckedlist',
            'saleChannel',
            'permissionQuota',
        ));
    }

    public function saleChannelType()
    {
        // forget previously set session used to sync data
        session()->forget(['accountRandomId', 'id', 'shopType', 'api', 'password', 'domain']);

        return Inertia::render('onboarding/pages/SaleChannelSelection');
    }

    public function accountSettings(Request $request)
    {
        $account = Account::select(
            'company_logo',
            'favicon',
            'store_name',
            'company',
            'address',
            'country',
            'city',
            'state',
            'zip',
            'accountName',
            'currency',
            'timeZone',
        )->find(Auth::user()->currentAccountId);

        return Inertia::render('setting/pages/AccountSettings', compact('account'));
    }

    public function updateTimezone(Request $request)
    {
        $account = Auth::user()->currentAccount();
        $account->timeZone = $request->input('timezone') ?? $account->timeZone;
        $account->save();

        return response()->json(['message' => 'Successfully updated timezone']);
    }

    public function updateCompanyProfile(Account $account, Request $request)
    {
        $account = Account::find(Auth::user()->currentAccountId);
        $account->company_logo = $request->input('companyLogo');
        $account->favicon = $request->input('favicon');
        $account->store_name = $request->input('storeName') ?? $account->store_name;
        $account->company = $request->input('companyName') ?? $account->companyName;
        $account->address = $request->input('companyAddress') ?? $account->address;
        $account->city = $request->input('city') ?? $account->city;
        $account->state = $request->input('state') ?? $account->state;
        $account->country = $request->input('country') ?? $account->country;
        $account->zip = $request->input('zip') ?? $account->zip;
        $account->save();
    }

    public function updateEducationalStatus(Account $account)
    {
        $account->update([
            'was_educated' => true
        ]);
    }

    public function getAccountTimezone(Account $account)
    {
        return response()->json([
            'status' => 'success',
            'timezone' => $account->timeZone
        ]);
    }

    public function updateEmailAffiliateSetting(Request $request)
    {
        $updatedSetting = $request->canDisableBadges
            ? $request->hasAffiliateBadge
            : true;
        Account::find(Auth::user()->currentAccountId)->update([
            'has_email_affiliate_badge' => $updatedSetting
        ]);

        return response()->json($updatedSetting);
    }

    public function getCheckoutSetting()
    {
        $settings = EcommercePreferences::firstOrCreate(['account_id' => $this->currentAccountId()]);
        return view('settings.checkoutSetting', compact('settings'));
    }

    public function getUserSettingPage()
    {
        $account = Account::find(Auth::user()->currentAccountId);
        $accountTimeZone = Account::find($account->id)->timeZone;
        $users = RoleInvitationEmail::where('account_random_id', $account->accountRandomId)
            ->select('email')
            ->groupBy('email')->get();

        foreach ($users as $user) {

            $userInfo = RoleInvitationEmail::where([
                'account_random_id' => $account->accountRandomId,
                'email' => $user->email,
            ])->orderBy('updated_at', 'desc')->first();

            $joinedDate = ($userInfo['status'] == 'verified') ? $userInfo->updated_at : "pending";

            $user['role'] = $userInfo->role;
            $user['joinedDate'] = $joinedDate;
            $updatedTime = new Carbon($userInfo->updated_at);
            $user->convertedTime =  $updatedTime->timezone($accountTimeZone)->isoFormat('YYYY-MM-D h:mm a');
            $existUser = User::select('id')->where('email', $user->email)->first();
            $user->userId = "not-user";
            if ($existUser !== null) {
                $user->userId = $existUser['id'];
            };
        }
        return Inertia::render('setting/pages/UserSettings', compact('users'));
    }

    public function removeUser($id, $email)
    {
        // Remove User from pivot table
        $user = Auth::user();
        $account = Account::find(Auth::user()->currentAccountId);

        $invitedUsers =  RoleInvitationEmail::where('account_random_id', $account->accountRandomId)->where('email', $email)->get();

        foreach ($invitedUsers as $invitedUser) {
            $invitedUser->delete();
        };

        if ($id != "not-user") {
            $userToBeRemove = AccountUser::where('account_id', $user->currentAccountId)->where('user_id', $id)->first();

            if ($userToBeRemove !== null) {
                $userToBeRemove->delete();
                // Set deleted user's currentAccountId back to own account id
                $deletedUser = User::find($id);
                $role = AccountRole::where('name', 'owner')->first();
                $deletedUserAccount = AccountUser::where('user_id', $id)->where('account_role_id', $role->id)->first();
                $deletedUser->currentAccountId = $deletedUserAccount->account_id;
                $deletedUser->save();
            }
        }

        return response()->json(['status' => 'success']);
    }

    public function sendOnboardingEmail()
    {
        $isProduction = app()->environment() == 'production';
        // First onboarding email id in production
        $emailId = 542;
        // Get first onboarding email id in local
        if (!$isProduction) {
            $senderAccountId = User::firstWhere('email', 'steve@gmail.com')->currentAccountId;
            $emailId = Email::where('account_id', $senderAccountId)->where('type', 'Marketing')->first()->id;
        }

        $receiverAccount = Account::where('id', Auth::user()->currentAccountId)->first();
        $email = Email::whereId($emailId)->first();

        if (isset($email)) {
            dispatch(new SendOnboardingEmailJob($email, $receiverAccount->user->first()->email));
            $selectedMiniStore = (bool)$receiverAccount['selected_mini_store'];
            $nextEmailToSend = $selectedMiniStore ? 1 : 15;
            $sendNextEmailAt = $isProduction ?
                Carbon::now()->addDays($nextEmailToSend) :
                Carbon::now()->addMinutes($nextEmailToSend);

            $receiverAccount->update(
                [
                    'send_onboarding_email_at' => $sendNextEmailAt,
                    'onboarding_email_to_sent' => $nextEmailToSend
                ]
            );
            $receiverAccount->save();
        }
    }

    public function boarded()
    {
        $account =  Account::find($this->currentAccountId());
        if (!$account->is_onboarded) {
            $account->is_onboarded = true;
            $account->save();
        }
    }
}
