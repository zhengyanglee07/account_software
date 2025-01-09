<?php

namespace App\Http\Controllers;

use App\ProcessedContact;
use App\ReferralCampaign;
use App\Segment;
use App\Account;
use App\AccountDomain;
use App\Currency;
use App\OrderDetail;
use App\Services\ContactCurrencyService;
use App\Traits\CurrencyConversionTraits;
use App\Traits\NoteTrait;
use App\Traits\SegmentTrait;
use App\Traits\AuthAccountTrait;
use App\UsersProduct;
use Illuminate\Http\Request;
use Auth;
use App\ProcessedTag;
use App\peopleCustomFieldName;
use App\ProcessedAddress;
use App\LandingPageFormContent;
use App\EcommerceVisitor;
use App\EcommerceTrackingLog;
use App\EcommercePage;
use App\EcommerceAbandonedCart;
use App\Order;
use App\funnel;
use App\Page;
use App\LandingPageForm;
use App\Services\ProductCourseService;
use Carbon\Carbon;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

use App\Traits\DatatableTrait;

class ProfileController extends Controller
{
    use NoteTrait, SegmentTrait, CurrencyConversionTraits, AuthAccountTrait, DatatableTrait;

    private $contactCurrencyService;

    public function __construct(ContactCurrencyService $contactCurrencyService)
    {
        $this->contactCurrencyService = $contactCurrencyService;
    }

    private function currentAccountId()
    {
        return Auth::user()->currentAccountId;
    }

    public function index(Request $request, $accountRandomId = null)
    {
        $tags = [];
        $conditionJson = $this->getConditionsJson();
        $allFunnels = funnel::with('landingpages')->select('id', 'funnel_name')->get();
        $allEcommercePages = EcommercePage::all();
        $allForms = LandingPageForm::where('account_id', $this->currentAccountId())->get();
        $allSegments = Segment::where('account_id', $this->currentAccountId())->get();
        $customField = peopleCustomFieldName::whereAccountId($this->currentAccountId())->get();
        $processedTags = ProcessedTag::where('account_id', $this->currentAccountId())->where('typeOfTag', 'contact')->get();

        foreach ($processedTags as $processedTag) {
            $tags[] = [
                'tagId' => $processedTag->id,
                'tagName' => $processedTag->tagName
            ];
        }

        // get custom field names for "Custom Field" sub filter in condition filter
        $customFieldNames = peopleCustomFieldName::whereAccountId($this->currentAccountId())
            ->pluck('custom_field_name')
            ->filter()
            ->unique();

        $paginatedPeople = $this->paginateRecords(
            ProcessedContact::where('account_id', $this->currentAccountId()),
            $request->query(),
            ['email', 'fname', 'lname'],
        );

        $contacts = $this->contactCurrencyService->mapContactsValuesBasedOnCurrency(
            $paginatedPeople->getCollection(),
            $this->currentAccountId(),
        );

        $currency = Account::findOrFail($this->currentAccountId())->currency;
        $exchangeRate = Currency::firstWhere(['account_id' => $this->currentAccountId(), 'currency' => $currency])->exchangeRate ?? 1;
        $usersProducts = UsersProduct::where('account_id', $this->currentAccountId())->get();

        return Inertia::render('people/pages/AllPeople', compact(
            "currency",
            "contacts",
            "conditionJson",
            "tags",
            "customFieldNames",
            "customField",
            "usersProducts",
            "exchangeRate",
            "paginatedPeople",
            "allFunnels",
            "allEcommercePages",
            "allForms",
        ));
    }

    /**
     * People profile page
     * People page onclick view button then direct user to people profile page
     *
     * @param $contactRandomId
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function view($contactRandomId, Request $request)
    {
        $currentAccountId = $this->getCurrentAccountId();
        $account = Account::findOrFail($currentAccountId);
        $currency = $account->currency === 'MYR' ? 'RM' : $account->currency;
        $exchangeRate = Currency::firstWhere(['account_id' => $currentAccountId, 'currency' => $account->currency])->exchangeRate ?? 1;
        $currencies = Currency::where('account_id', $currentAccountId)->pluck('exchangeRate', 'currency')->toArray();
        $domain = AccountDomain::where('account_id', $currentAccountId)->get()->pluck('domain', 'type');
        $funnelDomains = funnel::select(['id', 'funnel_name', 'domain_name', 'reference_key'])->where('account_id', $currentAccountId)->get();
        $accountRandomId = $account->accountRandomId;

        /**
         * Important note down there regarding $from and $ref:
         *
         * Both of them are used as query string/param, as you
         * seen below.
         *
         * $from has only two possible value as of Aug 2020:
         * 'people' or 'segment', and it's default to 'people'
         * to provide backward compatibility for old url without
         * query string
         *
         * e.g. /people/view/123456789, make sure it's able to
         * access people profile page without any problem
         *
         * new url with query string from either people/view segment
         * page's datatable are:
         * - /people/view/123456789?from=segment&ref=segment_key => view segment
         * - /people/view/123456789?from=people => people
         *
         * If you wonder why people page one doesn't have ref,
         * because accountRandomId is already provided as a props
         * to PeopleProfileHome component, which is important
         * for generating "Back to People" link, so ref is not
         * necessarily required if $from === 'people'.
         *
         * from and ref query strings will be used to generate
         * link for "Back to People" or "Back to Segment" respectively.
         *
         */
        $from = $request->query('from') ?? 'people';
        $refKey = $request->query('ref'); // ref key (segments)


        $processedContact = ProcessedContact
            ::with('peopleCustomFields.peopleCustomFieldName', 'processed_tags', 'orders', 'storeCredits')
            ->where(['contactRandomId' => $contactRandomId, 'account_id' => $currentAccountId])
            ->firstOrFail();

        // convert value based on currency, don't save this
        $processedContact->credit_balance = $this->convertCurrency(
            $processedContact->credit_balance,
            $account->currency
        );

        $processedContact->refer = $this->getReferData($processedContact->id);
        $processedContact->referBy = $this->getReferByData($processedContact->id);

        $allNotes = $this->getGroupedNotesByDate($processedContact->notes);

        $allAddress = ProcessedAddress::whereProcessedContactId($processedContact->id)->first();
        $marketingEmailStatus = $processedContact->marketingEmailStatus();

        // Note: this is remaining unused account's custom field (names),
        //       NOT all custom field names from an account
        $customFieldNames = peopleCustomFieldName
            ::where('account_id', $currentAccountId)
            ->whereNotIn(
                'custom_field_name',
                $processedContact->peopleCustomFields->pluck('peopleCustomFieldName.custom_field_name')
            )
            ->get();

        $orders = Order::where('processed_contact_id', $processedContact->id)->get();
        $orderDetails = $this->getOrderDetail($orders, $currency);
        $processedTags = ProcessedTag::where('account_id', $currentAccountId)->where('typeOfTag', 'contact')->get();
        $tags = [];

        foreach ($processedTags as $processedTag) {
            $tags[] = [
                'tagId' => $processedTag->id,
                'tagName' => $processedTag->tagName
            ];
        }

        $products = $this->getProductDetail();
        $trackingActivities = $this->getTrackingActivities(
            $processedContact->id,
            $currentAccountId,
            $products,
            $domain
        );
        $trackingActivitiesInDate = $trackingActivities->groupBy(function ($visitor) {
            return $visitor->created_at->toDateString();
        });
        $totalSales = $this->getTotalPrice($processedContact->orders->toArray(), false);
        $referralCampaigns = ReferralCampaign::get();

        $courseStudents = (new ProductCourseService(null, $processedContact))->getPaginatedCourseStudent();
        return Inertia::render('people/pages/PeopleProfile', compact(
            'accountRandomId',
            'allNotes',
            'currency',
            'processedContact',
            'allAddress',
            'marketingEmailStatus',
            'customFieldNames',
            'from',
            'refKey',
            'orders',
            'orderDetails',
            'trackingActivities',
            'trackingActivitiesInDate',
            'tags',
            'products',
            'domain',
            'funnelDomains',
            'exchangeRate',
            'currencies',
            'totalSales',
            'referralCampaigns',
            'courseStudents',
        ));
    }

    private function getOrder($visitor)
    {
        $orders = Order::where('id', $visitor['order_id'])->get();
        return array_map(
            function ($order) {
                return [
                    'value' => $order['order_number'],
                    'type' => 'order',
                    'payment_reference' => $order['payment_references'],
                    'created_at' => $order['created_at'],
                    'reference_key' => $order['reference_key'],
                ];
            },
            $orders->toArray()
        );
    }

    public function getTrackingActivities($processedContactId, $accountId, $products, $domains)
    {
        $visitors = EcommerceVisitor::with('activityLogs')->where([
            'account_id' => $accountId,
            'processed_contact_id' => $processedContactId,
        ])->latest()->get();
        $pages = Page::where('account_id', $accountId)->select(
            'id',
            'path',
            'name',
            'funnel_id',
        )->get();
        $orders = Order::where('account_id', $accountId)->select(
            'id',
            'order_number',
            'reference_key',
        )->get();
        $funnels = funnel::where('account_id', $accountId)->select(
            'id',
            'domain_name',
        )->get();
        $ecommercePages = EcommercePage::all()->pluck('name', 'id');
        $protocol = app()->environment() === 'local' ? 'http://' : 'https://';

        foreach ($visitors as $visitor) {
            $domain = $domains[$visitor?->sales_channel] ?? null;

            $visitor->activityLogs->map(function ($log) use ($protocol, $domain, $pages, $ecommercePages, $products, $orders, $funnels) {
                switch ($log->type) {
                    case 'builder-page':
                        $page = $pages->find($log->value);
                        $domain = $domain ? $domain : $funnels->find($page?->funnel_id)?->domain_name;
                        $log['name'] = $page?->name ?? "";
                        $log['url'] = $protocol . $domain . '/' . $page?->path ?? "";
                        break;
                    case 'store-page':
                        $log['name'] = $ecommercePages[$log->value];
                        break;
                    case 'product':
                    case 'add-to-cart':
                    case 'remove-from-cart':
                        $product = $products->find($log->value);
                        $log['name'] = $product['productTitle'] ?? '';
                        $log['image'] = $product['productImagePath'] ?? '';
                        $log['url'] = $protocol . $domain . '/products/' . ($product['path'] ?? '');
                        break;
                    case 'form':
                        $log['submission'] = LandingPageFormContent::with('landingPageFormLabel')->whereReferenceKey($log->value)->select(
                            'id',
                            'landing_page_form_id',
                            'landing_page_form_content',
                            'landing_page_form_label_id',
                        )->get()->map(function ($row) {
                            $row['label'] = $row['landingPageFormLabel']->landing_page_form_label;
                            return $row;
                        });
                        $form = $log?->submission?->first()?->form;
                        $log['name'] = $form?->title ?? "Form";
                        $log['url'] = "/form/" . $form?->reference_key ?? "";
                        break;
                    case 'order':
                        $order = $orders->find($log->value);
                        $log['name'] = "#" . $order['order_number'];
                        $log['url'] = '/orders/details/' . $order['reference_key'];
                        break;
                }
            });

            $visitor['lastActivity'] = $visitor->activityLogs->last();

            switch (true) {
                case $visitor->is_completed:
                    $visitor['status'] = 'completed';
                    break;
                case $visitor->abandonedCart()->value('abandoned_at'):
                    $visitor['status'] = 'abandoned-cart';
                    break;
                case Carbon::now() < $visitor['lastActivity']?->created_at->addMinutes(30):
                    $visitor['status'] = 'ongoing';
                    break;
                default:
                    $visitor['status'] = 'bounced';
            }
        }

        return $visitors;
    }

    public function getProductDetail()
    {
        $currentAccountId = $this->currentAccountId();
        $currency = Account::find($currentAccountId)->currency;
        $currency = $currency === 'MYR' ? 'RM' : $currency;
        $exceptDefault = Currency::where('account_id', $this->currentAccountId())
            ->where('isDefault', '0')
            ->where('exchangeRate', '!=', null)
            ->get();
        $allProduct = UsersProduct::where('account_id', $currentAccountId)->get();
        foreach ($allProduct as $product) {
            $product->productPrice =  $currency . " " . $this->getCurrencyRange($product->productPrice, $currency, false);
            if ($product->hasVariant === 1) {
                $minimunVariantPrice = $this->getCurrencyRange($product->variant_details->min('price'), $currency, false);
                $product->productPrice = "From " . $currency . " " . $minimunVariantPrice;
            }
        }
        return $allProduct;
    }

    public function getOrderDetail($orders, $currency)
    {
        $orderDetailArray = [];
        $currency = $currency === 'MYR' ? 'RM' : $currency;

        $orders = $orders->filter(function ($order) {
            return $order->payment_process_status === 'Success' || is_null(($order->payment_process_status));
        });
        foreach ($orders as $order) {
            $orderDetails = OrderDetail::where('order_id', $order->id)->withTrashed()->get();
            foreach ($orderDetails as $orderDetail) {
                $userProduct = $orderDetail->usersProduct()->withTrashed()->first();
                $orderDetail['image_url'] = $userProduct['productImagePath'] ?? '/images/product-default-image.png';
                $orderDetail['product_name'] = $userProduct['productTitle'] ?? 'Deleted product';
                // $orderDetail['unit_price'] = $currency.' '.number_format($orderDetail['unit_price'] / $order->exchange_rate, 2);
                if ($orderDetail->productType === 'course') {
                    $lessons = $userProduct->modules->flatMap(function ($module) {
                        return $module->lessons;
                    });
                    $orderDetail['totalLesson'] = $lessons->count();
                }
                array_push($orderDetailArray, $orderDetail);
            }
        }
        return $orderDetailArray;
    }

    public function getReferData($processedContactId)
    {
        $accountId = Auth::user()->currentAccountId;
        $accountTimeZone = Account::find($accountId)->timeZone;
        return DB::table('processed_contact_referral_campaign')->where(['refer_by' => $processedContactId])->get()->map(function ($contact) use ($accountTimeZone) {
            $processedContact = ProcessedContact::find($contact->processed_contact_id);
            $dateTime = new Carbon($contact->created_at);
            $contact->name = optional($processedContact)->fname;
            $contact->email = optional($processedContact)->email;
            $contact->joinedDate = $dateTime->timezone($accountTimeZone)->isoFormat('MMMM D, YYYY \\at h:mm a');
            return $contact;
        });
    }
    public function getReferByData($processedContactId)
    {
        return DB::table('processed_contact_referral_campaign')->where(['processed_contact_id' => $processedContactId])->get()->map(function ($contact) {
            $processedContact = ProcessedContact::find($contact->refer_by);
            $contact->name = optional($processedContact)->fname;
            $contact->email = optional($processedContact)->email;
            $contact->refKey = optional($processedContact)->contactRandomId;
            return $contact;
        });
    }
}
