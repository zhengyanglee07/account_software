<?php

namespace App\Http\Controllers;

use Auth;
use Mail;
use Cookie;
use Inertia\Inertia;

use App\Tax;
use App\User;
use \stdClass;
use App\Order;
use App\Account;
use App\Variant;
use App\Cashback;
use App\Category;
use App\Currency;
use App\Location;
use Carbon\Carbon;
use App\PaymentAPI;
use App\TaxCountry;
use App\OrderDetail;
use App\StoreCredit;
use App\ShippingZone;
use App\UsersProduct;
use App\AccountDomain;
use App\EasyParcel;
use App\Delyva;
use App\DelyvaQuotation;
use App\productOption;
use App\ShippingMethod;
use App\VariantDetails;
use App\ProductCategory;
use App\EcommerceAccount;
use App\ProcessedAddress;
use App\ProcessedContact;
use App\TaxCountryRegion;
use App\LalamoveQuotation;
use App\OrderSubscription;
use App\OrderTransactions;
use App\EasyParcelShipment;
use App\productOptionValue;
use App\LalamoveFulfillment;
use App\ProductSubscription;

use Illuminate\Http\Request;
use App\EcommercePreferences;
use App\EasyParcelFulfillment;
use App\Events\OrderPlaced;
use App\Events\ProductPurchased;
use App\Services\SegmentService;
use App\Traits\AuthAccountTrait;
use App\Traits\UsersProductTrait;
use Illuminate\Support\Facades\DB;
use App\Jobs\ProcessTriggeredSteps;
use App\Models\Promotion\Promotion;
use App\Mail\OrderPaymentBuyerEmail;
use App\Models\Orders\OrderDiscount;
use App\Services\LalamoveAPIService;
use App\Mail\OrderPaymentSellerEmail;
use App\Services\EasyParcelAPIService;
use App\Traits\CurrencyConversionTraits;
use App\Traits\AffiliateMemberCommissionTrait;
use App\Models\Promotion\PromotionRedemptionLog;
use App\Http\Controllers\EcommerceMailsController;
use App\Http\Controllers\ProductSubscriptionController;
use App\Jobs\AutomationTriggers\TriggerPurchaseProduct;
use App\Lalamove;
use App\LegalPolicy;
use App\SaleChannel;

use App\EcommerceVisitor;
use App\EcommerceTrackingLog;
use App\EcommerceAbandonedCart;
use App\EcommercePage;

use App\Traits\PublishedPageTrait;
use App\Traits\ReferralCampaignTrait;
use App\Traits\SegmentTrait;

class OnlineStoreCheckoutController extends Controller
{
    use AuthAccountTrait, UsersProductTrait, AffiliateMemberCommissionTrait, CurrencyConversionTraits, PublishedPageTrait, ReferralCampaignTrait, SegmentTrait;

    //************************global function ********************************
    public function domainType($url)
    {
        //        $accountId = $this->domainInfo() != null
        //        ? $this->domainInfo()->account_id
        //        : Auth::user()->currentAccountId;
        $accountId = $this->getCurrentAccountId($url);

        if (!app()->environment(['local'])) {
            return AccountDomain::where('account_id', $accountId)->where('domain', $url)->first()->type;
        } else {
            return AccountDomain::where('account_id', $accountId)->first()->type;
        }
    }

    public function ecommerceUser()
    {
        return Auth::guard('ecommerceUsers')->user();
    }

    public function getDefaultCurrency()
    {
        $defaultCurrency = Currency::where('account_id', $this->getCurrentAccountId())->where('isDefault', '1')->first()->currency;
        return $defaultCurrency;
    }

    public function getStorePreferences()
    {
        return EcommercePreferences::firstWhere('account_id', $this->getCurrentAccountId());
    }

    public function getShopName($accountId)
    {
        $shopName = Account::find($accountId)->company;
        return response()->json($shopName);
    }

    public function getRandomId($table, $type)
    {
        $condition = true;
        do {
            return $randomId = random_int(100000000001, 999999999999);
            $condition = DB::table($table)->where($type, $randomId)->exists();
        } while ($condition);
    }

    public function getCountry()
    {
        $country = $_SERVER["HTTP_CF_IPCOUNTRY"];
        return response()->json(['country' => $country]);
    }

    public function getSelectedCurrency(request $request)
    {
        $currency = Currency::where('account_id', $request->accountId)
            ->where('currency', $request->currency)
            ->first();
        return response()->json($currency);
    }

    public function checkOutOfStock(request $request)
    {
        $productRefKey = [];
        foreach ($request->cartItem as $key => $item) {
            $accessTime = $request->accessTime;
            if ($item['hasVariant']) {
                $variantLatestUpdateTime = VariantDetails::where('reference_key', $item['variantRefKey'])
                    ->latest('updated_at')->first()->updated_at;
                if (isset($variantLatestUpdateTime) && strtotime($variantLatestUpdateTime) >= $accessTime) {
                    array_push($productRefKey, $item['reference_key']);
                }
            }
            if (isset($item['customizations'])) {
                $optionLatestUpdateTime = ProductOptionValue::where('product_option_id', $item['customizations'][0]['id'])
                    ->latest('updated_at')->first()->updated_at;;
                if (isset($optionLatestUpdateTime) && strtotime($optionLatestUpdateTime) >= $accessTime) {
                    array_push($productRefKey, $item['reference_key']);
                }
            }

            $productExists = true;
            if (!isset($item['product_combination_id'])) {
                $item['product_combination_id'] = null;
            }
            $productExists = UsersProduct::where(
                [
                    'reference_key' => $item['refKey'],
                    'product_combination_id' => $item['product_combination_id'],
                    'status' => 'active'
                ]
            )
                ->exists();
            if (!$productExists) {
                array_push($productRefKey, $item['reference_key']);
            } else {
                $productOnly = UsersProduct::where('reference_key', $item['refKey'])->first();
                $product = !$item['hasVariant']
                    ? UsersProduct::where('reference_key', $item['refKey'])->first()
                    : VariantDetails::where('reference_key', $item['variantRefKey'])->first();
                ($product->quantity >= $item['qty'] ||
                    $product->is_selling &&
                    (!$item['hasVariant'] ||
                        $product->is_visible))
                    && $productOnly->status === 'active'
                    ? ""
                    : array_push($productRefKey, $item['reference_key']);
            }
        };

        return response(array_unique($productRefKey));
    }


    public function checkProductExists(request $request)
    {
        $removeProductIndex = [];
        foreach ($request->cartItem as $key => $product) {
            if (!isset($product['productCombinationId'])) {
                $product['productCombinationId'] = null;
            }
            $productExists = UsersProduct::where(
                [
                    'reference_key' => $product['refKey'],
                    'product_combination_id' => $product['productCombinationId'],
                    'status' => 'active'
                ]
            )
                ->exists();
            if (!$productExists) {
                array_push($removeProductIndex, $key);
            }
        }
        return response()->json($removeProductIndex);
    }
    public function getSelectedProduct(request $request)
    {
        $users_product = [];
        $uid = 0;
        foreach ($request->cartItem as $item) {
            $uid++;
            $object = (object)[];
            $object = UsersProduct::firstWhere('reference_key', $item['refKey']);
            $object->variant = $item['hasVariant']
                ? VariantDetails::firstWhere('reference_key', $item['variantCombinationId'])
                : null;
            $object->customization = count($item['customization']) > 0
                ? $this->getSelectedOption($item['customization'])
                : null;
            $object->cartUid = $uid;
            $object->cartQuantity = $item['qty'];
            $object->isDiscountApplied = false;
            $object->discount = [];
            $object->hasVariant = ($item['hasVariant']);
            $object->variantCombinationID = $item['hasVariant']
                ? $this->variantCombinationId(VariantDetails::firstWhere('reference_key', $item['variantCombinationId']))
                : [];
            if (in_array("subscriptionId", array_keys($item))) {
                $subscription = ProductSubscription::where('reference_key', $item['subscriptionId'])->first();
                $object->subscription = $subscription;
            }
            $selectedSaleChannels = [];
            foreach ($object->saleChannels()->get() as $saleChannel) {
                $selectedSaleChannels[] = $saleChannel->type;
            };
            if ($object->status === 'active' && count($object->saleChannels()->get()) === 0) $selectedSaleChannels = ['funnel', 'online-store', 'mini-store'];
            $saleChannel = $request->storeType !== 'store' ? 'mini-store' : 'online-store';
            if (in_array($saleChannel, $selectedSaleChannels)) array_push($users_product, $object);
        };
        return response()->json($users_product);
    }

    public function variantCombinationId($variant)
    {
        $combinationId = [];
        for ($i = 1; $i <= 5; $i++) {
            if ($variant['option_' . $i . '_id'] != null) {
                array_push($combinationId, $variant['option_' . $i . '_id']);
            }
        }
        return $combinationId;
    }

    public function getSelectedOption($customizations)
    {
        $productCustomization = [];
        foreach ($customizations as $customization) {
            $productOptionArray = [];
            $productOption = null;
            $productOption = productOption::where('id', $customization['id'])->select('display_name', 'is_total_Charge', 'total_charge_amount')->first();
            foreach ($customization['values'] as $value) {
                $productOptionValue = productOptionValue::where('id', $value['id'])->select('label', 'single_charge')->first();
                array_push($productOptionArray, $productOptionValue);
            }
            array_push($productCustomization, ['productOption' => $productOption, 'productOptionValue' => $productOptionArray]);
        }
        return $productCustomization;
    }

    //************************global function ********************************

    //************************out of stock page  ********************************

    public function getOutOfStockPage()
    {
        $pageName = "Out Of Stock";
        return view(
            'onlineStore.outOfStockPage',
            [
                'storePreferences' => $this->getStorePreferences(),
                'accountId' => $this->getCurrentAccountId(),
                'defaultCurrency' => $this->getDefaultCurrency(),
                'pageName' => $pageName
            ]
        );
    }


    public function getProcessContact($id)
    {
        $processContact = ProcessedContact::where('id', $id)->select('id', 'email', 'fname', 'lname', 'phone_number')->first();
        $order = Order::where('processed_contact_id', $id)->orderBy('created_at', 'DESC')->first();
        return response()->json(['processContact' => $processContact, 'order' => $order]);
    }

    //************************ customer information page ********************************
    public function getCustomerDetail()
    {
        $customer = Auth::guard('ecommerceUsers')->user();
        $processContact = $customer->processedContact;
        $address = $customer->addressBook;
        if ($address[0]->shipping_country === null) {
            if (count($customer->processedContact->lastestOrders) !== 0) {
                $address = [$customer->processedContact->lastestOrders[0]];
            }
        }
        return response()->json(['processedContact' => $processContact, 'address' => $address[0]]);
    }

    //************************ shipping method page ********************************

    public function getSelectedShipping($isPhysical)
    {
        if ($isPhysical) {
            session()->forget('shipping_method');
        }
        $selectedShipping = session('shipping_method', []);
        return response($selectedShipping);
    }

    public function getShipping()
    {
        $pageName = "Shipping Method";
        return view(
            'onlineStore.shippingMethod',
            [
                'pageName' => $pageName,
                'storePreferences' => $this->getStorePreferences(),
                'accountId' => $this->getCurrentAccountId(),
                'defaultCurrency' => $this->getDefaultCurrency(),
            ]
        );
    }

    public function checkShippingMethod(Request $request, $hasResponse = true)
    {
        $account_id = $this->getCurrentAccountId();
        $customerCountry = $request->customerShippingDetails['country'];
        $customerState = $request->customerShippingDetails['state'];
        $customerZipcode = $request->customerShippingDetails['zipCode'];
        $isMethodFound = false;
        $shippingMethod = [];
        $shippingZoneAll = ShippingZone::where('account_id', $account_id)->get();
        $compareArr = [];
        $rowMethods = [];
        foreach ($shippingZoneAll as $zone) {
            foreach ($zone->shippingRegion as $region) {
                if (!in_array($region->country, $compareArr)) {
                    array_push($compareArr, $region->country);
                }
                $checkByZipcode = $region->country == $customerCountry && $region->region_type == 'zipcodes';
                $settingContainZipcode = in_array($customerZipcode, json_decode($region->zipcodes));
                if ($checkByZipcode && $settingContainZipcode) {
                    $shippingMethod = $zone->shippingMethodDetails;
                    $isMethodFound = true;
                    break;
                }
                if ($region->country == 'Rest of world') {
                    $rowMethods = $zone->shippingMethodDetails;
                }
            }
        }
        if (!$isMethodFound) {
            foreach ($shippingZoneAll as $zone) {
                foreach ($zone->shippingRegion as $region) {
                    $checkByStates = $region->country == $customerCountry && $region->region_type == 'states';
                    $states = array_map(function ($value) {
                        return $value['stateName'];
                    }, $region->state);
                    $settingContainState = in_array($customerState, $states);
                    if ($checkByStates && $settingContainState) {
                        $shippingMethod = $zone->shippingMethodDetails;
                        $isMethodFound = true;
                        break;
                    }
                }
            }
        }
        if (!$isMethodFound & !in_array($customerCountry, $compareArr)) {
            $shippingMethod = $rowMethods;
        }
        return $shippingMethod;
    }

    public function storeShippingMethod(Request $request)
    {
        session()->put('shipping_method', $request->selectedShipping);
        return response()->json(['success' => true]);
    }


    public function storeCheckoutTax(Request $request)
    {
        session()->put('checkoutTaxInfo', [
            'taxSetting' => $request->taxSetting,
            'totalTax' => $request->totalTax,
            'shippingTax' => $request->shippingTax
        ]);

        return response()->json(['success' => true]);
    }

    //************************ shipping method page ********************************



    //************************ payment method page ********************************

    public function getPaymentMethod($domain)
    {
        $pageName = "Payment Methods";
        $customerDetail = $this->ecommerceUser();
        $domainName = $domain;
        $accountId = $this->getCurrentAccountId();
        $storePreferences = $this->getStorePreferences();
        $defaultCurrency = $this->getDefaultCurrency();
        $location = Location::where('account_id', $accountId)->first();
        $segmentService = new SegmentService();
        $cashbacks = Cashback::where('account_id', $accountId)->with('segments')->orderBy('cashback_amount', 'DESC')->orderBy('expire_date', 'DESC')->get();
        $cashbackDetails = array();

        foreach ($cashbacks as $cashback) {
            $segments = $cashback->segments;
            $contactIds = array();
            foreach ($segments as $segment) {
                // $condition = json_decode($segment->conditions, true);
                // $contactIds = array_unique(array_merge($contactIds, $segmentService->filterContacts($condition)));
                $contactIds = $segment->contacts(true);
            }
            $contactIds = array_values($contactIds);
            $cashback['contactIds'] = $contactIds;
        }
        return view(
            'onlineStore.paymentMethod',
            compact(
                'cashbacks',
                'domainName',
                'accountId',
                'defaultCurrency',
                'customerDetail',
                'pageName',
                'storePreferences',
                'location'
            )
        );
    }

    //************************ save Order ********************************
    public function saveCheckoutOrder(Request $request, LalamoveAPIService $lalamoveAPIService)
    {
        $request->validate([
            'url' => 'nullable|string',
            'accountId' => 'required'
        ]);

        $newProcessContact = $this->createProcessContact($request); // TODO: checked
        $newOrder = $this->updateAndCreateOrder($request, $newProcessContact);

        foreach ($request->productDetail as $key => $product) {
            $newOrderDetail = $this->createOrderDetail($request, $newOrder, $product, $key);
        }

        // just to get latest order details on the order
        $newOrder->refresh();

        if ($newOrder->shipping_method_name === 'EasyParcel') {
            $easyParcelShipment = EasyParcelShipment::create(array_merge(
                ['order_id' => $newOrder->id],
                $request->shippingMethod
            ));

            foreach ($newOrder->orderDetails as $orderDetail) {
                EasyParcelFulfillment::create([
                    'easy_parcel_shipment_id' => $easyParcelShipment->id,
                    'order_detail_id' => $orderDetail->id
                ]);
            }
        }

        if ($newOrder->shipping_method_name === 'Lalamove') {
            $lalamoveQuotation = $request->shippingMethod;
            $senderLocation = Location::ignoreAccountIdScope()->where('account_id', $request->accountId)->first();

            if (!$senderLocation) {
                throw new \RuntimeException('Sender location not set, cannot use Lalamove',
                    'order_id: ' . $newOrder->id
                );
            }

            $lalamoveQuotation = LalamoveQuotation::create([
                'order_id' => $newOrder->id,
                'scheduled_at' => null,
                'service_type' => $lalamoveQuotation['serviceType'],
                'stops' => $lalamoveAPIService->makeStops(
                    $senderLocation->displayAddr,
                    $lalamoveAPIService->generateDisplayAddress($lalamoveQuotation['address'])
                ),
                'deliveries' => $lalamoveAPIService->makeDeliveries(
                    $lalamoveQuotation['contact']['name'],
                    $lalamoveQuotation['contact']['phone']
                ),
                'requester_contacts' => $lalamoveAPIService->makeRequesterContact($senderLocation),
                'special_requests' => [],
                'total_fee_amount' => $lalamoveQuotation['totalFee'], // IMPORTANT: DON'T USE 'convertCharge' here
                'total_fee_currency' => $lalamoveQuotation['totalFeeCurrency']
            ]);

            foreach ($newOrder->orderDetails as $orderDetail) {
                LalamoveFulfillment::create([
                    'lalamove_quotation_id' => $lalamoveQuotation->id,
                    'order_detail_id' => $orderDetail->id
                ]);
            }
        }

        if ($newOrder->shipping_method_name === 'Delyva') {
            $delyvaQuotation = $request->shippingMethod;
            $delyvaInfo =  Delyva::where('account_id', $request->accountId)->first();
            $senderLocation = Location::ignoreAccountIdScope()->where('account_id', $request->accountId)->first();
            if (!$senderLocation) {
                throw new \RuntimeException('Sender location not set, cannot use Delyva',
                    'order_id: ' . $newOrder->id
                );
            }
            $delyvaQuotation = DelyvaQuotation::create([
                'order_id' => $newOrder->id,
                'scheduled_at' => null,
                'service_code' => $delyvaQuotation['service']['code'],
                'service_name' => $delyvaQuotation['service']['name'],
                'type' => $delyvaInfo->item_type,
                'total_fee_amount' => $delyvaQuotation['price']['amount'], // IMPORTANT: DON'T USE 'convertCharge' here
                'total_fee_currency' => $delyvaQuotation['price']['currency'],
                'service_company_name' => $delyvaQuotation['service']['serviceCompany']['name'],
                'service_detail' => json_encode($delyvaQuotation['service']),
            ]);
        }

        if ($request->hasCookie('refer_by_affiliate_member')) {
            $this->calculateAffiliateMemberCommissions($newOrder, $request->getHost());
            // Cookie::queue(Cookie::forget('refer_by_affiliate_member'));
        }
        $type = $request->saleChannel === 'mini-store' ? 'Mini Store' : 'Online Store';
        $orderCount = Order::where(['processed_contact_id'=>$newOrder->processed_contact_id, 'acquisition_channel'=>$type])->get()->count();
        if ($request->hasCookie('referral') && $orderCount == 1) {
            $people = ProcessedContact::find($newOrder->processed_contact_id);
            $this->checkReferralCampaignAction($request->getHost(), 'purchase', null, $people, $newOrder);
        }

        // $this->createOrderDiscount($newOrder,$newProcessContact, $request->appliedPromotion);
        $newOrderTransaction = $this->createOrderTransaction($request, $newProcessContact, $newOrder);

        return response()->json([
            'contactRandomId' => $newProcessContact->contactRandomId, // legacy prop
            'payment_references' => $newOrder->payment_references
        ]);
    }

    public function createProcessContact($request)
    {
        //        $url = $_SERVER['HTTP_HOST'];
        // $url = $request->url;
        // $type = $this->domainType($url);
        $type = $request->saleChannel;


        $customerInfo = $request->formDetail['customerInfo'];
        $data = isset($customerInfo['email']) ? ['email' => $customerInfo['email']] : ['phone_number' => $customerInfo['phoneNumber']];
        $newProcessContact = ProcessedContact::where('account_id', $request->accountId)
            ->firstOrNew($data);
        $newProcessContact->contactRandomId = $newProcessContact->contactRandomId != null
            ? $newProcessContact['contactRandomId']
            : $this->getRandomId('processed_contacts', 'contactRandomId');
        $newProcessContact->account_id = $request->accountId;
        $newProcessContact->fname = empty($customerInfo['fullName']) ? $newProcessContact->fname : $customerInfo['fullName'];
        $newProcessContact->email = empty($customerInfo['email']) ? $newProcessContact->email : $customerInfo['email'];
        $newProcessContact->phone_number = empty($customerInfo['phoneNumber']) ? $newProcessContact->phone_number : $customerInfo['phoneNumber'];
        $newProcessContact->ordersCount += 1;
        $newProcessContact->totalSpent += $this->getOrderCurrencyRange($request->paymentIntent['amount'] / 100, $request->currency);
        $newProcessContact->acquisition_channel = $type === 'mini-store' ? 'Mini Store' : 'Online Store';
        $newProcessContact->dateCreated = date('Y-m-d H:i:s');
        $segmentIds = json_encode($this->getSegmentIdsByContact($newProcessContact));
        $newProcessContact->save();
        ProcessedAddress::updateOrCreate(['processed_contact_id' => $newProcessContact->id]);
        $newProcessContact->segmentIds = $segmentIds;
        return $newProcessContact;
    }

    public function updateAndCreateOrder($request, $processContact)
    {
        //        $url = $_SERVER['HTTP_HOST'];
        // $url = $request->url;
        // $type = $this->domainType($url);
        $type = $request->saleChannel;

        //saveOrder
        $accountId = $request->accountId;
        $currencyObject = $request->currency;
        $shippingCharge = $request->selectedShipping != null  ? $request->selectedShipping['originalCharge'] : 0;
        //        $latestOrder= DB::table('orders')
        //        ->where('account_id',$accountId)
        //        ->orderBy('order_number','desc')
        //        ->first();
        $newOrder = new Order();
        $newOrder->account_id = $accountId;
        $newOrder->processed_contact_id = $processContact->id;

        $fulfilledStatus = "Unfulfilled";
        if (!$request->isPhysical) {
            $fulfilledStatus = "Fulfilled";
            $newOrder->additional_status = "Closed";
            $newOrder->fulfilled_at = date('Y-m-d H:i:s');
        }
        $newOrder->fulfillment_status = $fulfilledStatus;
        $newOrder->payment_status = "Paid";
        $newOrder->payment_process_status = 'Pending';
        $newOrder->paid_at = date('Y-m-d H:i:s');
        $newOrder->payment_references = $this->getRandomId('orders', 'payment_references');
        $newOrder->payment_method = 'card';
        $newOrder->currency = $currencyObject['currency'];
        $newOrder->exchange_rate = $currencyObject['exchangeRate'];

        $newOrder->cashback_amount = $request->cashback['amount'] * 100;
        $newOrder->used_credit_amount = $request->storeCreditTotal * 100;

        // ---------Tax Related After Tommy done will check back here--------
        $newOrder->subtotal = $request->subtotal;
        $newOrder->tax_name = $request->taxSetting['taxName'];
        $newOrder->taxes = $request->totalTax;
        $newOrder->tax_rate = $request->taxSetting['taxRate'];
        $newOrder->is_product_include_tax = $request->taxSetting['setting']['is_product_include_tax'];
        $newOrder->is_shipping_fee_taxable = $request->taxSetting['setting']['is_shipping_fee_taxable'];
        if ($request->deliveryHour !== null) {
            $newOrder->delivery_hour_type = 'custom';
            $newOrder->delivery_date = $request->deliveryHour['deliveryDate'];
            $newOrder->delivery_timeslot = $request->deliveryHour['selectDeliverySlot'];
            $newOrder->delivery_type = $request->deliveryHour['type'];
            $newOrder->pickup_location = ($newOrder->delivery_type == 'pickup') ? json_encode(Location::ignoreAccountIdScope()->where('account_id', $accountId)->first()) : json_encode([]);
        }
        if (!empty($request->shippingMethod)) {
            $newOrder->shipping_method_name = $request->shippingMethod['shipping_method'];
            $newOrder->shipping_method = $request->shippingMethod['shipping_name'];
        }
        $newOrder->shipping = $shippingCharge;
        $newOrder->shipping_tax = $request->shippingTax;
        $newOrder->total = $this->getOrderCurrencyRange($request->paymentIntent['amount'] / 100, $request->currency);
        $newOrder->paided_by_customer = $newOrder->total;
        $newOrder->notes = $request->notes;
        $newOrder->reference_key = $this->getRandomId('orders', 'order_number');
        $newOrder->acquisition_channel =  $type === 'mini-store' ? 'Mini Store' : 'Online Store';
        $shipping = $request->formDetail['shipping'];
        $billing = $request->formDetail['billing'];
        $newOrder->shipping_name = $shipping['fullName'];
        $newOrder->shipping_phoneNumber = $shipping['phoneNumber'];
        if (isset($billing['companyName'])) {
            $newOrder->shipping_company_name = $shipping['companyName'];
        }
        $newOrder->shipping_address =  $shipping['address'];
        $newOrder->shipping_city = $shipping['city'];
        $newOrder->shipping_state =  $shipping['state'];
        $newOrder->shipping_zipcode = $shipping['zipCode'];
        $newOrder->shipping_country = $shipping['country'];
        $newOrder->billing_name = $billing['fullName'];
        if (isset($billing['companyName'])) {
            $newOrder->billing_company_name = $billing['companyName'];
        }
        $newOrder->billing_address = $billing['address'];
        $newOrder->billing_city = $billing['city'];
        $newOrder->billing_state = $billing['state'];
        $newOrder->billing_zipcode = $billing['zipCode'];
        $newOrder->billing_country = $billing['country'];
        $newOrder->segmentIds = $processContact->segmentIds;
        $newOrder->save();
        return $newOrder;
    }

    public function createOrderDetail($request, $order, $product, $index)
    {
        $cartItem = $request->cartItem[$index];
        $currency = Currency::where('account_Id', $request->accountId)->where('currency', $request->currency['currency'])->first();

        $newOrderDetail = new OrderDetail();
        $newOrderDetail->order_id = $order->id;
        $newOrderDetail->users_product_id = $product['id'];
        $newOrderDetail->SKU = $product['hasVariant']
            ? $this->getProductVariant($product)['sku'] ?? null
            : $product['SKU'];
        $product['physicalProducts'] != 'on' ? $newOrderDetail->order_number = "#" . $order->order_number . "-F1" : "";
        $product['physicalProducts'] !== 'on' ? $fulfilledStatus = "Fulfilled" : $fulfilledStatus = "Unfulfilled";
        $newOrderDetail->is_virtual = ($product['physicalProducts'] !== 'on');
        $newOrderDetail->discount = ($product['isDiscountApplied'] ?? null) ? $product['discount']['value'] * 100 : 0;
        $newOrderDetail->discount_details = json_encode($product['discount'] ?? []);
        $newOrderDetail->is_discount_applied = $product['isDiscountApplied'] ?? false;
        $newOrderDetail->fulfillment_status = $fulfilledStatus;
        $newOrderDetail->product_name = $product['productTitle'];
        $newOrderDetail->variant_combination_id = $cartItem['variantRefKey'] ?? null;
        $newOrderDetail->variant = json_encode($cartItem['variations'] ?? []);
        $newOrderDetail->customization = json_encode($cartItem['customizations'] ?? []);
        $newOrderDetail->image_url = $cartItem['hasVariant']
            ? $cartItem['variantImage']
            : $cartItem['productImagePath'] ?? '';

        if ($product['physicalProducts'] === 'on') {
            $newOrderDetail->variant_name = ($cartItem['variation'] ?? null) ? $this->variantName($cartItem['variation']) : '';
        }
        $newOrderDetail->weight = ($product['hasVariant'])
            ? (float)$this->getProductVariant($product)['weight'] * $product['qty']
            : (float)$product['weight'] * $product['qty'];
        $newOrderDetail->fulfilled_at =  $product['physicalProducts'] !== 'on' ? date('Y-m-d H:i:s') : null;
        $newOrderDetail->payment_status = "Paid";
        $newOrderDetail->paid_at = date('Y-m-d H:i:s');
        $newOrderDetail->unit_price = $request->unitPrice[$index] / $cartItem['qty'];
        //-----This one also after tommy integrate only check-------
        // if($param->taxSetting['setting']['is_product_include_tax']){
        //     $newOrderDetail->unit_price = ($param->paymentIntent['amount']/100)/$param->productQuantity;
        // }else{
        //     $newOrderDetail->unit_price = ($param->paymentIntent['amount']/100/$param->productQuantity)-$param->totalTax;
        // }
        $newOrderDetail->quantity = $cartItem['qty'];
        $newOrderDetail->total = $newOrderDetail->unit_price * $cartItem['qty'];
        $newOrderDetail->is_taxable = $product['isTaxable'];
        $newOrderDetail->tax = $request->productTaxArray[$index];
        $newOrderDetail->save();
    }

    /**
     * Get variant from checkout cart item which has variant
     *
     * @param $product Checkout cart item passed from frontend
     * @return mixed
     */
    private function getProductVariant($product)
    {
        return collect($product['variant_details'])
            ->firstWhere(
                'reference_key',
                $product['variantRefKey']
            );
    }

    public function createOrderTransaction($param, $processContact, $order)
    {
        $newTransaction = new OrderTransactions();
        $newTransaction->order_id = $order->id;
        $newTransaction->transaction_key = $this->getRandomId('order_transactions', 'transaction_key');
        $newTransaction->total = $this->getOrderCurrencyRange($param->paymentIntent['amount'] / 100, $param->currency);
        $newTransaction->paid_at = now();
        $newTransaction->payment_status = "Paid";
        $newTransaction->save();
    }

    public function calculateInventory($order, $isSubscription)
    {
        $isVariant = $order->variant !== '[]' ? true : false;
        $inventory = $isVariant
            ? VariantDetails::where('reference_key', $order['variant_combination_id'])
            : UsersProduct::where('id', $order->users_product_id);
        $remainingStock = $inventory->first()->quantity;
        if ($isSubscription) {
            $inventory->update(['quantity' => $remainingStock - $order->quantity]);
        } else {
            if ($remainingStock !== 0) $inventory->update(['quantity' => $remainingStock - $order->quantity]);
        }
    }

    public function createOrderDiscount($order, $processContact, $appliedPromotion)
    {
        if (!empty($appliedPromotion)) {
            foreach ($appliedPromotion as $discount) {
                if ($discount['valid_status']) {
                    $orderDiscount = new OrderDiscount();
                    $orderDiscount->order_id = $order->id;
                    $orderDiscount->discount_code = $discount['promotion']['discount_code'];
                    $orderDiscount->discount_value = ($discount['discountValue']['value']) * 100;
                    $orderDiscount->display_name = $discount['promotion']['display_name'];
                    $orderDiscount->promotion_name = $discount['promotion']['promotion_name'];
                    $orderDiscount->promotion_method = $discount['promotion']['promotion_method'];
                    $orderDiscount->promotion_category = $discount['promotion']['promotion_category'];
                    if ($orderDiscount->promotion_category == 'Order') {
                        $orderDiscount->discount_type = $discount['promotion']['promotion_type']['order_discount_type'];
                    } else if ($orderDiscount->promotion_category == 'Product') {
                        $orderDiscount->discount_type = $discount['promotion']['promotion_type']['product_discount_type'];
                    }

                    $orderDiscount->save();
                    // $order->subtotal += ($discount['promotion']['promotion_category'] =='Order') ? ($discount['discountValue']->value) : 0;
                    // $order->save();
                    $extraCondition = Promotion::find($discount['promotion']['id'])->extraCondition;
                    $extraCondition->store_usage += 1;
                    $extraCondition->save();
                    $customerRedemption = PromotionRedemptionLog::firstOrNew([
                        'promotion_id' =>  $discount['promotion']['id'],
                        'customer_email' => $processContact->email
                    ]);
                    $customerRedemption->discount_code = $discount['promotion']['discount_code'];
                    $customerRedemption->applied_usage += 1;
                    $customerRedemption->save();
                }
                $totalUsage = PromotionRedemptionLog::where('promotion_id', $discount['promotion']['id'])->sum('applied_usage');
                $promotion = Promotion::find($discount['promotion']['id']);
                $promotion->promotion_usage = $totalUsage;
                $promotion->save();
            }
        }
    }

    public function variantName($variant)
    {
        if (count($variant) === 1) {
            return $variant[0]['value'];
        }
        if (count($variant) === 2) {
            return $variant[0]['value'] . '/' . $variant[1]['value'];
        }
        if (count($variant) === 3) {
            return $variant[0]['value'] . '/' . $variant[1]['value'] . '/' . $variant[2]['value'];
        }
        if (count($variant) === 4) {
            return $variant[0]['value'] . '/' . $variant[1]['value'] . '/' . $variant[2]['value'] . '/' . $variant[3]['value'];
        }
        if (count($variant) === 5) {
            return $variant[0]['value'] . '/' . $variant[1]['value'] . '/' . $variant[2]['value'] . '/' . $variant[3]['value'] . '/' . $variant[4]['value'];
        }
    }

    //************************ save Order ********************************

    //************************ checkout ********************************

    public function getCheckoutPayment($accountId, $currencyDetails, $isSubscription)
    {
        $payment_gateways = PaymentAPI::Where('account_id', $accountId)->get();
        $payment_methods = [];
        if ($isSubscription) {
            return response()->json($this->getSubscription($accountId));
        } else {
            foreach ($payment_gateways as $payment_gateway) {
                $object = (object)
                [
                    'id' => $payment_gateway->id,
                    'name' => $payment_gateway->payment_methods,
                    'displayName' => $payment_gateway->display_name,
                    'description' => $payment_gateway->description,
                    'api' => $payment_gateway->publishable_key,
                    'checked' => false,
                    'icon' => 'down',
                    'enabled_at' => $payment_gateway->enabled_at,
                    'enable_fpx' => $payment_gateway->enable_fpx,
                ];
                array_push($payment_methods, $object);

                // add stripe FPX if there's stripe payment method and store currency is limited to MRY
                if ($payment_gateway->payment_methods === 'stripe' && $payment_gateway->enable_fpx && ($currencyDetails->currency === 'MYR' || $currencyDetails->currency === 'RM')) {
                    $object = (object)
                    [
                        'id' => 'stripe-fpx-id',
                        'name' => 'stripe FPX',
                        //                        'displayName' => $payment_gateway->display_name2,
                        'displayName' => 'Stripe FPX',
                        'api' => $payment_gateway->publishable_key,
                        'checked' => false,
                        'icon' =>  'down',
                        'enabled_at' => $payment_gateway->enabled_at,
                    ];
                    array_push($payment_methods, $object);
                };
            }
        }
        return $payment_methods;
    }
    public function getSubscription($accountId)
    {
        $payment_methods = [];
        $payment_gateway = PaymentAPI::Where('account_id', $accountId)->where('payment_methods', 'stripe')->first();
        $object = (object)
        [
            'name' => 'stripe subscription',
            'displayName' => 'stripe subscription',
            'api' => $payment_gateway->publishable_key,
            'checked' => true,
            'icon' =>  'up',
            'enabled_at' => $payment_gateway->enabled_at,
        ];
        array_push($payment_methods, $object);
        return $payment_methods;
    }

    //************************ stripe card payment ********************************

    public function changePaymentStatus(Request $request)
    {
        $request->validate([
            'paymentMethod' => 'required',
            'status' => 'required',
            'url' => 'required',
            'paymentRef' => 'required',
        ]);

        $redirectUrl = '/checkout/payment';
        $order = Order::where('payment_references', $request->paymentRef)->first();
        if (isset($request->subscription)) $this->storeSubscriptionDetail($request->subscription, $order);

        $payment = PaymentAPI::firstWhere(
            [
                'account_id' => $this->getCurrentAccountId(),
                'payment_methods' => $request->paymentMethod
            ]
        );

        $order->payment_method =
            $request->paymentMethod === 'Store Credit' ||  $request->paymentMethod === 'Stripe Subscription'
            ?  $request->paymentMethod
            : $payment->display_name;
        $order->payment_process_status = $request->status;
        if ($request->paymentMethod === 'manual payment') {
            $order->payment_status = 'Unpaid';
            $order->paided_by_customer = 0;
        }
        $order->save();

        $params =  null;
        if (isset($request->cashback) && isset($request->storeCreditTotal) && isset($request->usedCredit)) {
            $params['cashback'] = $request->cashback;
            $params['storeCreditTotal'] = $request->storeCreditTotal;
            $params['usedCredit'] = $request->usedCredit;
        }

        if ($request->status === 'Success') {
            $redirectUrl = $this->paymentSuccess($order, $params, $request->appliedPromotion);
        }

        return response()->json(['url' => $redirectUrl]);
    }

    public function lastestOrderNumber($accountId)
    {
        $latestOrderNumber = Order::where('account_id', $accountId)
            ->where(
                function ($query) {
                    $query
                        ->where('payment_process_status', 'Success')
                        ->orWhereNull('payment_process_status');
                }
            )
            ->max('order_number');
        return $latestOrderNumber === null ? 1000 : $latestOrderNumber + 1;
    }

    private function storeSubscriptionDetail($subscription, $order)
    {
        $productSubscription = ProductSubscription::where('price_id', $subscription['plan']['id'])->first();
        $orderSubscription = OrderSubscription::updateOrcreate(
            ['subscription_id' => $subscription['id']],
            [
                'processed_contact_id' => $order->processed_contact_id,
                'account_id' => $this->getCurrentAccountId(),
                'product_subscription_id' => $productSubscription->id,
                'subscription_name' => $productSubscription->display_name,
                'start_date' => date('Y/m/d H:i:s', $subscription['start_date']),
                'last_payment' => date('Y/m/d H:i:s', $subscription['current_period_start']),
                'next_payment' => date('Y/m/d H:i:s', $subscription['current_period_end']),
                'status' => $subscription['status']
            ]
        );
        $order->invoice_id = $subscription['latest_invoice']['id'];
        $order->invoice_url = $subscription['latest_invoice']['invoice_pdf'];
        $order->order_subscription_id = $orderSubscription->id;
    }

    //************************ stripe card payment ********************************

    //************************ stripe Fpx payment ********************************
    public function getStatus(Request $request)
    {
        $request->validate([
            // stripe FPX params
            'payment_intent' => 'required',
            'payment_intent_client_secret' => 'required',
            'redirect_status' => 'required',

            // custom query params
            'url' => 'required',
            'proto' => 'required',
            'payment_ref' => 'required'
        ]);

        $status = $request->redirect_status;
        $order = Order::where('payment_references', $request->payment_ref)->first();
        $clientURL = $request->url;
        $clientProto = $request->proto;
        $type = AccountDomain::getDomainRecord()->type;

        if ($status === 'failed') {
            $status = 'Failed';
            $redirectUrl = $type === 'mini-store' ? '/checkout/mini-store?status=failed&payment=fpx' : '/checkout/payment?status=failed&payment=fpx';
            //            setcookie("status", 'fail', 0,"/");
        } else {
            $status = 'Success';

            $params =  null;
            $cashback = json_decode($request->cashback,true);
            if (isset($cashback) && isset($request->storeCreditTotal) && isset($request->usedCredit)) {
                $params['cashback'] = $cashback;
                $params['storeCreditTotal'] = $request->storeCreditTotal;
                $params['usedCredit'] = $request->usedCredit;
            }
            $redirectUrl = $this->paymentSuccess($order, $params, json_decode($request->appliedPromotion,true));
        }
        $order->payment_process_status = $status;
        $order->payment_method = 'Stripe FPX';
        $order->save();

        // redirect to client store page instead of server
        return redirect($clientProto .  '//' . $clientURL . $redirectUrl);
    }

    //************************ stripe Fpx payment ********************************

    //************************ subscription payment ********************************

    private function stripe()
    {
        // Set your secret key. Remember to switch to your live secret key in production.
        // See your keys here: https://dashboard.stripe.com/account/apikeys
        $paymentApi = PaymentAPI::where('account_id', $this->getCurrentAccountId())
            ->where('payment_methods', 'Stripe')
            ->first();
        return \Stripe\Stripe::setApiKey($paymentApi->secret_key);
    }

    private function stripeClient()
    {
        $paymentApi = PaymentAPI::where('account_id', $this->getCurrentAccountId())
            ->where('payment_methods', 'Stripe')
            ->first();
        return new \Stripe\StripeClient($paymentApi->secret_key);
    }

    private function createStripeWebhook()
    {
        $domain = AccountDomain::where('account_id', $this->getCurrentAccountId())->where('type', 'store')->first()->domain;
        $webhook = $this->stripeClient()->webhookEndpoints->create([
            'url' => 'https://' . $domain . '/product/subscrption/webhook',
            'enabled_events' => [
                'invoice.paid',
                'invoice.payment_failed',
                'customer.subscription.deleted',
                'customer.subscription.updated',
            ],
        ]);
        User::where('currentAccountId', $this->getCurrentAccountId())->update([
            'webhook_id' => $webhook->secret,
        ]);
    }

    public function createStripeCustomer(request $request)
    {
        $webhookId = User::where('currentAccountId', $this->getCurrentAccountId())->first()->webhook_id;
        if ($webhookId === null) $this->createStripeWebhook();
        $processContact = ProcessedContact::where('account_id', $this->getCurrentAccountId())
            ->where('email', $request->email)
            ->first();
        $stripeCustomer = $this->stripeClient()->customers->create(
            [
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phoneNumber,
            ]
        );
        $processContact->customer_id = $stripeCustomer->id;
        $processContact->save();
        return response()->json($processContact->customer_id);
    }

    public function createProductSubscription(request $request)
    {
        $this->stripe();
        $productSubscription = ProductSubscription::where('reference_key', $request->subscriptionId)->first();
        $charge = round($request->charge, 2);
        $currency = $request->currency;
        $priceId = app(ProductSubscriptionController::class)->createProductPrice($productSubscription, $charge, $currency);
        //create payment methods
        try {
            $payment_method = \Stripe\PaymentMethod::retrieve($request->paymentMethodId);
            $payment_method->attach(['customer' => $request->customerId]);
        } catch (Exception $e) {
            return response()->json($e->jsonBody);
        }
        // Set the default payment method on the customer
        \Stripe\Customer::update($request->customerId, ['invoice_settings' => ['default_payment_method' => $request->paymentMethodId]]);
        //create subscription
        $subscription = \Stripe\Subscription::create([
            'customer' =>  $request->customerId,
            'items' => [['price' => $priceId]],
            'expand' => ['latest_invoice.payment_intent'],
        ]);
        return response()->json($subscription);
    }

    public function deleteProductSubscription($domain, $id)
    {
        $this->stripe();
        $orderSubscription = OrderSubscription::find($id);
        //terminate subscription on plan end
        $is_terminate_immediately = $orderSubscription->account->terminate_cycle === 'immediately' ? true : false;
        if ($is_terminate_immediately) {
            $cancelSubscription = \Stripe\Subscription::retrieve($orderSubscription->subscription_id);
            $cancelSubscription->cancel();
            $cancel_at = $cancelSubscription['canceled_at'];
        } else {
            $cancelSubscription = \Stripe\Subscription::update($orderSubscription->subscription_id, ['cancel_at_period_end' => true,]);
            $cancel_at = $cancelSubscription['cancel_at'];
        }
        $orderSubscription->update(
            [
                'status' => $cancelSubscription['status'],
                'end_payment' => date('Y/m/d H:i:s', $cancel_at),
            ]
        );
        $subscription = app('App\Http\Controllers\ProductSubscriptionController')->getArrangedProductSubscription();
        return response($subscription);
    }

    public function retryProductInvoice(request $request)
    {
        $this->stripe();
        try {
            $payment_method = \Stripe\PaymentMethod::retrieve($request->paymentMethodId);
            $payment_method->attach(['customer' => $request->customerId]);
        } catch (Exception $e) {
            return response()->json($e->jsonBody);
        }
        \Stripe\Customer::update($request->customerId, ['invoice_settings' => ['default_payment_method' => $request->paymentMethodId]]);
        $invoice = \Stripe\invoice::retrieve($request->invoiceId, ['expand' => ['payment_intent']]);
        $paymentIntent = \Stripe\PaymentIntent::retrieve($invoice->payment_intent);
        return response()->json($paymentIntent);
    }

    public function productSubscriptionWebhook()
    {
        return;
        $this->stripe();
        $endpoint_secret = User::where('currentAccountId', $this->getCurrentAccountId())->first()->webhook_id;
        $payload = @file_get_contents('php://input');
        $sig_header = $_SERVER['HTTP_STRIPE_SIGNATURE'];
        $event = null;
        try {
            $event = \Stripe\Webhook::constructEvent(
                $payload,
                $sig_header,
                $endpoint_secret
            );
        } catch (\UnexpectedValueException $e) {
            // Invalid payload
            http_response_code(400);
            exit();
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            // Invalid signature
            http_response_code(500);
            exit();
        }

        // Handle the event
        switch ($event->type) {
            case 'invoice.paid':
                $this->updateInvoice($event->data->object);
                break;
            case 'invoice.payment_failed':
                $this->updateInvoice($event->data->object);
                break;
            case 'customer.subscription.updated':
                $this->updateSubscription($event->data->object, $event->data->object->cancel_at);
                break;
            case 'customer.subscription.deleted':
                $this->updateSubscription($event->data->object, $event->data->object->canceled_at);
                break;
                // ... handle other event types
            default:
                echo 'Received unknown event type ' . $event->type;
        }
    }

    private function updateInvoice($object)
    {
        $orderSubscription = OrderSubscription::where('subscription_id', $object->subscription)->first();
        $productSubscription = $orderSubscription->productSubscription;
        $lastestOrder = $orderSubscription->orders[0];
        if ($object->id !== $lastestOrder->invoice_id) {
            //duplicate lastest order
            $newOrder = $lastestOrder->replicate()->fill(
                [
                    'order_number' => $this->lastestOrderNumber($lastestOrder->account_id),
                    'payment_status' => $object->paid ? 'Paid' : 'Unpaid',
                    'paid_at' => date('Y/m/d H:i:s', $object->created),
                    'payment_references' => $this->getRandomId('orders', 'payment_references'),
                    'reference_key' => $this->getRandomId('orders', 'reference_key'),
                    'invoice_id' => $object->id,
                    'invoice_url' => $object->invoice_pdf,
                ]
            );
            $newOrder->save();
            //duplicate lastest order details
            $newOrderDetail = $lastestOrder->orderDetails[0]->replicate()->fill(
                [
                    'order_id' => $newOrder->id,
                    'payment_status' => $object->paid ? 'Paid' : 'Unpaid',
                    'paid_at' => date('Y/m/d H:i:s', $object->created),
                ]
            );
            $newOrderDetail->save();
            // check expiration cycle
            if (count($orderSubscription->orders) >= $productSubscription->expiration_cycle) {
                $this->stripe();
                $is_terminate_immediately = $orderSubscription->account->terminate_cycle === 'immediately' ? true : false;
                if ($is_terminate_immediately) {
                    $cancelSubscription = \Stripe\Subscription::retrieve($orderSubscription->subscription_id);
                    $cancelSubscription->cancel();
                } else {
                    $cancelSubscription = \Stripe\Subscription::update($orderSubscription->subscription_id, ['cancel_at_period_end' => true,]);
                }
            }
            //reduce inventroy
            $this->calculateInventory($newOrderDetail, true);
        }
    }

    private function updateSubscription($object, $deleted_at)
    {
        $orderSubscription = OrderSubscription::where('subscription_id', $object->id)->first();
        $orderSubscription->update(
            [
                'status' => $object->status,
                'last_payment' => date('Y/m/d H:i:s', $object->current_period_start),
                'next_payment' => date('Y/m/d H:i:s', $object->current_period_end),
                'end_payment' => $deleted_at !== null ? date('Y/m/d H:i:s', $deleted_at) : null,
            ]
        );
    }
    //************************ subscription payment ********************************

    //************************  payment success ********************************

    public function paymentSuccess($order, $params = null, $appliedPromotion = null)
    {
        $order->order_number = $this->lastestOrderNumber($order->account_id);
        $order->save();

        // update order number of virtual product
        if(isset($order->orderDetails)){
            $order->orderDetails->each(function ($row) use ($order) {
                if($row['is_virtual']){
                    $row->update(['order_number'=> "#" . $order->order_number . "-F1"]);
                }
            });
        }

        $processContact = processedContact::find($order->processed_contact_id);
        $orderDetail = orderDetail::where('order_id', $order->id)->get();

        /**
         * Update order id in ecommerce visitor record
         * once the customer placed an order
         */
        // $visitor = EcommerceVisitor::where('reference_key', $_COOKIE['visitor_id'])->first();
        // $visitor->order_id = $order->id;
        // $visitor->save();

        /*
        *   Must update store credits first before saving cashback amount
        *   as customer not able to use the cashback earned directly form same order
        *   Process:
        *       1 - Update Store Credit
        *       2 - Save Cashback
        *       3 - Create Discount
        *       4 - Modify subtotal
        */
        // if (Auth::guard('ecommerceUsers')->check()) {
        //     $ecommerceAccount = Auth::guard('ecommerceUsers')->user();
        //     $sellerInfo = $ecommerceAccount->sellerInfo;
        //     if (!$ecommerceAccount->is_verify) {
        //         EcommerceMailsController::sendSignupEmail(
        //             $ecommerceAccount,
        //             $sellerInfo,
        //         );
        //     }
        // }

        if (isset($params)) {
            $this->updateStoreCredits($processContact, $order->currency, $params['storeCreditTotal']);
            $this->saveCashbackAmount($processContact, $order, $params['cashback']);
            // $order->subtotal += $params['usedCredit'];
            // $order->save();
        }
        if (isset($appliedPromotion)) {
            $this->createOrderDiscount($order, $processContact, $appliedPromotion);
        }

        foreach ($orderDetail as $product) {
            $this->calculateInventory($product, false);
        };
        //setcookie('pId', $processContact->contactRandomId, time() + (86400), "/");
        //setcookie('orId', session('payment_references'), time() + (86400), "/");
        // event(new ProductPurchased($order, $processContact));
        event(new OrderPlaced($order));

        $notifiableSetting = Account::find($order->account_id)->notifiableSetting;
        $sellerEmails = [$order->sellerEmail()];
        if(!empty($notifiableSetting->notification_email)){
            $sellerEmails = array_map('trim',explode(',', $notifiableSetting->notification_email));
        }
        $buyerEmail = $order->processedContact->email ?? null;
        if (
            $notifiableSetting->is_fulfill_order_notifiable &&
            !is_null($buyerEmail) &&
            count($sellerEmails)
        ) {
            Mail::to($buyerEmail)->send(new OrderPaymentBuyerEmail($order));
            foreach ($sellerEmails as $sellerEmail) {
                Mail::to($sellerEmail)->send(new OrderPaymentSellerEmail($order));
            }
        }

        $redirectUrl = '/checkout/success/' . $order->payment_references;
        session()->forget('orderReferenceKey');
        session()->forget('shipping_method');
        session()->forget('checkoutTaxInfo');
        //        session()->forget('payment_intent_FPX');
        session()->forget('storeCreditTotal');
        session()->forget('appliedPromotion');
        session()->forget('usedCredit');
        return $redirectUrl;
    }

    //************************ payment success ********************************

    //************************  store credit ********************************
    public function updateStoreCredits($contact, $currency, $storeCreditUsed)
    {
        $currencyArray = Currency::where('account_id', $contact->account_id)->pluck('exchangeRate', 'currency')->toArray();

        if ($storeCreditUsed == 0) return;
        StoreCredit::create([
            'account_id' => $contact->account_id,
            'processed_contact_id' => $contact->id,
            'credit_amount' => $storeCreditUsed * 100,
            'currency' => $currency,
            'credit_type' => 'Deduct',
            'reason' => 'Credit Used',
            'source' => 'Credit Used',
        ]);
        $selectedCredits = $contact->storeCredits()
            ->where('credit_type', 'Add')
            ->where('expire_date', '>', Carbon::now())
            ->where('balance', '>', 0)
            ->orderBy('expire_date')
            ->get();
        $leftOverCredit = $storeCreditUsed * 100;
        foreach ($selectedCredits as $record) {
            $balanceCredit = $record->balance - ($leftOverCredit / ((float)$currencyArray[$currency]));
            if ($balanceCredit >= 0) {
                $record->balance = $balanceCredit;
                $record->save();
                break;
            }
            $record->balance = 0;
            $record->save();
            $leftOverCredit = abs($balanceCredit);
        }

        $contact->credit_balance -= ($storeCreditUsed * 100 / ((float)$currencyArray[$currency]));
        $contact->save();
    }
    //************************  store credit ********************************

    //************************  store cashback ********************************
    public function saveCashbackAmount($contact, $order, $cashback)
    {
        $cashbackAmount = 0;
        if (isset($cashback)) {
            $cashbackAmount = $cashback['amount'];
            if (!isset($cashback['detail'])) return;
        }

        if ($order->payment_status === 'Paid') {
            $storecredit = StoreCredit::create([
                'account_id' => $contact->account_id,
                'processed_contact_id' => $contact->id,
                'credit_amount' => $cashbackAmount * 100,
                'balance' => $cashbackAmount * 100,
                'currency' => $order->currency,
                'credit_type' => 'Add',
                'source' => 'Cashback',
                'reason' => 'Cashback',
                'expire_date' => Carbon::now()->addMonth($cashback['detail']['expire_date'])->toDateString()
            ]);
        } else {
            $order->update(['cashback_detail' => json_encode($cashback)]);
        };

        session()->forget('cashback');
        return;
    }
    //************************  store cashback ********************************


    //************************ checkout ********************************

    //************************ payment method page ********************************



    //************************ order sucess page ********************************

    public function getSuccess($domain, $refKey)
    {
        $pageName = "Checkout Success";
        $registeredCustomerDetail = $this->ecommerceUser();
        $accountId = $this->getCurrentAccountId();
        $paymentInstruction = '';
        if (PaymentAPI::firstWhere(['account_id' => $accountId, 'payment_methods' => 'cashOnDelivery']) !== null) {
            $paymentInstruction = PaymentAPI::firstWhere(['account_id' => $accountId, 'payment_methods' => 'cashOnDelivery'])->description;
        };
        $order = Order::where('payment_references', $refKey)->with('orderDiscount')->first();
        $order->payment_instruction = $paymentInstruction;
        $orderDetail = orderDetail::where('order_id', $order->id)->get();
        $processContact = processedContact::where('id', $order->processed_contact_id)->select('email', 'phone_number')->first();
        $contactRandomId = processedContact::find($order->processed_contact_id)->contactRandomId;
        $sellerEmail = User::where('currentAccountId', $order->account_id)->first()->email;
        $isLogin = $this->ecommerceUser() === null ? false : true;
        $customerDetail = (object)
        [
            'customerInfo' => $processContact,
            'shipping' => (object)
            [
                'fullName' => $order->shipping_name,
                'companyName' => $order->shipping_company_name,
                'address' => $order->shipping_address,
                'city' => $order->shipping_city,
                'state' => $order->shipping_state,
                'shipping_zipcode' => $order->shipping_zipcode,
                'shipping_country' => $order->shipping_country,
                'phoneNumber' => $order->shipping_phoneNumber,
            ],
            'billing' => (object)
            [
                'fullName' => $order->billing_name,
                'companyName' => $order->billing_company_name,
                'address' => $order->billing_address,
                'city' => $order->billing_city,
                'state' => $order->billing_state,
                'shipping_zipcode' => $order->billing_zipcode,
                'shipping_country' => $order->billing_country,
                'phoneNumber' => $order->billing_phoneNumber,
            ],
        ];
        $shopName = Account::find($order->account_id)->shopName;
        $storePreferences = $this->getStorePreferences();

        $segmentService = new SegmentService();
        $cashbacks = Cashback::where('account_id', $accountId)->with('segments')->orderBy('cashback_amount', 'DESC')->get();
        $cashbackDetails = array();

        foreach ($cashbacks as $cashback) {
            $segments = $cashback->segments;
            $contactIds = array();
            foreach ($segments as $segment) {
                // $condition = json_decode($segment->conditions, true);
                // $contactIds = array_unique(array_merge($contactIds, $segmentService->filterContacts($condition)));
                $contactIds = $segment->contacts(true);
            }
            $contactIds = array_values($contactIds);
            $cashback['contactIds'] = $contactIds;
        }

        return view(
            'onlineStore.checkoutSuccessful',
            compact(
                'isLogin',
                'customerDetail',
                'shopName',
                'order',
                'sellerEmail',
                'contactRandomId',
                'orderDetail',
                'registeredCustomerDetail',
                'cashbacks',
                'pageName',
                'storePreferences'
            )
        );
    }

    public function verifyOrderDetail(request $request)
    {
        $order = Order::where(['order_number' => $request->orderNumber, 'account_id' => $this->getCurrentAccountId()]);
        $isOrder = $order->exists();
        $isOwnerEmail = processedContact::where('email', $request->email)->exists();
        $status = 'Failed';
        if ($isOrder && $isOwnerEmail) {
            $status = 'success';
            $contactRandomId = processedContact::find($order->first()->processed_contact_id)->contactRandomId;
            setcookie("pId", $contactRandomId, time() + (60 * 60 * 24 * 14), '/');
        }
        return response()->json(['status' => $status]);
    }

    //************************ order sucess page ********************************

    public function getDeliveryHour($domainName)
    {
        $accountId = app()->environment() === 'local' ? $this->getCurrentAccountId() : $this->getCurrentAccountId($domainName);
        $preferences = EcommercePreferences::where('account_id', $accountId)->first();
        $deliveryHour = [];
        $disabledDate = [];
        $preperatonTime = 0;
        $isPreperationTime = false;
        $preOrderFrom = null;
        $isLimitOrder = false;
        if ($preferences->delivery_hour_type == 'custom') {
            $deliveryHour = json_decode($preferences->delivery_hour);
            $disabledDate = json_decode($preferences->delivery_disabled_date);
            $preOrderFrom = $preferences->delivery_pre_order_from;
            $preperatonTime = $preferences->delivery_preperation_value;
            $isPreperationTime = $preferences->delivery_is_preperation_time;
            $isLimitOrder = $preferences->delivery_is_limit_order;
        }

        return response()->json([
            'deliveryHour' => $deliveryHour,
            'disabledDate' => $disabledDate,
            'preOrderFrom' => $preOrderFrom,
            'preperationTime' => $preperatonTime,
            'isPreperationTime' => $isPreperationTime,
            'isLimitOrder' => $isLimitOrder,
        ]);
        // dd($preferences);
    }

    public function getStorePickup(Request $request)
    {
        $preferences = EcommercePreferences::where('account_id', $request->accountId)->first();
        $deliveryHour = [];
        $disabledDate = [];
        $preperatonTime = 0;
        $isPreperationTime = false;
        $preOrderFrom = null;
        $isLimitOrder = false;

        if ($preferences->is_enable_store_pickup) {
            $deliveryHour = json_decode($preferences->pickup_hour);
            $disabledDate = json_decode($preferences->pickup_disabled_date);
            $preOrderFrom = $preferences->pickup_pre_order_from;
            $preperatonTime = $preferences->pickup_preperation_value;
            $isPreperationTime = $preferences->pickup_is_preperation_time;
            $isLimitOrder = $preferences->pickup_is_limit_order;
        }

        return response()->json([
            'deliveryHour' => $deliveryHour,
            'disabledDate' => $disabledDate,
            'preOrderFrom' => $preOrderFrom,
            'preperationTime' => $preperatonTime,
            'isPreperationTime' => $isPreperationTime,
            'isLimitOrder' => $isLimitOrder,
        ]);
        // dd($preferences);
    }

    public function showCheckoutPages(Request $request)
    {
        $pageNameArray = [
            'information' => 'CustomerInformation',
            'shipping' => 'ShippingMethod',
            'payment' => 'PaymentMethod',
            'success' => 'CheckoutSuccessful',
            'mini-store' => 'MiniStoreCheckoutPage',
            'outOfStock' => 'OutOfStock',
        ];
        $trackingPageName = [
            'information' => 'Customer Information',
            'shipping' => 'Shipping Methods',
            'payment' => 'Payment Methods',
            // 'success' => 'Checkout Success',
            'mini-store' => 'Mini Store Checkout',
            'outOfStock' => null
        ];
        $currentPath = $request->path;
        if (!isset($pageNameArray[$currentPath])) {
            abort(404);
        };
        $pagePath = ($currentPath === 'mini-store' ? 'mini-store' : 'online-store') . '/pages/' . $pageNameArray[$currentPath];

        $accountId = $this->getCurrentAccountId();
        $isLocalEnv = app()->environment() === 'local';
        $domain = AccountDomain::getDomainRecord();

        $salesChannel = $isLocalEnv
            ? 'online-store'
            : $domain->type;
        $appSaleChannel = new SaleChannel();

        $publishPageBaseData = $this->getPublishedPageBaseData();
        if (!$appSaleChannel->isActiveSaleChannel($salesChannel, $accountId)) abort(404);

        $request['accountId'] = $accountId;
        $shopName = Account::find($accountId)->company;
        $preferences = EcommercePreferences::firstWhere('account_id', $accountId);
        $sellerEmail = User::where('currentAccountId', $accountId)->first()->email;
        $currencyDetails = Currency::where('account_id', $accountId)->where('isDefault', 1)->first();
        $location = Location::ignoreAccountIdScope()->where('account_id', $accountId)->first();
        $preferences['latestUpdateTime'] = strtotime($preferences->updated_at);
        $order = Order::where('account_id', $accountId)
            ->where('delivery_hour_type', 'custom')
            ->get();
        $paymentMethod = $this->getCheckoutPayment($accountId, $currencyDetails, false);
        $legalPolicy = LegalPolicy::with('legalPolicyType')
            ->where('account_id', $accountId)
            ->whereNotNull('template')
            ->orderBy('type_id', 'asc')
            ->get();
        $pageType = $domain->type;
        if (Auth::guard('ecommerceUsers')->check()) {
            $ecommerceUser = Auth::guard('ecommerceUsers')->user();
            $customerInfo = [
                'user' => $ecommerceUser,
                'processedContact' => $ecommerceUser->processedContact,
                'address' => $ecommerceUser->addressBook[0],
            ];
        }

        $visitorId = $_COOKIE["visitor_id"] ?? null;

        $trackingData = new Request;
        $trackingData->pageName = $trackingPageName[$currentPath] ?? null;
        $trackingData->visitorRefKey = $visitorId;

        $trackPageView = $visitorId && $trackingData->pageName
            ? app('App\Http\Controllers\OnlineStoreTrackingController')->trackPageView($trackingData)
            : null;

        $storeData = [
            'shopName' => $shopName ?? null,
            'preferences' => $preferences ?? null,
            'currencyDetails' => $currencyDetails ?? null,
            'location' => $location ?? null,
            'products' => $this->getAllActiveProducts($accountId) ?? [],
            'categories' => $this->getAllCategoriesWithActiveProducts($accountId) ?? [],
            'customOrders' => $order ?? [],
            'isActiveSaleChannel' => $appSaleChannel->isActiveSaleChannel($salesChannel, $accountId) ?? true,
            'sellerEmail' => $sellerEmail ?? null,
            'paymentMethods' => $paymentMethod ?? [],
            'legalPolicy' => $legalPolicy ?? [],
            'customerInfo' => $customerInfo ?? null,
        ];

        return Inertia::render(
            $pagePath,
            array_merge($publishPageBaseData, ['storeData' => $storeData, 'pageType' => $pageType,])
        );
    }

    public function getCheckoutInformation(Request $request)
    {
        $accountId = $this->getCurrentAccountId();
        $isLocalEnv = app()->environment() === 'local';
        $domain = AccountDomain::getDomainRecord();
        $salesChannel = $isLocalEnv
            ? 'online-store'
            : $domain->type;
        $appSaleChannel = new SaleChannel();

        if ($appSaleChannel->isActiveSaleChannel($salesChannel, $accountId)) {
            $request['accountId'] = $accountId;
            $hasAutomationPromo = Promotion::where('account_id', $accountId)->exists();
            $formDetail = $request->formDetail;

            $hasShipping = ShippingZone::where('account_id', $accountId)->exists();
            $hasEasyParcel = EasyParcel::where('account_id', $accountId)->where('easyparcel_selected', 1)->exists();
            $hasLalamove = Lalamove::where('account_id', $accountId)->where('lalamove_selected', 1)->exists();
            $hasDelyva =  Delyva::where('account_id', $accountId)->where('delyva_selected', 1)->exists();

            if (isset($formDetail)) {
                $locationInfo = $request->isShippingRequired ? $formDetail['shipping'] : $formDetail['billing'];
                $taxSetting = $this->checkTaxSetting($accountId, $locationInfo);
                $request['customerShippingDetails'] = $formDetail['shipping'];
                $shippingMethod = $this->checkShippingMethod($request, false);
                $hasEasyParcel = EasyParcel::where('account_id', $accountId)->where('easyparcel_selected', 1)->exists();
                $hasLalamove = Lalamove::where('account_id', $accountId)->where('lalamove_selected', 1)->exists();
                $delyvaInfo =  Delyva::where('account_id', $accountId)->where('delyva_selected', 1)->first();
                $hasDelyva = isset($delyvaInfo);
                if ($hasDelyva) {
                    $hasDelyva = $locationInfo['country'] === "Malaysia"
                        && Location::ignoreAccountIdScope()->where('account_id', $accountId)->first()->country === "Malaysia"
                        ?? false; //service only available in malaysia
                }
                $customerEmail = $formDetail['customerInfo']['email'];
                if (isset($customerEmail)) {
                    $processedContact = ProcessedContact::where('account_id', $accountId)->where('email', $customerEmail)->first();
                }
            }
        }

        return response()->json([
            'taxSetting' => $taxSetting ?? [],
            'shippingMethod' => $shippingMethod ?? [],
            'hasShipping' => $hasShipping ?? false,
            'hasEasyParcel' => $hasEasyParcel ?? false,
            'hasLalamove' => $hasLalamove ?? false,
            'hasDelyva' => $hasDelyva ?? false,
            'delyvaInfo' => $delyvaInfo ?? NULL,
            'hasAutomationPromo' => $hasAutomationPromo ?? false,
            'processedContact' => $processedContact ?? [],
            'cashbacks' => $this->getCashback($accountId) ?? [],
            'isActiveSaleChannel' => $appSaleChannel->isActiveSaleChannel($salesChannel, $accountId) ?? true,
        ]);
    }

    public function getCashback($accountId)
    {
        $cashbacks = Cashback::where('account_id', $accountId)->with('segments')->orderBy('cashback_amount', 'DESC')->orderBy('expire_date', 'DESC')->get();
        $segmentService = new SegmentService();

        foreach ($cashbacks as $cashback) {
            $segments = $cashback->segments;
            $contactIds = array();
            foreach ($segments as $segment) {
                $contactIds = array_unique(
                    array_merge($contactIds, $segmentService->filterContacts($segment->conditions, null, true, $accountId))
                );
                $contactIds = $segment->contacts(true);
            }
            $contactIds = array_values($contactIds);
            $cashback['salesChannel'] = $cashback->saleChannels()->get();
            $cashback['contactIds'] = $contactIds;
        }
        return $cashbacks;
    }

    public function checkTaxSetting($accountId, $locationInfo)
    {
        $taxName = '';
        $taxRate = 0;
        $taxSetting = Tax::where('account_id', $accountId)->first();
        $taxCountry = $locationInfo['country'];
        $taxState = $locationInfo['state'];
        // dump($request);
        if ($taxSetting !== null) {
            $taxSettingCountry = TaxCountry::where('tax_setting_id', $taxSetting->id)->where('country_name', $taxCountry)->first();
            if ($taxSettingCountry !== null) {
                if ($taxSettingCountry->has_sub_region) {
                    $taxSettingState = TaxCountryRegion::where('country_id', $taxSettingCountry->id)->where('sub_region_name', $taxState)->first();
                    if ($taxSettingState !== null) {
                        $taxRate = $taxSettingState->total_tax;
                        $taxName = $taxSettingState->tax_name;
                    } else {
                        $taxRate = $taxSettingCountry->country_tax;
                        $taxName = $taxSettingCountry->tax_name;
                    }
                } else {
                    $taxRate = $taxSettingCountry->country_tax;
                    $taxName = $taxSettingCountry->tax_name;
                }
            }
        }
        return ['taxRate' => $taxRate, 'taxName' => $taxName, 'setting' => $taxSetting];
    }

    public function getAllActiveProducts($accountId)
    {
        $currency = Currency::where('account_id', $accountId)->where('isDefault', 1)->first();;
        return UsersProduct::where(['account_id' => $accountId, 'status' => 'active'])->get()->map(function ($product) use ($accountId, $currency) {
            $product->productPrice = $this->priceFormater($product->productPrice, $currency);
            $product->variant_details = $product->variant_details;
            $product->custom_options =  $this->getProductOption($product);
            $product->saleChannels = $this->selectedSaleChannels($product);
            return $product;
        });
    }

    public function getProductWeight($cartItems)
    {
        $totalWeight = 0;
        foreach ($cartItems as $key => $value) {
            $weight = 0;
            if ($value['hasVariant']) {
                $variantDetails = VariantDetails::where('reference_key', $value['variantRefKey'])->first();
                $weight = $variantDetails['weight'];
            } else {
                $userProduct = UsersProduct::where('reference_key', $value['reference_key'])->first();
                $weight = $userProduct['weight'];
            }
            $totalWeight += (float)$weight  * $value['qty'];
        }
        return $totalWeight;
    }
    public function getAllCategoriesWithActiveProducts($accountId)
    {
        return Category::where('account_id', $accountId)->with(array('products' => function ($query) {
            $query->where('status', 'active');
        }))->orderBy('priority')->get();
    }

    public function checkShippingMethodAvailability(Request $request)
    {
        $shipping = $request->customerShippingDetails;
        $shippingMethod = $this->checkShippingMethod($request, false);

        $isValid = count($shippingMethod) > 0;
        if($isValid)
            return response()->json([
                'valid' => $isValid
            ]);

        $request->fields = [
            'send_code' => $shipping['zipCode'],
            'send_state' => $shipping['state'],
            'send_country' => $shipping['country'],
            'street' => $shipping['address'],
            'city' => $shipping['city'],
            'state' => $shipping['state'],
            'zip' => $shipping['zipCode'],
            'country' => $shipping['country'],
            'weight' => 1,
            'phone' => '01212121212',
            'name' => 'None',
            'scheduleAt' => $request['scheduleAt'],
            'date_coll' => $request['date_coll'],
        ];
        $accountId = $this->getCurrentAccountId();
        $hasEasyParcel = EasyParcel::where('account_id', $accountId)->where('easyparcel_selected', 1)->exists();
        $hasLalamove = Lalamove::where('account_id', $accountId)->where('lalamove_selected', 1)->exists();
        $hasDelyva =  Delyva::where('account_id', $accountId)->where('delyva_selected', 1)->exists();
        $request->accountId = $accountId;
        $request->totalWeight = 1;

        // Currently supported countries of easy parcel
        $supportedCountries = ['Malaysia', 'Singapore', 'Thailand', 'Indonesia'];
        if ($hasEasyParcel && in_array($shipping['country'], $supportedCountries)) {
            try {
                $easyparcel = app('App\Http\Controllers\EasyParcelController')->shippingRateChecking($request);
                $shippingMethod = [...$shippingMethod, ...$easyparcel->original['methods']];
            } catch (\Throwable $th) {
            }
        }
        if ($hasLalamove && $shipping['country'] === 'Malaysia') {
            try {
                $lalamove = app('App\Http\Controllers\LalamoveController')->quotationChecking($request);
                foreach ($lalamove->original['quotations'] as $value) {
                    array_push($shippingMethod, $value);
                }
            } catch (\Throwable $th) {
            }
        }
        if ($hasDelyva && $shipping['country'] === 'Malaysia') {
            try {
                $delyva = app('App\Http\Controllers\DelyvaController')->quotationCheck($request);
                $shippingMethod = [...$shippingMethod, ...$delyva['data']['services']];
            } catch (\Throwable $th) {
            }
        }

        return response()->json([
            'valid' => count($shippingMethod) > 0
        ]);
    }
}
