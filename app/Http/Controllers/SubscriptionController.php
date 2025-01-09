<?php

namespace App\Http\Controllers;
use Auth;
use Exception;
use App\User;
use App\Account;
use App\PromoCode;
use App\Subscription;
use App\CreditCardDetail;
use App\SubscriptionLogs;
use App\SubscriptionPlan;
use App\RetryInvoice;
use Illuminate\Http\Request;
use App\SubscriptionPlanPrice;
use App\Traits\AuthAccountTrait;
use Carbon\Carbon;
use Inertia\Inertia;
use DateTime;

class SubscriptionController extends Controller
{
    use AuthAccountTrait;

    private const MIN_USER_CREATION_HOURS = 288; // 12 days
    private const MAX_PROMO_HOURS = 72; // 3 days
    private const PERCENTAGE_DISCOUNT = 50;

    private const YEARLY_PROMO_START_AT = '2022-05-18 14:15:00';

    private const STRIPE_SUBSCRIPTION_CANCELED_STATUS = 'canceled';

    /**
     * get account id for current user
     */
    public function current_accountId()
    {
        $user = Auth::user();
        return $user->currentAccountId;
    }

     /**
     * get integration API key for different enviroment
     *
     * suggestion : for secure purpose can put this to env file
     */
    public function integrateStripeApi()
    {
        $key =
        [
//            'local' => 'sk_test_51Hh9jlFeI4XEtTz4pjvSNvdVnpqlB7vBUJEEToglMjdT0gskUzZu9Hejek1gzTWYIJsajW0oDhAQPqmAl2CsIdSq00ZeEANswy',
            'local'=> 'sk_test_51IoNpTFXu7UEcy2xftTt5NSD11C4iCLkpn2tPxtxo1nH7yDtBIEhjjrnf2C5zOupi4hgv89Ngwd6RYrSsPohBLi9004pR820fK',
            'staging' => 'sk_test_jiOaiu4JcFbIW8kKt9y1v0YR00y8ojNNUv',
            'production' => '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'
        ];
        \Stripe\Stripe::setApiKey($key[app()->environment()]);
    }

    private function getSubscriptionId($priceId)
    {
        $subscriptionPlan = SubscriptionPlanPrice::where('price_id',$priceId)
        ->first()
        ->subscriptionPlan;
        return $subscriptionPlan->id;
    }

     /**
     * to get all subscription and since now we cant downgrade so filter out the lower plan
     *
     * @param $type string
     */
    private function getAllSubscription($type)
    {
        //to get all subscription and since now we cant downgrade so filter out the lower plan
        $greaterOrLessThan = $type === 'upgrade' ? ">=" : "=<";
        $type === 'create' ? $greaterOrLessThan = '>=' : $greaterOrLessThan;
        $currentSubscriptionPlan = Subscription
            ::with('subscriptionPlan', 'subscriptionPlanPrice')
            ->where('account_id',$this->current_accountId())
            ->first();
        //to get all subscription with subscription plan price
        $allSubscriptionPlan = SubscriptionPlan::with('subscriptionPlanPrice')
            ->where('plan','!=','Square +')
            ->where('plan','!=','Triangle +')
            ->where('plan','!=','Circle +')->get();
        // check credit card is ti exists
        $creditCard = CreditCardDetail::where('account_id', $this->current_accountId())->first();
        return [
            'creditCard' => $creditCard,
            'greaterOrLessThan' => $greaterOrLessThan,
            'allSubscriptionPlan' => $allSubscriptionPlan,
            'currentSubscriptionPlan' => $currentSubscriptionPlan,
            'env' => app()->environment(),
        ];
    }

    /**
     * to load subscription sign up page
     */
    public function viewSubPlanSelect()
    {
        $type = "create";
        $getSubscriptionDetail = $this->getAllSubscription($type);
        $coupon = $this->getYearlySubscriptionCoupons(false);
        return Inertia::render('subscription/pages/SubscriptionPlan', array_merge($getSubscriptionDetail, compact(
            'type',
            'coupon',
        )));
    }


    /**
     * to load subscription upgrade page
     *
     * @param $type string
     *
     * @param $request request
     */
    public function viewUpgradePlanSelect($type, Request $request)
    {
        $getSubscriptionDetail = $this->getAllSubscription($type);
        $currentSubscriptionPlan = $getSubscriptionDetail['currentSubscriptionPlan'];

        $userIsEligibleForPromo = $this->isEligibleForYearlyPlanPromo($request->user(), $currentSubscriptionPlan);
        $userPlan = $currentSubscriptionPlan->subscriptionPlan->plan;
        $userPlanPeriod = $currentSubscriptionPlan->subscriptionPlanPrice->subscription_plan_type;

        $canUserDiscountPlan = function ($expectedPlan) use ($userPlan, $userPlanPeriod) {
            return $this->canDiscountPlan($userPlan, $userPlanPeriod, $expectedPlan);
        };

        // Check for eligible user for yearly Square plan promo
        if ($userIsEligibleForPromo) {
            foreach ($getSubscriptionDetail['allSubscriptionPlan'] as $subscriptionPlan) {
                if ($subscriptionPlan->plan === 'Free') {
                    continue;
                }

                if (
                    (
                        $subscriptionPlan->plan === 'Square' &&
                        $canUserDiscountPlan('Square')
                    ) ||
                    (
                        $subscriptionPlan->plan === 'Triangle' &&
                        $canUserDiscountPlan('Triangle')
                    ) ||
                    (
                        $subscriptionPlan->plan === 'Circle' &&
                        $canUserDiscountPlan('Circle')
                    )
                ) {
                    $subscriptionPlan->subscriptionPlanPrice = $this->discountPlanPrice($subscriptionPlan);
                }
            }
        }

        // get promo countdown timer (in hours)
        $promoRemainingHours = $userIsEligibleForPromo
            ? $this->getRemainingPromoHours($request->user())
            : 0;

        $logoutLink = route('logout');
        $coupon = $this->getYearlySubscriptionCoupons(false);
        return Inertia::render('subscription/pages/SubscriptionPlan', array_merge($getSubscriptionDetail, compact(
            'type',
            'currentSubscriptionPlan',
            'promoRemainingHours',
            'logoutLink',
            'coupon',
        )));
    }

    private function canDiscountPlan($plan, $period, $expectedPlan): bool
    {
        $plansForYearlySquarePromo = ['Free'];
        $plansForYearlyTrianglePromo = array_merge($plansForYearlySquarePromo, ['Square']);
        $plansForYearlyCirclePromo = array_merge($plansForYearlyTrianglePromo, ['Triangle']);

        if ($expectedPlan === 'Square') {
            $plansForPromo = $plansForYearlySquarePromo;
        } else if ($expectedPlan === 'Triangle') {
            $plansForPromo = $plansForYearlyTrianglePromo;
        } else {
            $plansForPromo = $plansForYearlyCirclePromo;
        }

        return $this->isPlanMonthly($plan, $period, $expectedPlan) ||
            in_array($plan, $plansForPromo, true);
    }

    /**
     * Discount YEARLY subscription plan to provided price
     *
     * @param $subscriptionPlan
     * @return mixed
     */
    private function discountPlanPrice($subscriptionPlan)
    {
        return $subscriptionPlan->subscriptionPlanPrice->map(function ($p) {
            $p->price = $p->subscription_plan_type === 'yearly'
                ? (float)$p->price * (self::PERCENTAGE_DISCOUNT / 100)
                : $p->price;

            return $p;
        });
    }

    /**
     * Simple func to check whether plan provided is the same with
     * expected plan and its period is monthly
     *
     * @param $plan
     * @param $period
     * @param $expectedPlan
     * @return bool
     */
    private function isPlanMonthly($plan, $period, $expectedPlan): bool
    {
        return $plan === $expectedPlan && $period === 'monthly';
    }

    /**
     * Wrapper for HTTP req endpoint on isEligibleForYearlyPlanPromo method
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkNewUserPromoEligibility(Request $request)
    {
        $getSubscriptionDetail = $this->getAllSubscription($request->type);

        return response()->json([
            'isEligible' => $this->isEligibleForYearlyPlanPromo($request->user(), $getSubscriptionDetail['currentSubscriptionPlan'])
        ]);
    }

    /**
     * For new user to be eligible for yearly paid plan promotion, he/she must:
     *
     * - Owned/Registered hypershapes account for at least 14 days
     * - Free plan or paid monthly plan
     *
     * @param \App\User $user
     * @param $subscription
     * @return bool
     */
    private function isEligibleForYearlyPlanPromo(User $user, $subscription): bool
    {
        // $plan = $subscription->subscriptionPlan->plan;
        // $price = $subscription->subscriptionPlanPrice;

        // // skip plans other than Free with undetectable period
        // if ($plan !== 'Free' && is_null($price)) {
        //     return false;
        // }

        // // only monthly paid plan is eligible for promo
        // if ($plan !== 'Free' && $price->subscription_plan_type === 'yearly') {
        //     return false;
        // }

        // $creationTime = $user->created_at->diffInHours(now());
        // $promotionEnds = self::MIN_USER_CREATION_HOURS + self::MAX_PROMO_HOURS;

        // return $creationTime >= self::MIN_USER_CREATION_HOURS
        //     && $creationTime <= $promotionEnds;
        return false;
    }

    private function getRemainingPromoHours(User $user)
    {
        $creationHours = $user->created_at->diffInHours(now());
        return self::MIN_USER_CREATION_HOURS + self::MAX_PROMO_HOURS - $creationHours;
    }

   /**
     * check promo code is it valid
     *
     * @param $request request
     */
    public function validatePromoCode(request $request)
    {
        $promoCode = PromoCode::where('promo_code',$request->promoCode)->first();
        $status = 'Promo Code Not Available';
        //check promo code is it exists
        if($promoCode){
            //check is it expired
            $isExpired = $promoCode->expire ? strtotime('now') >= strtotime($promoCode->expire) : false;
            //check is it over usage limit
            $isExceedLimit = $promoCode->usage_limit ? $promoCode->usage >= $promoCode->usage_limit : false;
            // return success or error message
            !$isExpired && !$isExceedLimit ? $status = 'Promo Code Available' : $status = 'Promo Code Expired Or Fully Redeem';
        }
        if(!empty($promoCode->reference_id) && $status === 'Promo Code Available'){
            $this->integrateStripeApi();
            header('Content-Type: application/json');
            $promoCode = \Stripe\PromotionCode::retrieve($promoCode->reference_id);
        }
        return response()->json([
            'status' => $status,
            'promoCode' => $promoCode ?? null
        ]);
    }

    /*
     * get yearly subscription coupons for free plan user
     */
    public function getYearlySubscriptionCoupons($hasResponse = true)
    {
        $isProduction = app()->environment() === 'production';
        $featureStartedAt = $isProduction ? new Carbon(self::YEARLY_PROMO_START_AT) : Carbon::now();
        $duration = 7;

        $accountId = $this->getCurrentAccountId();
        $account = Account::find($accountId);
        $isAfterDiscountStarted = $account->created_at > $featureStartedAt;
        $emailVerifiedAt = new Carbon($account->user->first()->email_verified_at);

        $discountStartedAt = $isAfterDiscountStarted ? $emailVerifiedAt : $featureStartedAt;
        $discountRate = 80;
        // To determine rate of old & new user
        // $discountRate = $isAfterDiscountStarted ? 20 : 50;

        $couponID = 'yearly-subscription-discount-' . $discountRate;

        $this->integrateStripeApi();
        header('Content-Type: application/json');

        $isDiscountStarted = Carbon::now() >= $discountStartedAt;
        $isMontlyPlan = false;
        if (isset($account->subscription->subscriptionPlanPrice)) {
            $isMontlyPlan = $account->subscription->subscriptionPlanPrice['subscription_plan_type'] === 'monthly';
        }

        $hasSubscribedYearlyPlan = false;
        if(isset(Auth::user()->customer_id) && $hasResponse){
            try {
                $subscription = \Stripe\Invoice::all(['customer' => Auth::user()->customer_id])->data[0]->lines->data;
                $yearlySubscription =  array_filter(
                    $subscription,
                    function ($item) {
                        return $item->plan->interval === 'year';
                    }
                );
                $hasSubscribedYearlyPlan = count($yearlySubscription) > 0;
            } catch (\Throwable $th) {}
        }

        // $eligibleForPromo = ($account->subscription_plan_id === 1 || $isMontlyPlan)
        //     && !$hasSubscribedYearlyPlan
        //     && $isDiscountStarted
        //     && $discountStartedAt->diffInDays(Carbon::now()) < $duration;
        $eligibleForPromo = false;

        if ($eligibleForPromo) {
            try {
                $coupon = \Stripe\Coupon::retrieve($couponID);
            } catch (\Throwable $th) {
            }
        }

        if ($hasResponse)
            return response()->json([
                'createAt' => $account->created_at,
                'featureStartedAt' => $featureStartedAt,
                'couponID' => $couponID,
                'coupon' => $coupon ?? null,
                'eligibleForPromo' => $eligibleForPromo,
                'discountStartedAt' => $discountStartedAt,
                'discountRate' => $discountRate,
                'duration' =>  $duration,
                'hasSubscribedYearlyPlan' => $hasSubscribedYearlyPlan,
                'yearlySubscription' => $yearlySubscription ?? '',
            ]);
        return $coupon ?? null;
    }


    /**
     * create subscription
     *
     * @param $request request
     */
    public function makeRecurringPayment(request $request)
    {
        //integrate stripe Api
        $this->integrateStripeApi();
        header('Content-Type: application/json');
        //create stripe customer
        $user = User::where('currentAccountId',$this->current_accountId())->first(); // can use auth user
        $customerId = $user->customer_id;
        $selectedPlan = $request->selectedPlan;
        $plan = $selectedPlan['plan'] ?? null;
        $period = $selectedPlan['subscription_plan_type'] ?? null;
        $eligibleForPromo = $request->newUserPromo && $plan !== 'Free' && $period !== 'monthly';

        //if customer id not exists then create
        if($customerId == null)
        {
            $customer = \Stripe\Customer::create(['name' => $user->firstName . $user->lastName, 'email' => $user->email]);
            $user->customer_id = $customer->id;
            $user->save();
            $customerId = $customer->id;
        }
        //attach stripe customer to payment methods
        try
        {
            $payment_method = \Stripe\PaymentMethod::retrieve($request->paymentMethodId);
            $payment_method->attach(['customer' => $customerId]);
        }
        catch(Exception $e)
        {
			return response()->json($e->getJsonBody());
		}
        // Set the default payment method on the customer
        \Stripe\Customer::update($customerId, ['invoice_settings' => ['default_payment_method' => $request->paymentMethodId]]);
        //apply promo code if exists
        $newSubscription = [
            'customer' => $customerId,
            'items' => [['price' => $request->priceId]],
            'expand' => ['latest_invoice.payment_intent'],
        ];

        $coupon =null;
        if($selectedPlan['subscription_plan_type'] === 'yearly'){
            $coupon =  $this->getYearlySubscriptionCoupons(false);
        }

        if($request->promoCodeType === "success")
        {
            $promoCode = PromoCode::where('promo_code',$request->promoCode)->first();
            $promoCode->usage += 1;
            $promoCode->save();
            $newSubscription['trial_period_days'] = $promoCode->trial_or_discount;
            if($promoCode->reference_id){
                $newSubscription['promotion_code'] = $promoCode->reference_id;
            }
        }
        else if (isset($coupon)) {
            $newSubscription['coupon'] = $coupon->id;
        } else {
            $newSubscription['coupon'] = $eligibleForPromo ?  $this->getYearlyPlanPromoCouponId() : '';
        }

        // Create the subscription
        $subscriptionDetail = \Stripe\Subscription::create($newSubscription);
        $planId = $this->getSubscriptionId($request->priceId);
        return response()->json(['subscriptionDetail' => $subscriptionDetail, 'planId' => $planId, 'cardDetail' => $payment_method]);
    }

    /**
     * save retry invoice
     *
     * @param $request request
     */
    public function saveRetryInvoice(request $request)
    {
        //create retry invoice
        RetryInvoice::create(
            [
                'account_id' => $this->current_accountId(),
                'latest_invoice_payment_intent_status' => $request->status,
                'latest_invoice_id' => $request->invoiceId,
                'expire_date' => date("Y-m-d H:i:s", strtotime('+1 days')),
            ]
        );
    }

     /**
     * get retry invoice
     */
    public function getRetryInvoice(){
        //get valid retry invoice
        $retryInvoice = RetryInvoice::where('account_id',$this->current_accountId())
        ->where('expire_date','>=',date("Y-m-d H:i:s"))
        ->first();
        return response()->json($retryInvoice);
    }

    /**
     * delete retry invoice
     */
    public function deleteRetryInvoice(){
        $retryInvoice = RetryInvoice::where('account_id',$this->current_accountId())->first();
        $retryInvoice->delete();
    }

    /**
     * retry invoice with a new payment method
     *
     * @param $request request
     */
    public function retryInvoiceWithNewPaymentMethod(request $request)
    {
        $this->integrateStripeApi();
        header('Content-Type: application/json');
        //create stripe customer
        $user = User::where('currentAccountId',$this->current_accountId())->first();
        $customerId = $user->customer_id;
        if($user->customer_id == null)
        {
            $customer = \Stripe\Customer::create(['name' => $user->firstName . $user->lastName,'email' => $user->email]);
            $user->customer_id = $customer->id;
            $user->save();
            $customerId = $customer->id;
        }
         //attach stripe customer to payment methods
        try
        {
            $payment_method = \Stripe\PaymentMethod::retrieve($request->paymentMethodId);
            $payment_method->attach(['customer' => $customerId]);
        }
        catch (Exception $e)
        {
            return response()->json($e->getJsonBody());
        }
        // Set the default payment method on the customer
        \Stripe\Customer::update($customerId, ['invoice_settings' => ['default_payment_method' => $request->paymentMethodId]]);
        $invoice = \Stripe\Invoice::retrieve($request->invoiceId,['expand' => ['payment_intent']]);
        $paymentIntent = \Stripe\PaymentIntent::retrieve($invoice->payment_intent);
        return response()->json(['invoice'=>$invoice,'paymentIntent'=>$paymentIntent]);
    }

    /**
     * save credit card detail
     *
     * @param $request request
     */
    public function saveCardDetail(request $request)
    {
        $paymentMethod =  $request->paymentMethod;
        $expireMonth = sprintf("%02d",$paymentMethod['card']['exp_month']);
        $expireYear = substr($paymentMethod['card']['exp_year'],2,2);
        $expireDate = $expireMonth."/".$expireYear;
        $cardDetail =  CreditCardDetail::firstOrNew(['account_id' => $this->current_accountId()]);
        $cardDetail->last_4_digit = $paymentMethod['card']['last4'];
        $cardDetail->card_types = $paymentMethod['card']['brand'];
        $cardDetail->expire_date = $expireDate;
        $cardDetail->payment_method_id = $paymentMethod['id'];
        $cardDetail->card_holder_name = $request->cardHolder === null ? $cardDetail->card_holder_name : $request->cardHolder;
        $cardDetail->save();
    }

     /**
     * save subscription detail
     *
     * @param $request request
     */
    public function saveSubscriptionDetail(request $request)
    {
        if(!isset($request->selectedPlan)) abort(500, 'No Subscription Plan Selected');
        
        $subscriptionDetail = $request->subscriptionDetail;
        //default for free plan
        if(!$subscriptionDetail){
            $subscriptionDetail = [
                'id' => null,
                'current_period_start' => null,
                'current_period_end' => null,
                'trial_start' => null,
                'trial_end' => null,
                'status' => 'active'
            ];
        }
         //get current date
        $currentDate = date('Y/m/d H:i:s',$subscriptionDetail['current_period_start']) ?? date('Y/m/d H:i:s');
        $account = Account::find($this->current_accountId())->update(
            [
                'subscription_plan_id' => $request->selectedPlan['subscription_plan_id'],
                'subscription_status' => $subscriptionDetail['status']
            ]
        );
        Subscription::updateOrCreate(
            ['account_id'=> $this->current_accountId()],
            [
                'user_id' => Auth::user()->id,
                'subscription_id' => $subscriptionDetail['id'],
                'subscription_plan_id' => $request->selectedPlan['subscription_plan_id'],
                'subscription_plan_price_id' => $request->selectedPlan['id'],
                'status' => $subscriptionDetail['status'],
                'current_plan_start' => $currentDate,
                'current_plan_end' => date('Y/m/d H:i:s',$subscriptionDetail['current_period_end']),
                'cancel_at' => null,
                'last_email_reset' => $currentDate,
                'trial_start' => date('Y/m/d H:i:s',$subscriptionDetail['trial_start']),
                'trial_end' => date('Y/m/d H:i:s',$subscriptionDetail['trial_end']),
            ]
        );
    }

    //subscription page

    public function getSubscriptionPage (request $request)
    {
        $subscriptionPlanId = Account::find($this->current_accountId())->subscription_plan_id;
        $subscription = Subscription::where('account_id',$this->current_accountId())->first();
        $planName = SubscriptionPlan::find($subscriptionPlanId)->plan;
        $creditCard = CreditCardDetail::where('account_id',$this->current_accountId())->first();
        return view ('subscriptionPage',compact('subscription','planName','creditCard'));
    }

    public function getSubscription(request $request)
    {
        $this->integrateStripeApi();
        header('Content-Type: application/json');
        $subscription = \Stripe\Subscription::retrieve($request['invoice']['subscription']);
        return response()->json(['subscription'=>$subscription]);
    }

    /**
     * upgrade subscription plan
     *
     * @param $request request
     */
    public function upgradeSubscriptionPlan(request $request){
        $this->integrateStripeApi();
        $subscription = Subscription::where('account_id',$this->current_accountId())->first();
        $customerId = User::where('currentAccountId',$this->current_accountId())->first()->customer_id;
        $selectedPlan = $request->selectedPlan;
        $priceId = $selectedPlan['price_id'] ?? null;
        $plan = $selectedPlan['plan'] ?? null;
        $period = $selectedPlan['subscription_plan_type'] ?? null;
        $eligibleForPromo = $request->promo && $plan !== 'Free' && $period !== 'monthly';

        if($subscription->subscription_plan_id !== 1)
        {
            //get subscription detail from stripe through API
            $retrieveSubscription = \Stripe\Subscription::retrieve($subscription->subscription_id);
            // if upgrade
            if ($request->type === 'upgrade') {
                $coupon = null;
                if ($selectedPlan['subscription_plan_type'] === 'yearly') {
                    $coupon =  $this->getYearlySubscriptionCoupons(false);
                }

                $data = [ 
                    // cancel subscription immediately
                    'cancel_at_period_end' => false,
                    //user will pay immediately when plan upgrade
                    'billing_cycle_anchor' => 'now',
                    // the upgrade fees will be charge after deduced the old plan remaining time
                    'proration_behavior' => 'create_prorations',
                    'items' => [
                        [
                            'id' => $retrieveSubscription->items->data[0]->id,
                            'price' => $priceId
                        ]
                    ]
                ];

                if (isset($coupon)) {
                    $data['coupon'] = $coupon->id;
                } else {
                    $data['coupon'] = $eligibleForPromo ?  $this->getYearlyPlanPromoCouponId() : '';
                }
                // update subscription throght APi
                $updateSubscription = \Stripe\Subscription::update($subscription->subscription_id, $data);
            }
            // reactive subscription plan
            else
            {
                $updateSubscription = \Stripe\Subscription::update($subscription->subscription_id, [
                    // cancel subscription immediately
                    'cancel_at_period_end' => false,
                    'billing_cycle_anchor' => 'now',
                    'proration_behavior' => 'create_prorations',
                ]);
            }
        }
        //if subscription id not exists
        else
        {
            if(!$subscription->subscription_id)
            {
                //create subscription
                $updateSubscription = \Stripe\Subscription::create([
                    'customer' => $customerId,
                    'items' => [['price' => $priceId]],
                    'expand' => ['latest_invoice.payment_intent'],
                    'coupon' => $eligibleForPromo
                        ? $this->getYearlyPlanPromoCouponId()
                        : '',
                ]);
            }
        }
            $account = Account::where('id',$this->current_accountId())->update([
                'subscription_plan_id' => $request->selectedPlan['subscription_plan_id'] ?? $subscription->subscription_plan_id ,
                'subscription_status' =>  $updateSubscription['status'],
            ]);
            $subscription = Subscription::where('account_id',$this->current_accountId())->update([
                'account_id' => $this->current_accountId(),
                'user_id' => Auth::user()->id,
                'subscription_id' => $updateSubscription['id'],
                'subscription_plan_id' =>  $request->selectedPlan['subscription_plan_id'] ?? $subscription->subscription_plan_id,
                'subscription_plan_price_id' =>  $request->selectedPlan['id'] ?? $subscription->subscription_plan_price_id,
                'status' => $updateSubscription['status'],
                'current_plan_start' =>  date('Y/m/d H:i:s',$updateSubscription['current_period_start']) ?? date('Y/m/d H:i:s'),
                'current_plan_end' => date('Y/m/d H:i:s',$updateSubscription['current_period_end']),
                'trial_start' => date('Y/m/d H:i:s',$updateSubscription['trial_start']),
                'trial_end' => date('Y/m/d H:i:s',$updateSubscription['trial_end']),
                'cancel_at' => null
            ]);
    }

    /**
     * as of Sept 2021, coupon below should give 50% discount to yearly plan
     *
     * @return string
     */
    private function getYearlyPlanPromoCouponId(): string
    {
        $env = app()->environment();

        if ($env === 'local') {
            return '7yZf0wlJ';
        }

        // staging things are in Steve sub-account
        if ($env === 'staging') {
            return '';
        }

        if ($env === 'production') {
            return 'WhGQBSXm';
        }

        return '';
    }

     /**
     * terminate subscription plan
     */
    public function terminateSubscription()
    {
        $this->integrateStripeApi();
        $subscription = Subscription::where('account_id',$this->current_accountId())->first();
        //terminate subscription on plan end
        $cancelSubscription = \Stripe\Subscription::update($subscription->subscription_id, ['cancel_at_period_end' => true,]);
        Account::find($this->current_accountId())->update(['subscription_status' => $cancelSubscription->status]);
        $subscription->update(['status'=>$cancelSubscription['status'],'cancel_at' => date('Y/m/d H:i:s',$cancelSubscription['cancel_at'])]);
    }


     /**
     * terminate subscription plan
     *
     * @param $request request
     */
    public function changePaymentMethods(request $request)
    {
        $this->integrateStripeApi();
        header('Content-Type: application/json');
        //create stripe customer
        $user = User::where('currentAccountId',$this->current_accountId())->first();
        $customerId = $user->customer_id;
        // create customer when customer id not exists
        if($user->customer_id == null)
        {
            $customer = \Stripe\Customer::create(['name' => $user->firstName . $user->lastName,'email' => $user->email]);
            $user->customer_id = $customer->id;
            $user->save();
            $customerId = $customer->id;
        }
        //attach stripe customer to payment methods
        try
        {
            $payment_method = \Stripe\PaymentMethod::retrieve($request->paymentMethodId);
            $payment_method->attach(['customer' => $customerId]);
        }
        catch (Exception $e)
        {
            return response()->json($e->getJsonBody());
        }
        // Set the default payment method on the customer
        \Stripe\Customer::update($customerId, ['invoice_settings' => ['default_payment_method' => $request->paymentMethodId]]);
        return response()->json($payment_method);
    }

    /**
     * create subscription logs
     *
     * @param $subscriptionDetail object
     *
     * @param $event object
     */
    public function createSubscriptionLogs($subscriptionDetail,$event)
    {
        subscriptionLogs::create([
            'subscription_id' => $subscriptionDetail->subscription,
            'status' =>  $event->type,
            'amount_paid' => $subscriptionDetail->amount_paid,
            'invoice_pdf_url' => $subscriptionDetail->invoice_pdf,
            'recieved_time' => date('Y/m/d H:i:s',$event->created),
        ]);
        if($subscriptionDetail->amount_paid === 0) return;
        app('App\Http\Controllers\SubscriptionInvoiceController')->createSubscriptionInvoice($subscriptionDetail,$event);
    }

    /**
     * upgrade subscription detail
     *
     * @param $subscriptionDetail object
     */
    public function updateSubscriptionDetail($subscriptionDetail)
    {
        $user = User::where('customer_id',$subscriptionDetail->customer)->first();

        // Reset to free plan after subscription cancelled
        if($subscriptionDetail->status === self::STRIPE_SUBSCRIPTION_CANCELED_STATUS){
            $freePlanId = SubscriptionPlan::firstWhere('plan','Free')->id;
            $freePlanPriceId = SubscriptionPlanPrice::firstWhere(['subscription_plan_id' => $freePlanId, 'subscription_plan_type' => 'yearly'])->id;
            Subscription::firstWhere('account_id',$user->currentAccountId)->update([
                'subscription_id' => null, // Remember to reset subscription_id, since subscription will be deleted by stripe after canceled
                'subscription_plan_id' => $freePlanId,
                'subscription_plan_price_id' => $freePlanPriceId,
                'status' => self::STRIPE_SUBSCRIPTION_CANCELED_STATUS,
                'current_plan_start' => Carbon::now(),
                'current_plan_end' => '1970-01-01 07:30:00',
                'trial_start' => '1970-01-01 07:30:00',
                'trial_end' => '1970-01-01 07:30:00',
                'cancel_at' => null,
            ]);
            Account::find($user->currentAccountId)->update([
                'subscription_plan_id' => $freePlanId,
                'subscription_status'=> self::STRIPE_SUBSCRIPTION_CANCELED_STATUS,
            ]);
            return;
        }

        $priceId = optional($subscriptionDetail->plan)->id;
        if (!isset($priceId) && isset($subscriptionDetail->items['data'][0]['plan']))
            $priceId = $subscriptionDetail->items['data'][0]['plan']['id'];

        $subscriptionPlanPrice = SubscriptionPlanPrice::firstWhere('price_id', $priceId ?? '');
        if(!isset($subscriptionPlanPrice->subscriptionPlan)) return;
        
        $subscriptionPlanId = $subscriptionPlanPrice->subscriptionPlan->id;
        $subscriptionPlanPriceId = $subscriptionPlanPrice->id;
        $subscription = Subscription::updateOrCreate(
            ['account_id' => $user->currentAccountId],
            [
                'user_id' => $user->id,
                'subscription_id' => $subscriptionDetail->id,
                'subscription_plan_id' => $subscriptionPlanId,
                'subscription_plan_price_id' => $subscriptionPlanPriceId,
                'status' => $subscriptionDetail->status,
                'current_plan_start' => date('Y/m/d H:i:s',$subscriptionDetail->current_period_start),
                'current_plan_end' => date('Y/m/d H:i:s',$subscriptionDetail->current_period_end),
                'trial_start' => date('Y/m/d H:i:s',$subscriptionDetail->trial_start),
                'trial_end' => date('Y/m/d H:i:s',$subscriptionDetail->trial_end),
                'cancel_at' => date('Y/m/d H:i:s',$subscriptionDetail->canceled_at),
            ]
        );
        Account::find($user->currentAccountId)->update(
            [
                'subscription_plan_id' => $subscriptionPlanId,
                'subscription_status'=>$subscriptionDetail->status
            ]
        );
    }

    /**
     * get response for webhook stripe
     */
    public function getWebhookForRecurringBilling()
    {
        $payload = @file_get_contents('php://input');
        $event = null;
        try
        {
            $event = \Stripe\Event::constructFrom(json_decode($payload, true));
        }
        catch(\UnexpectedValueException $e)
        {
            // Invalid payload
            http_response_code(400);
            exit();
        }
        // Handle the event
        switch ($event->type) {
            case 'invoice.paid':
            $this->createSubscriptionLogs($event->data->object,$event);
            break;

            case 'customer.subscription.created':
            $this->updateSubscriptionDetail($event->data->object);
            break;

            case 'customer.subscription.deleted':
            $this->updateSubscriptionDetail($event->data->object);
            break;

            case 'customer.subscription.trial_will_end':
            $this->updateSubscriptionDetail($event->data->object);
            break;

            case 'customer.subscription.updated':
            $this->updateSubscriptionDetail($event->data->object);
            break;

            case 'promotion_code.created':
            $promoCode = $event->data->object;
            PromoCode::create([
                'promo_code' => $promoCode->code,
                'reference_id' => $promoCode->id,
                'trial_or_discount' => 0,
                'expire' => $promoCode->expires_at ? DateTime::createFromFormat( 'U', $promoCode->expires_at) : null,
                'usage' => 0,
                'usage_limit' => $promoCode->max_redemptions,
            ]);
            break;

            default:
            http_response_code(400);
            exit();
        }
		http_response_code(200);
    }

    public function getSubscriptionPlans(){
        $subscriptionPlans = SubscriptionPlan::with('subscriptionPlanPrice')
        ->where('plan', '!=', 'Square +')
        ->where('plan', '!=', 'Triangle +')
        ->where('plan', '!=', 'Circle +')->get();
        return response()->json(['plan' => $subscriptionPlans]);
    }
}
