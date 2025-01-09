<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

use App\ProcessedContact;
use App\Segment;
use Carbon\Carbon;
use App\Account;
use App\Currency;
use Auth;
use App\funnel;
use App\Page;
use App\LandingPageForm;
use App\Order;
use App\OrderDetail;
use App\Traits\CurrencyConversionTraits;
use App\UsersProduct;
use App\AccountDomain;
use App\AffiliateMemberAccount;
use App\AffiliateMemberCampaign;
use App\AffiliateMemberCommission;
use App\AffiliateMemberCommissionPayout;
use App\AffiliateMemberParticipant;
use App\EcommerceVisitor;
use App\ReferralCampaign;
use App\ReferralCampaignClickLog;
use App\ReferralInviteeOrder;
use App\SaleChannel;
use App\ReferralSocialShareClickLog;
use App\ProcessedContactReferralActionLog;
use App\AffiliateMemberReferralClickLog;
use App\EcommerceTrackingLog;
use Inertia\Inertia;

class ReportsController extends Controller
{
    use CurrencyConversionTraits;

    public function currentAccountId()
    {
        $user = Auth::user();
        return $user->currentAccountId;
    }

    public function funnelReport()
    {
        $currentAccountId = $this->currentAccountId();
        $timezone = Account::find($currentAccountId)->timeZone;
        $funnels = funnel::select('id', 'funnel_name', 'reference_key', 'currency')->get();

        foreach ($funnels as $funnel) {
            // TODO Darren refactor these
            $optin = [];
            $allForm = LandingPageForm::where('funnel_id', $funnel->id)->get();
            foreach ($allForm as $form) {
                $submissionDates = $form->formContents->groupBy(function ($data) use ($timezone) {
                    return $data->created_at->timeZone($timezone)->isoformat('YYYY-MM-DD');
                });
                foreach ($submissionDates as $date => $submissions) {
                    $optin[$date] = $submissions->groupBy('reference_key')->count();
                }
            }
            $funnel['optins'] = $optin;
            $allOrders = Order::where('funnel_id', $funnel->id)->get();
            foreach ($allOrders as $order) {
                $order['convertedDateTime'] = $order->created_at->timeZone($timezone)->isoformat('YYYY-MM-DD hh:mm');
            }
            $funnel['orders'] = $allOrders;
        }

        $currency = Currency::where('account_id', $currentAccountId)->get();

        return Inertia::render('report/pages/FunnelReport', compact(
            'funnels',
            'currency'
        ));
    }

    public function getFunnelReportData($funnel_id)
    {
        $currentAccountId = $this->currentAccountId();
        $allLandingPage = Page::where('account_id', $currentAccountId)
            ->where('funnel_id', $funnel_id)
            ->select('id', 'name', 'is_published', 'index')
            ->get();
        $formCount = LandingPageForm::where('account_id', $currentAccountId)
            ->where('funnel_id', $funnel_id)
            ->select('landing_id', 'submit_count')
            ->get();
        $landingpageOrder = Order::where('account_id', $currentAccountId)
            ->where('funnel_id', $funnel_id)
            ->select('id', 'landing_id', 'total', 'currency')
            ->get();
        $funnel = funnel::where('id', $funnel_id)->first();
        $funnelCurrency = $funnel->currency;
        $convertedOrder = [];
        foreach ($landingpageOrder as $order) {
            $order['total'] = $this->convertCurrency($order['total'], $order['currency'], true, true);
            $order['total'] = $this->convertCurrency($order['total'], $funnelCurrency, true, false);
            $convertedOrder[] = $order;
        }
        $currency = Currency::where('currency', $funnelCurrency)->where('account_id', $currentAccountId)->first();
        $exchangeRate = $currency->exchangeRate === '' ? $currency->suggestCurrency : $currency->exchangeRate;
        return response()->json([
            'allLandingPage' => $allLandingPage,
            'formCount' => $formCount,
            'landingpageOrder' => $convertedOrder,
            'exchangeRate' => $exchangeRate,
            'funnelCurrency' => $funnelCurrency
        ]);
    }

    public function funnelReportIndividual(funnel $funnel)
    {
        $landingPages = $funnel->landingpages()->select('id', 'name')->get();
        $landingPageIds = $landingPages->pluck('id')->toArray();

        $orders = Order::without('orderDetails', 'orderDiscount')->where([
            'account_id' => $this->currentAccountId(),
            'funnel_id' => $funnel->id,
        ])->select(
            'id',
            'processed_contact_id',
            'landing_id',
            'total',
            'created_at'
        )->get();

        $forms = LandingPageForm::with('formContents')->whereAccountId($this->currentAccountId())->whereIn(
            'landing_id',
            $landingPageIds
        )->get();

        $pageViews = EcommerceTrackingLog::where([
            'type' => 'builder-page',
        ])->whereIn(
            'value',
            $landingPageIds
        )->select(
            'value',
            'created_at'
        )->get();

        return Inertia::render('report/pages/FunnelReportIndividual', compact(
            'funnel',
            'orders',
            'forms',
            'landingPages',
            'pageViews',
        ));
    }

    public function salesReportView()
    {
        $matchAccountId = [
            'account_id' => $this->currentAccountId()
        ];
        $account = Account::find($this->currentAccountId());
        $timezone = $account->timeZone;
        $defaultCurrency = $account->currency;

        $allOrders = Order::where($matchAccountId)->where(
            function ($query) {
                $query->where('payment_process_status', 'Success')->orWhereNull('payment_process_status');
            }
        )->select(
            'id',
            'subtotal',
            'shipping',
            'taxes',
            'total',
            'created_at',
            'currency',
            'acquisition_channel',
            'refund_shipping',
            'refunded',
            'cashback_amount',
            'is_product_include_tax',
            'tax_rate',
            'shipping_tax',
        )->latest()->get()->map(function ($order) use ($account, $timezone) {
            $date = new Carbon($order['created_at'], $account->timeZone);
            // dd($timezone);
            $order['date'] = $date->toDateString();
            $order['subtotal'] = $this->convertCurrency($order['subtotal'], $order['currency'], true, true);
            $order['shipping'] = $this->convertCurrency($order['shipping'], $order['currency'], true, true);
            $order['taxes'] = $this->convertCurrency($order['taxes'], $order['currency'], true, true);
            $order['total'] = $this->convertCurrency($order['total'], $order['currency'], true, true);
            $order['refunded'] = $this->convertCurrency($order['refunded'], $order['currency'], true, true);
            $order['convertedDateTime'] = $order->created_at->timeZone($timezone)->isoformat('YYYY-MM-DD');
            $products = DB::table('order_details')->where('order_id', $order['id'])->get('product_name');
            //$discount = $this.order_discount.discount_value;
            $order['products'] = $products;
            return $order;
        });

        $currencies = Currency::where($matchAccountId)->select(
            'currency',
            'exchangeRate',
            'suggestRate'
        )->get();

        $currencies = empty($currencies) ? null : $currencies;

        $allProducts = DB::table('products')->get('title', 'id');

        return Inertia::render('report/pages/SalesReport', compact(
            'allOrders',
            'defaultCurrency',
            'currencies',
            'allProducts'
        ));
    }

    public function growthReportPreview(Request $request)
    {
        $currentAccountId = $this->currentAccountId();
        $current_account = Account::find($currentAccountId);
        $currency = $current_account->currency;

        //get all customers to pass to frontend
        $allCustomers = ProcessedContact
            ::where('account_id', $currentAccountId)
            ->get();
        //get all segment names to pass to frontend
        $segments = Segment::where('account_id', $currentAccountId)->get();
        //get all orders to pass to frontend
        $allOrders = Order
            ::with('processedContact')
            ->where('account_id', $currentAccountId)->where(
                function ($query) {
                    $query->where('payment_process_status', 'Success')->orWhereNull('payment_process_status');
                }
            )
            ->orderBy('order_number', 'desc')
            ->get();
        //get default currency for account
        $currency = Currency::where('account_id', $currentAccountId)->where('isDefault', 1)->first()->currency;

        //getting the default Exchange Rate

        $allOrders->map(function ($order) {
            $order['total'] = $this->convertCurrency($order['total'], $order['currency'], true, true);
            return $order;
        });
        $exchangeRate = DB::table('currencies')->where('account_id', $currentAccountId)->get('exchangeRate')->first();
        $suggestRate = DB::table('currencies')->where('account_id', $currentAccountId)->get('suggestRate')->first();
        $defaultExchangeRate = $exchangeRate === 1 ? $suggestRate : $exchangeRate;

        return Inertia::render('report/pages/GrowthReport', compact(
            'allCustomers',
            'segments',
            'allOrders',
            'currency',
            'defaultExchangeRate',
        ));
    }

    public function productReport()
    {
        $account = Account::find($this->currentAccountId());
        $defaultCurrency = $account->currency;

        $products = UsersProduct::where('account_id', $account->id)->get();

        $orders = Order::where('account_id', $account->id)->where(function ($query) {
            $query->where('payment_process_status', 'Success')->orWhereNull('payment_process_status');
        })->get();

        $visitors = EcommerceVisitor::where('account_id', $account->id)->withWhereHas('activityLogs', function ($query) {
            $query->where('type', 'product')->whereNotNull('value');
        })->get();

        $currency = Currency::where(
            'account_id',
            $account->id
        )->get()->pluck('suggestRate', 'currency');

        return Inertia::render('report/pages/ProductReport', compact(
            'orders',
            'products',
            'visitors',
            'currency',
            'defaultCurrency',
        ));
    }

    public function salesChannelReportView()
    {
        $matchAccountId = [
            'account_id' => $this->currentAccountId()
        ];
        $account = Account::find($this->currentAccountId());
        $defaultCurrency = $account->currency;
        $timezone = $account->timeZone;
        $allOrders = Order::where($matchAccountId)->where(
            function ($query) {
                $query->where('payment_process_status', 'Success')->orWhereNull('payment_process_status');
            }
        )->select(
            'id',
            'processed_contact_id',
            'subtotal',
            'shipping',
            'taxes',
            'total',
            'created_at',
            'currency',
            'acquisition_channel',
            'refund_shipping',
            'refunded',
            'cashback_amount',
            'is_product_include_tax',
            'tax_rate',
            'shipping_tax',
        )->latest()->get();

        foreach ($allOrders as $order) {
            $order->convertedDateTime = $order->created_at->timeZone($timezone)->timestamp;
        }

        $currencies = Currency::where($matchAccountId)->select(
            'currency',
            'exchangeRate',
            'suggestRate'
        )->get();

        $currencies = empty($currencies) ? null : $currencies;

        return Inertia::render('report/pages/SalesChannelReport', compact('allOrders', 'defaultCurrency', 'currencies'));
    }
    public function referralReport()
    {
        $accountId = $this->currentAccountId();
        $account = Account::find($this->currentAccountId());
        $defaultCurrency = $account->currency;
        $campaigns = ReferralCampaign::get()->map(function ($campaign) use ($account) {
            $referralProcessedContacts = DB::table('processed_contact_referral_campaign')->where('referral_campaign_id', $campaign->id);
            $campaign['referralProcessedContacts'] = $this->getConvertedDateTime($referralProcessedContacts->get()->filter(function ($el) {
                return ProcessedContact::find($el->processed_contact_id);
            })->values(), $account);
            $referralCampaignClickLog = ReferralCampaignClickLog::where('referral_campaign_id', $campaign->id)->get();
            $campaign['clicks'] = $this->getConvertedDateTime($referralCampaignClickLog, $account);
            $campaign['leads'] = $this->getConvertedDateTime($referralProcessedContacts->whereNotNull('refer_by')->get(), $account);
            $referralInviteeOrder = ReferralInviteeOrder::where('referral_campaign_id', $campaign->id)->get();
            $campaign['customers'] = $this->getConvertedDateTime($referralInviteeOrder, $account);
            $allOrders = Order::with('processedContact')->where('account_id', $campaign->account_id)->whereIn(
                'id',
                $campaign['customers']->pluck('order_id')
            )->where(
                function ($query) {
                    $query->where('payment_process_status', 'Success')->orWhereNull('payment_process_status');
                }
            )->select(
                'id',
                'processed_contact_id',
                'subtotal',
                'shipping',
                'taxes',
                'total',
                'created_at',
                'currency',
                'acquisition_channel',
                'refund_shipping',
                'refunded',
                'cashback_amount',
                'is_product_include_tax',
                'tax_rate',
                'shipping_tax',
            )->latest()->get();
            $campaign['orders'] = $this->getConvertedDateTime($allOrders, $account);
            $campaign['convertedDateTime'] = $campaign->created_at->timeZone($account->timeZone)->isoformat('YYYY-MM-DD');

            return $campaign;
        });

        $referralCampaignsTitle = $campaigns->pluck('title');

        $currencies = Currency::where('account_id', $accountId)->select(
            'currency',
            'exchangeRate',
            'suggestRate'
        )->get();

        $currencies = empty($currencies) ? null : $currencies;

        return Inertia::render('report/pages/ReferralReport', compact('campaigns', 'referralCampaignsTitle', 'defaultCurrency', 'currencies'));
    }

    public function getConvertedDateTime($arr, $account)
    {
        foreach ($arr as $el) {
            $el->convertedDateTime = Carbon::parse($el->created_at)->timeZone($account->timeZone)->isoformat('YYYY-MM-DD');
        }

        return $arr;
    }

    private function actionRate($action, $participants)
    {
        switch ($action->type) {
            case 'custom':
                return  number_format(ProcessedContactReferralActionLog::where('action_id', $action->id)->get()->count() / $participants->values()->count() * 100, 2) . '%';
            case 'purchase':
                return $action->referralType === 'inviter' ? ReferralInviteeOrder::where('referral_campaign_id', $action->referral_campaign_id)->get()->count() : number_format(ReferralInviteeOrder::where('referral_campaign_id', $action->referral_campaign_id)->get()->count() / $participants->values()->count() * 100, 2) . '%';;
            case 'sign-up':
                return $action->referralType === 'inviter' ? $participants->whereNotNull('refer_by')->values()->count() : number_format($participants->whereNotNull('refer_by')->values()->count() / $participants->values()->count() * 100, 2) . '%';;
            default:
                return number_format($participants->where('is_joined', true)->values()->count() / $participants->values()->count() ?? 1 * 100, 2) . '%';
        }
    }

    private function actionTitle($type, $referralType)
    {
        switch ($type) {
            case 'sign-up':
                return $referralType === 'inviter' ? 'Refer' : 'Become' . ' a new sign up';
            case 'join':
                return 'Join this campaign';
            default:
                return $referralType === 'inviter' ? 'Refer' : 'Become' . ' a new customer';
        }
    }

    public function referralReportView($refKey)
    {

        $campaign = ReferralCampaign::where('reference_key', $refKey)->firstOrFail();
        $campaign->referralSocialNetworks();
        $salesChannel = SaleChannel::find($campaign?->sale_channel_id);

        $processedContacts = ProcessedContact::select('fname', 'lname', 'email', 'id')->where('account_id', $campaign->account_id)->whereNotNull('email')->get();
        $referralSocialShares = ReferralSocialShareClickLog::where('referral_campaign_id', $campaign->id)->get();
        $referralPageClickLogs = ReferralCampaignClickLog::where('referral_campaign_id', $campaign->id)->get();
        $referralParticipants = DB::Table('processed_contact_referral_campaign')->where(['referral_campaign_id' => $campaign?->id])->get()->filter(function ($el) {
            return ProcessedContact::find($el->processed_contact_id);
        })->values();
        $participants =  $referralParticipants->map(function ($contact) use ($processedContacts, $referralSocialShares, $referralParticipants) {
            $contact->name =  $processedContacts->find($contact?->processed_contact_id)?->fname ?? '' . ' ' . $processedContacts->find($contact?->processed_contact_id)?->lname ?? '';
            $contact->email =  $processedContacts->find($contact?->processed_contact_id)?->email ?? '-';
            $contact->shareCount = $referralSocialShares->where('processed_contact_id', $contact?->processed_contact_id)->values()->count();
            $contact->referCount = $referralParticipants->where('refer_by', $contact?->processed_contact_id)->values()->count();
            return $contact;
        });
        $actions = $campaign->actions()?->values();
        $actions = $actions?->merge($campaign->inviteeActions())?->values()->map(function ($action) use ($participants) {
            $action->rate  = $participants->count() ?  $this->actionRate($action, $participants) : '0%';
            $action->title  = $action->title ?? $this->actionTitle($action->type, $action->referralType);
            return $action;
        });
        $rewards = $campaign->rewards()->values()->map(function ($reward) use ($participants) {
            $reward->rate  =  $participants->count() ?  number_format($participants->where('point', '>=', $reward->point_to_unlock)->values()->count() / $participants->values()->count() * 100, 2) . '%' : '0%';
            return $reward;
        });
        return Inertia::render('report/pages/ReferralReportView', compact('campaign', 'participants', 'referralSocialShares', 'referralPageClickLogs', 'actions', 'rewards'));
    }

    // for affiliate member report
    public function affiliateReport()
    {
        $accountId = $this->currentAccountId();
        $account = Account::find($this->currentAccountId());
        $defaultCurrency = $account->currency;
        $commissions = AffiliateMemberCommission::where('account_id', $accountId)->get();
        $orders = $commissions->unique(function ($item) {
            return $item['order_id'] . $item['order_detail_id'];
        })->map(function ($commission) {
            $order = Order::without(['orderDetails', 'orderDiscount'])->select(['total', 'processed_contact_id', 'created_at', 'currency'])->find($commission->order_id) ?? collect();
            // if($commission->order_detail_id){
            //     $order->sale = OrderDetail::find($commission->order_detail_id)?->total ?? 0;
            // }else{
            //     $order->sale = $order?->total ?? 0;
            // }
            $order->sale = $this->convertCurrency($order?->total ?? 0, $order->currency, true, true);
            return $order;
        })->values()->all();
        $clicks = AffiliateMemberAccount::where('account_id', $accountId)->get()->flatMap(function ($member) {
            return AffiliateMemberReferralClickLog::where('referral_identifier', $member->referral_identifier)->get();
        });
        $affiliates = AffiliateMemberParticipant::without('member')->where('account_id', $accountId)->get();
        $payouts = AffiliateMemberCommissionPayout::where('account_id', $accountId)->get();
        $commissionsInfo = [
            'totalCommissions' => 0,
            'pendingCommissions' => 0,
            'approvedCommissions' => 0,
            'withdrawnCommissions' => 0,
            'disapprovedCommissions' => 0,
            'disapprovedPayouts' => 0,
        ];

        foreach ($commissions as $commission) {
            $commissionAmount = $this->convertCurrency(
                $commission->commission,
                $commission->currency,
                true,
                true
            );

            $commissionsInfo['totalCommissions'] += $commissionAmount;
            $commissionsInfo["{$commission->status}Commissions"] += $commissionAmount;
        }

        $disapprovedPayouts = 0;
        foreach ($payouts as $payout) {
            $payoutAmount = $payout->amount;
            $payoutStatus = $payout->status;

            if ($payoutStatus === 'paid') {
                $commissionsInfo['approvedCommissions'] -= $payoutAmount;
                $commissionsInfo['withdrawnCommissions'] += $payoutAmount;
            }

            if ($payoutStatus === 'disapproved') {
                $commissionsInfo['approvedCommissions'] -= $payoutAmount;
                $disapprovedPayouts += $payoutAmount;
                $commissionsInfo['disapprovedPayouts'] = $disapprovedPayouts;
            }
        }

        return Inertia::render('report/pages/AffiliateReport', compact('commissions', 'orders', 'clicks', 'affiliates', 'defaultCurrency', 'commissionsInfo'));
    }
}
