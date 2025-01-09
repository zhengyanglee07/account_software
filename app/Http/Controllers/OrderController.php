<?php

namespace App\Http\Controllers;

use App\EasyParcelFulfillment;
use App\EasyParcelShipment;
use App\EasyParcelShipmentParcel;
use App\Events\ProductPurchased;
use App\funnel;
use App\LalamoveDeliveryOrder;
use App\LalamoveFulfillment;
use App\LalamoveQuotation;
use App\Delyva;
use App\DelyvaQuotation;
use App\DelyvaFulfillment;
use App\DelyvaDeliveryOrder;
use App\DelyvaDeliveryOrderDetail;
use App\Page;
use App\Location;

use App\EcommerceVisitor;
use App\EcommerceTrackingLog;
use App\EcommerceAbandonedCart;
use App\EcommercePage;

use App\Mail\OrderPaymentBuyerEmail;
use App\Mail\OrderPaymentSellerEmail;
use App\Services\EasyParcelAPIService;
use App\Services\LalamoveAPIService;
use Auth;
use App\UsersProduct;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Http\Request;
use App\Order;
use App\OrderDetail;
use Illuminate\Support\Facades\DB;
use App\ProcessedContact;
use App\ProcessedAddress;
use App\OrderTransactions;
use App\Account;
use App\AccountDomain;
use App\Currency;
use App\Events\OrderPlaced;
use App\Mail\OrderShippedEmail;
use App\Models\Promotion\Promotion;
use Composer\XdebugHandler\Process;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Http;
use App\OrderRefundLog;
use App\ProductVariant;
use App\Repository\Checkout\FormDetail;
use App\Services\Checkout\CashbackService;
use App\Services\Checkout\CheckoutOrderService;
use App\Services\Checkout\PaymentService;
use App\Services\OrderService;
use App\Services\ProcessedContactService;
use App\StoreCredit;
use App\Traits\AuthAccountTrait;
use Mail;
use Illuminate\Support\Carbon;
use App\Variant;
use App\VariantDetails;
use App\Traits\CurrencyConversionTraits;
use App\VariantValue;
use PDF;
use Inertia\Inertia;
use App\Traits\ReferralCampaignTrait;
use App\Traits\SegmentTrait;
use App\Traits\UsersProductTrait;
use Illuminate\Support\Facades\Log;
use App\Traits\AffiliateMemberAccountTrait;

class OrderController extends Controller
{
    use CurrencyConversionTraits, AuthAccountTrait, ReferralCampaignTrait, SegmentTrait, UsersProductTrait, AffiliateMemberAccountTrait;

    public function currentAccount()
    {
        return Auth::user()->currentAccount();
    }

    public function variantCombinationName($variant)
    {
        $option_1_name = $this->findVariantName(1, $variant->option_1_id);
        $option_2_name = $this->findVariantName(2, $variant->option_2_id);
        $option_3_name = $this->findVariantName(3, $variant->option_3_id);
        return trim($option_1_name . $option_2_name . $option_3_name);
    }

    public function findVariantName($index, $id)
    {
        $variant = VariantValue::find($id);
        if ($variant) {
            return $index == 1 ? $variant->variant_value : " / " . $variant->variant_value;
        }
        return "";
    }

    public function editOrderDetail($reference_key)
    {
        $currentAccountId = $this->getCurrentAccountId();
        $orders = Order::where('reference_key', $reference_key)->where('account_id', $currentAccountId)->first();
        $orders->currency = ($orders->currency == 'MYR') ? 'RM' : $orders->currency;
        $currency = $orders->currency;
        $exchangeRate = $orders->exchange_rate;
        $orderDetail = OrderDetail::where('order_details.order_id', $orders->id)->get();
        $userProducts = UsersProduct::where('account_id', $currentAccountId)->get();
        $variationCombination = [];
        foreach ($userProducts as $product) {
            if ($product->hasVariant == 1) {
                foreach ($product->variant_details as $variant_details) {
                    // dump("all variant details is ", $variant_details);
                    $key = $this->variantCombinationName($variant_details);
                    $item = (object)[
                        'productID' => $product->id,
                        'productTitle' => $product->productTitle,
                        'variant_name' => $key,
                        'quantity' => 1,
                        'total' => $variant_details->price,
                        'productPrice' => $variant_details->price,
                        'unit_price' => $variant_details->price,
                        'paymentStatus' => "Unpaid",
                        'fulfillmentStatus' => "Unfulfilled",
                        'productImagePath' => $product->productImagePath,
                        'isChecked' => false,
                        'disabled' => false
                    ];
                    array_push($variationCombination, $item);
                }
            }
        }
        // dd($variationCombination);
        foreach ($orderDetail as $value) {
            $value->original_quantity = $value->quantity;
        }
        return Inertia::render('order/pages/EditOrder', compact('orders', 'orderDetail', 'userProducts', 'currency', 'variationCombination', 'exchangeRate'));
    }

    public function getRandomId($table, $type)
    {
        $condition = true;
        do {
            return $randomId = random_int(100000000001, 999999999999);
            $condition = DB::table($table)->where($type, $randomId)->exists();
        } while ($condition);
    }

    public function savePaymentDetail(request $request)
    {
        $landing = Page::where('id', $request->landingSettings['id'])->first();
        $newProcessContact = $this->createProcessContact($request, $landing);
        $newOrder = $this->updateAndCreateOrder($request, $newProcessContact, $landing);
        $newOrderDetail = $this->createOrderDetail($request, $newProcessContact, $newOrder);
        $newOrderTransaction = $this->createOrderTransaction($request, $newProcessContact, $newOrder);
        $this->calculateInventory($request->productDetail, $request->productQuantity);

        // TODO: add back visitor
        //        $visitor = EcommerceVisitor::where('reference_key',$_COOKIE['visitor_id'])->first();
        //        $visitor->order_id = $newOrder->id;
        //        $visitor->save();
        // event(new ProductPurchased($newOrder, $newProcessContact));

        $visitorId = $request->visitorId ?? null;
        $cartId = $request->cartId ?? null;

        if ($visitorId) {
            $processedContact = ProcessedContact::where([
                'account_id' => $newOrder->account_id,
                'id' => $newOrder->processed_contact_id
            ])->first();

            $visitor = EcommerceVisitor::firstWhere('reference_key', $visitorId);

            $visitor->processed_contact_id = $processedContact->id ?? null;

            $visitor->is_completed = true;
            $visitor->order_id = $newOrder->id;
            $visitor->save();
        }

        if ($cartId) {
            $cart = EcommerceAbandonedCart::firstWhere('reference_key', $cartId);

            $cart->product_detail = null;
            $cart->status = false;

            $cart->save();
        }


        event(new OrderPlaced($newOrder));

        $orderCount = Order::where('processed_contact_id', $newOrder->processed_contact_id)->where('funnel_id', $request->landingSettings['funnel_id'])->get()->count();
        $people = ProcessedContact::find($newOrder->processed_contact_id);
        $user = $this->referralUser($people, $request->landingSettings['funnel_id']);
        $cookie = $user ? cookie('funnel#user#' . $user['campaign'], $user['referralCode'], (3 * 30 * 24 * 60)) : null; // 90 days
        if ($request->hasCookie('referral') && $orderCount == 1) {
            $this->checkReferralCampaignAction($request->getHost(), 'purchase', $request->landingSettings['funnel_id'], $people, $newOrder);
        }
        $compactData = [
            'contactRandomId' => $newProcessContact->contactRandomId,
            'paymentReference' => $newOrder->payment_references,
        ];
        return $cookie ? response()->json($compactData)->cookie($cookie) : response()->json($compactData);
    }

    public function createProcessContact($request, $landing)
    {
        $customerInfo = $request->customerDetail['customerInfo'];
        $constrains = isset($customerInfo['email']) ? ['email' => $customerInfo['email']] : ['phone_number' => $customerInfo['phoneNumber']];
        $newProcessContact = ProcessedContact::where('account_id', $request->landingSettings['account_id'])
            ->firstOrNew($constrains);
        $newProcessContact->contactRandomId = $newProcessContact->contactRandomId != null
            ? $newProcessContact['contactRandomId']
            : $this->getRandomId('processed_contacts', 'contactRandomId');
        $newProcessContact->account_id = $request->landingSettings['account_id'];
        $newProcessContact->customer_id = $request->customerId;
        $newProcessContact->email = empty($customerInfo['email']) ?  $newProcessContact->email : $customerInfo['email'];
        $newProcessContact->fname = empty($customerInfo['fullName']) ? $newProcessContact->fname : $customerInfo['fullName'];
        $newProcessContact->phone_number = empty($customerInfo['phoneNumber']) ? $newProcessContact->phone_number : $customerInfo['phoneNumber'];
        $newProcessContact->ordersCount += 1;
        $newProcessContact->totalSpent += $request->paymentIntent['amount'] / 100;
        $funnel = funnel::ignoreAccountIdScope()->where('id', $landing->funnel_id)->first();
        $newProcessContact->acquisition_channel = $funnel->funnel_name . " - " . $landing->name;
        $newProcessContact->dateCreated = date('Y-m-d H:i:s', $request->paymentIntent['created']);
        $newProcessContact->save();
        ProcessedAddress::updateOrCreate(['processed_contact_id' => $newProcessContact->id]);
        return $newProcessContact;
    }
    public function updateAndCreateOrder($param, $processContact, $landing)
    {
        //saveOrder
        $latestOrder = DB::table('orders')
            ->where('account_id', $param->landingSettings['account_id'])
            ->where('processed_contact_id', $processContact->id)
            ->orderBy('created_at', 'desc')
            ->first();
        $latestOrderNumber = DB::table('orders')
            ->where('account_id', $param->landingSettings['account_id'])
            ->orderBy('created_at', 'desc')
            ->first();
        $paymentReference = $latestOrder === null ? "" : $latestOrder->payment_references;
        $param->fId === $paymentReference
            ? $newOrder = Order::where('id', $latestOrder->id)->first()
            : $newOrder = new Order();
        $newOrder->account_id = $param->landingSettings['account_id'];
        $newOrder->processed_contact_id = $processContact->id;
        $newOrder->funnel_id = $landing->funnel_id;
        $newOrder->landing_id = $param->landingSettings['id'];
        if ($param->fId === $paymentReference) {
            $newOrder->order_number = $latestOrder->order_number;
        } else {
            $orderNumber = Order::all()->where('account_id', $param->landingSettings['account_id'])->isEmpty() ? 1000 : $latestOrderNumber->order_number + 1;
            if ($latestOrder !== null) {
                $orderNumber = $latestOrder->payment_process_status === 'Failed' || $latestOrder->payment_process_status === 'Pending'
                    ? $latestOrder->order_number
                    : $orderNumber;
            }
            $newOrder->order_number = $orderNumber;
            $newOrder->reference_key = $this->getRandomId('orders', 'order_number');
        }
        $newOrder->acquisition_channel = 'Funnel';
        $fulfilledStatus = "Unfulfilled";
        if ($param->fId === $paymentReference) {
            if ($param->productDetail['physicalProducts'] === null && $latestOrder->fulfillment_status !== 'Unfulfilled') {
                $fulfilledStatus = "Fulfilled";
                $newOrder->additional_status = "Closed";
                $newOrder->fulfilled_at = date('Y-m-d H:i:s', $param->paymentIntent['created']);
            }
        } else {
            if ($param->productDetail['physicalProducts'] === null) {
                $fulfilledStatus = "Fulfilled";
                $newOrder->additional_status = "Closed";
                $newOrder->fulfilled_at = date('Y-m-d H:i:s', $param->paymentIntent['created']);
            }
        }
        $newOrder->fulfillment_status = $fulfilledStatus;
        $newOrder->payment_status = "Paid";
        $newOrder->payment_process_status = "Success";
        $newOrder->paid_at = date('Y-m-d H:i:s', $param->paymentIntent['created']);
        $newOrder->payment_references = isset($param->fId) === false ? $this->getRandomId('orders', 'payment_references') : $param->fId;
        $newOrder->payment_method = 'card';
        $newOrder->currency = $param->currency;
        $newOrder->exchange_rate = $param->exchangeRate;

        if ($param->taxSetting['setting']['is_product_include_tax']) {
            $newOrder->subtotal = $param->fId === $paymentReference
                ? $param->subtotal + $latestOrder->subtotal
                : $param->subtotal;
        } else {
            $newOrder->subtotal = $param->fId === $paymentReference
                ? $param->subtotal + $latestOrder->subtotal
                : $param->subtotal;
        }
        if ($param->type == 'sales-form') {
            $newOrder->tax_name = $param->taxSetting['taxName'];
            $newOrder->tax_rate = $param->taxSetting['taxRate'];
            $newOrder->is_product_include_tax = $param->taxSetting['setting']['is_product_include_tax'];
            $newOrder->is_shipping_fee_taxable = $param->taxSetting['setting']['is_shipping_fee_taxable'];
            $newOrder->shipping_tax = ($param->taxSetting['setting']['is_shipping_fee_taxable'])
                ? $param->shippingFee * ($newOrder->tax_rate / 100) : 0;
        }
        $newOrder->taxes = $param->fId === $paymentReference ? $param->totalTax + $latestOrder->taxes : $param->totalTax;
        if (!empty($param->shippingMethod)) {
            $newOrder->shipping_method_name = $param->shippingMethod['shipping_method'];
            $newOrder->shipping_method = $param->shippingMethod['shipping_name'];
        }
        $newOrder->shipping += $param->shippingFee;
        $total = $param->fId === $paymentReference
            ?  $this->getOrderCurrencyRange($param->paymentIntent['amount'] / 100, $param->currency) + $latestOrder->total
            : $this->getOrderCurrencyRange($param->paymentIntent['amount'] / 100, $param->currency);
        $newOrder->total = $total;
        $newOrder->paided_by_customer = $newOrder->total;
        $customerInfo = $param->customerDetail['customerInfo'];
        $shipping = $param->customerDetail['shipping'];
        $billing = $param->customerDetail['billing'];
        if ($shipping['address']) {
            $newOrder->shipping_name = $shipping['fullName'];
            $newOrder->shipping_phoneNumber = $shipping['phoneNumber'];
            $newOrder->shipping_company_name = $shipping['companyName'];
            $newOrder->shipping_address =  $shipping['address'];
            $newOrder->shipping_city = $shipping['city'];
            $newOrder->shipping_state =  $shipping['state'];
            $newOrder->shipping_zipcode = $shipping['zipCode'];
            $newOrder->shipping_country = $shipping['country'];
        }
        $newOrder->billing_name = $billing['fullName'];
        $newOrder->billing_company_name = $billing['companyName'];
        $newOrder->billing_address = $billing['address'];
        $newOrder->billing_city = $billing['city'];
        $newOrder->billing_state = $billing['state'];
        $newOrder->billing_zipcode = $billing['zipCode'];
        $newOrder->billing_country = $billing['country'];
        $newOrder->save();
        return $newOrder;
    }

    public function createOrderDetail($param, $processContact, $order)
    {
        $currency = Currency::where('account_id', $param->landingSettings['account_id'])->where('currency', $param->currency)->first();
        $exchangeRate = $param->exchangeRate === null ? '1' : $param->exchangeRate;
        $newOrderDetail = new OrderDetail();
        $newOrderDetail->order_id = $order->id;
        $newOrderDetail->users_product_id = $param->productDetail['id'];
        $param->physicalProduct == null ? $newOrderDetail->order_number = "#" . $order->order_number . "-F1" : "";
        $fulfilledStatus = "Unfulfilled";
        if ($param->productDetail['physicalProducts'] === null) {
            $fulfilledStatus = "Fulfilled";
        }
        $newOrderDetail->is_virtual = ($param->physicalProduct !== 'on');
        $newOrderDetail->fulfillment_status = $fulfilledStatus;
        $newOrderDetail->product_name = $param->productDetail['productTitle'];
        $newOrderDetail->image_url = $param->productDetail['productImagePath'];
        $newOrderDetail->variant_name = $param->selectedVariant;
        $param->physicalProduct === 'on' ? $newOrderDetail->variant_name = $param->selectedVariant : "";
        $newOrderDetail->fulfilled_at = date('Y-m-d H:i:s', $param->paymentIntent['created']);
        $newOrderDetail->payment_status = "Paid";
        $newOrderDetail->paid_at = date('Y-m-d H:i:s', $param->paymentIntent['created']);
        $newOrderDetail->unit_price = $param->productPrice;
        $newOrderDetail->weight = floatVal($param->productDetail['weight']) * $param->productQuantity;
        $newOrderDetail->quantity = $param->productQuantity;
        $newOrderDetail->total = $newOrderDetail->unit_price * $newOrderDetail->quantity;
        $newOrderDetail->is_taxable = $param->productDetail['isTaxable'];
        $newOrderDetail->save();
    }

    public function createOrderTransaction($param, $processContact, $order)
    {
        $newTransaction = new OrderTransactions();
        $newTransaction->order_id = $order->id;
        $newTransaction->transaction_key = $this->getRandomId('order_transactions', 'transaction_key');
        $newTransaction->total =  $this->getOrderCurrencyRange($param->paymentIntent['amount'] / 100, $param->currency);
        $newTransaction->paid_at = now();
        $newTransaction->payment_status = "Paid";
        $newTransaction->save();

        $notifiableSetting = Account::find($order->account_id)->notifiableSetting;
        $sellerEmails = [$order->sellerEmail()];
        if (!empty($notifiableSetting->notification_email)) {
            $sellerEmails = array_map('trim', explode(',', $notifiableSetting->notification_email));
        }
        $buyerEmail = $order->processedContact->email;
        if ($notifiableSetting->is_fulfill_order_notifiable && !is_null($buyerEmail) && count($sellerEmails)) {
            Mail::to($buyerEmail)->send(new OrderPaymentBuyerEmail($order));
            foreach ($sellerEmails as $sellerEmail) {
                Mail::to($sellerEmail)->send(new OrderPaymentSellerEmail($order));
            }
        }
    }

    public function calculateInventory($product, $quantity)
    {
        $userProduct = UsersProduct::find($product['id']);
        $remainingStock = $userProduct->quantity;
        if ($remainingStock !== 0) $userProduct->update(['quantity' => $remainingStock - $quantity]);
    }

    /**
     * @param $reference_key
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function getOrder($reference_key)
    {
        $currentAccountId = $this->getCurrentAccountId();
        $currency = Account::find($currentAccountId)->currency;
        $processedContact = ProcessedContact::withTrashed()->where('account_id', $currentAccountId)->get();
        $orders = Order::where('reference_key', $reference_key)
            ->where('account_id', $currentAccountId)
            ->with(
                'easyParcelShipments',
                'easyParcelShipmentParcels',
                'easyParcelFulfillments.orderDetail',
                'lalamoveQuotations',
                'lalamoveDeliveryOrders',
                'delyvaQuotations',
                'delyvaDeliveryOrders',
                'orderDiscount',
            )
            ->first();
        $orders->nextOrder = $orders->nextOrder();
        $orders->previousOrder = $orders->previousOrder();
        $orders->currency = ($orders->currency == 'MYR') ? 'RM' : $orders->currency;

        $this->authorize('view', $orders);
        $orderDetail = OrderDetail::where('order_id', $orders->id)->withTrashed()->get();
        $refundLog = OrderRefundLog::where('order_id', $orders->id)->get();
        $accountTimeZone = Account::find($currentAccountId)->timeZone;
        // $ Info = Delyva::where('account_id',$currentAccountId)->first();
        $location = Location::where('account_id', $currentAccountId)->first();

        $customer = $processedContact->find($orders['processed_contact_id']);
        // dd($customer);
        $orders->name = $customer == null ? 'No customer' : $customer->displayName;
        $orders->email = $customer == null ? '' : $customer->email;
        $orders->phoneNo = $customer == null ? '' : $customer->phone_number;
        $orders->fname = $customer == null ? '' : $customer->fname;
        $orders->lname = $customer == null ? '' : $customer->lname;
        $orders->processedRandomId = $customer == null ? '' : $customer->contactRandomId;
        $orderTime = new Carbon($orders->created_at);
        $orders->convertTime = $orderTime->timezone($accountTimeZone)->isoFormat('MMMM D, YYYY \\at h:mm a');
        $environment = app()->environment();
        $affiliateCommissionInfo = $this->getAffiliateCommisionInfo($orders->id);

        return Inertia::render('order/pages/ViewOrder', compact('orders', 'orderDetail', 'currency', 'refundLog', 'location', 'environment', 'affiliateCommissionInfo'));
    }

    public function index(Request $request)
    {
        $currentAccountId = $this->getCurrentAccountId();
        // $isFetchedByAxios = $request->query('requestSource');
        $currency = Account::find($currentAccountId)->currency;

        $paginatedOrders = Order::where('account_id', $currentAccountId)
            ->where(function ($query) {
                $query->where('payment_process_status', 'Success')->orWhereNull('payment_process_status');
            })
            ->latest()
            ->paginate(150);

        $processedContact = ProcessedContact::where('account_id', $currentAccountId)->get();
        $accountTimeZone = Account::find($currentAccountId)->timeZone;

        $orders = $paginatedOrders->map(function ($order) use ($processedContact, $accountTimeZone) {
            $customer = $processedContact->find($order['processed_contact_id']);
            $order->convertDate = $order->created_at->timeZone($accountTimeZone)->isoformat('MMMM D, YYYY');
            $order->convertTime = $order->created_at->timeZone($accountTimeZone)->isoformat('h:mm a');
            $order->convertDateTime = $order->created_at->timezone($accountTimeZone)->isoFormat('MMMM D, YYYY \\at h:mm a');
            $order->name = $customer == null ? "No customer" : $customer->displayName;
            $order->currency = ($order->currency == 'MYR') ? 'RM' : $order->currency;
            return $order;
        });

        // if($isFetchedByAxios) {
        // return response()->json([
        //     'updatedOrders' => $orders
        // ]);
        // }

        return Inertia::render('order/pages/AllOrders', [
            'dbOrders' => $orders,
            'paginatedOrders' => $paginatedOrders,
            'currency' => $currency,
        ]);
    }


    /**
     * @param \Illuminate\Http\Request $request
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Exception
     */
    public function deleteOrderDetail(Request $request): void
    {
        $order_id = $request->orders['id'];
        $currentAccountId = $this->getCurrentAccountId();
        $deleteOrder = Order::where('account_id', $currentAccountId)->where('id', $order_id)->firstOrFail();

        $this->authorize('delete', $deleteOrder);

        $deleteOrder->delete();
    }

    public function updateOrderDetail(Request $request)
    {
        $currentAccountId = $this->getCurrentAccountId();
        $order_id = $request->ordersJs['id'];

        if ($request->existedProduct != null) {
            foreach ($request->existedProduct as $product) {
                $orderDetail = OrderDetail::where('id', $product['id'])->where('order_id', $order_id)
                    ->where('fulfillment_status', "Unfulfilled")
                    ->first();
                $orderDetail->quantity = $product['quantity'];
                $orderDetail->total = $product['total'];
                $orderDetail->save();
            }
        }

        if (!empty($request->orderProduct)) {
            foreach ($request->orderProduct as $newProduct) {
                $newOrderDetail = new OrderDetail();
                $newOrderDetail->order_id = $request->ordersJs['id'];
                $newOrderDetail->product_name = $newProduct['product_name'];
                $newOrderDetail->variant_name = $newProduct['variant_name'];
                $newOrderDetail->users_product_id = $newProduct["productID"];
                $newOrderDetail->fulfillment_status = $newProduct["fulfillmentStatus"];
                $newOrderDetail->variant = json_encode($newProduct['variant']);
                $newOrderDetail->customization = json_encode($newProduct['customization']);
                $newOrderDetail->order_number = "";
                $newOrderDetail->image_url = $newProduct['image_url'];
                $newOrderDetail->payment_status = $newProduct["paymentStatus"];
                $newOrderDetail->unit_price = $newProduct["unit_price"];
                $newOrderDetail->weight = ($newProduct['weight'] == null) ? 0 : $newProduct['weight'] * $newProduct["quantity"];
                $newOrderDetail->quantity = $newProduct["quantity"];
                $newOrderDetail->total = $newProduct["total"];
                $newOrderDetail->save();
            }
        }

        if (count($request->removedProduct) > 0) {
            foreach ($request->removedProduct as $removedProduct) {
                $orderDetail = OrderDetail::find($removedProduct['id']);
                $orderDetail->quantity -= $removedProduct['original_quantity'];
                $orderDetail->total = $orderDetail->quantity * $orderDetail->unit_price;
                $orderDetail->save();
                $removedProductsInOrder = OrderDetail::where('order_id', $request->ordersJs['id'])
                    ->where('fulfillment_status', 'Removed')
                    ->where('users_product_id', $removedProduct['users_product_id'])->get();
                if (count($removedProductsInOrder) > 0) {
                    foreach ($removedProductsInOrder as $product) {
                        $variant = json_decode($product['variant'], TRUE);
                        $customization = json_decode($product['customization'], TRUE);
                        $result_variant = array_diff($variant, json_decode($removedProduct['variant']));
                        $result_customization =  array_diff($customization, json_decode($removedProduct['customization']));

                        if (empty($result_variant) && empty($result_customization)) {
                            $product->quantity += $removedProduct['original_quantity'];
                            $product->total = $product->quantity * $product->unit_price;
                            $product->save();
                        }
                    }
                } else {
                    $newOrderDetail = $orderDetail->replicate();
                    $newOrderDetail->quantity = $removedProduct['original_quantity'];
                    $newOrderDetail->fulfillment_status = "Removed";
                    $newOrderDetail->payment_status = "Refunded";
                    $newOrderDetail->parent_id = $orderDetail->id;
                    $newOrderDetail->total = $newOrderDetail->quantity * $newOrderDetail->unit_price;
                    $newOrderDetail->save();
                }
                if ($orderDetail->quantity == 0) {
                    $orderDetail->delete();
                }
                // $orderDetail = OrderDetail::where('id',$removedProduct['id'])->where('order_id',$removedProduct['order_id'])
                // 				->where('fulfillment_status',"Unfulfilled")
                // 				->first();
                // $orderDetail->fulfillment_status = "Removed";
                // $orderDetail -> save();
            }
        }

        $order = Order::where('id', $request->ordersJs['id'])->where('account_id', $currentAccountId)->first();
        $totalProduct = OrderDetail::where('order_id', $order_id)->count();
        $fulfillProduct = OrderDetail::where('order_id', $order_id)->where('fulfillment_status', "Fulfilled")->count();
        $unfulfillProduct = OrderDetail::where('order_id', $order_id)->where('fulfillment_status', "Unfulfilled")->count();
        $removedProduct = OrderDetail::where('order_id', $order_id)->where('fulfillment_status', "Removed")->count();
        if ($totalProduct - $removedProduct == $fulfillProduct) {
            $order->fulfillment_status = "Fulfilled";
        } else if ($totalProduct - $removedProduct == $unfulfillProduct) {
            $order->fulfillment_status = "Unfulfilled";
        } else {
            $order->fulfillment_status = "Partially Fulfilled";
        }
        $order->total = $request->total;
        $order->subtotal = $request->subTotal;
        $order->taxes = $request->taxes;
        $order->shipping = $request->shipping;
        $order->save();
        return response()->json([
            'order_id' => $request->ordersJs['reference_key']
        ]);
    }

    public function markOrderStatus($reference_key)
    {
        $currentAccountId = $this->getCurrentAccountId();
        $order = Order
            ::with('easyParcelShipments', 'lalamoveQuotations')
            ->where('reference_key', $reference_key)
            ->where('account_id', $currentAccountId)
            ->firstOrFail();
        $order->currency = ($order->currency == 'MYR') ? 'RM' : $order->currency;
        $currency = Account::find($currentAccountId)->currency;
        $afterShipPath = storage_path() . "/json/tracking-aftership.json";
        $trackingMyPath = storage_path() . "/json/tracking-my.json";
        $afterShipJson = json_decode(file_get_contents($afterShipPath), true);
        $trackingMyJson = json_decode(file_get_contents($trackingMyPath), true);
        $orderDetail = OrderDetail::where(['order_id' => $order->id, 'fulfillment_status' => 'Unfulfilled', 'is_virtual' => 0])->get();
        foreach ($orderDetail as $detail) {
            $detail->max = $detail->quantity;
        }
        // $compare1 = array_map(function($value){return $value['label'];},$afterShipJson['couriers']);
        // $compare2 = array_map(function($value){return $value['label'];},$trackingMyJson['couriers']);
        // $duplicate = array_uintersect($compare1,$compare2,function($value1,$value2){
        //     return substr_compare($value1,$value2,0,6);
        // });
        return Inertia::render('order/pages/MarkFulfillment', compact('orderDetail', 'order', 'currency', 'afterShipJson', 'trackingMyJson'));
    }

    /**
     * Show EasyParcel fulfillment page
     *
     * @param $reference_key
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     *
     */
    public function showFulfillmentWithEasyParcel($reference_key)
    {
        $currentAccountId = $this->getCurrentAccountId();
        $order = Order
            ::with('easyParcelShipments')
            ->where('account_id', $currentAccountId)
            ->where('reference_key', $reference_key)
            ->firstOrFail();
        $order->currency = ($order->currency == 'MYR') ? 'RM' : $order->currency;
        $currency = Currency::where(['account_id' => $currentAccountId, 'isDefault' => 1])->first();
        $unfulfilledOrderDetails = OrderDetail
            ::with('usersProduct')
            ->where('order_id', $order->id)
            ->where('fulfillment_status', 'Unfulfilled')
            ->where('is_virtual', 0)
            ->get();
        $location = Location::where('account_id', $currentAccountId)->first();

        return Inertia::render('order/pages/EasyParcelOrderFulfillment', compact('order', 'unfulfilledOrderDetails', 'location', 'currency'));
    }

    /**
     * Nearly the same with EasyParcel fulfillment page
     *
     * @param $reference_key
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function showFulfillmentWithLalamove($reference_key)
    {
        $currentAccountId = $this->getCurrentAccountId();
        $order = Order
            ::with('lalamoveQuotations')
            ->where('account_id', $currentAccountId)
            ->where('reference_key', $reference_key)
            ->firstOrFail();
        $order->currency = ($order->currency == 'MYR') ? 'RM' : $order->currency;
        $currency = Currency::where(['account_id' => $currentAccountId, 'isDefault' => 1])->first();
        $unfulfilledOrderDetails = OrderDetail
            ::where('order_id', $order->id)
            ->where('fulfillment_status', 'Unfulfilled')
            ->where('is_virtual', 0)
            ->get();
        $location = Location::first();

        return Inertia::render('order/pages/LalamoveOrderFulfillment', compact('order', 'unfulfilledOrderDetails', 'location', 'currency'));
    }

    public function showFulfillmentWithDelyva($reference_key)
    {
        $currentAccountId = $this->getCurrentAccountId();
        $order = Order::with('delyvaQuotations')
            ->where('account_id', $currentAccountId)
            ->where('reference_key', $reference_key)
            ->firstOrFail();
        // dd($order->delyvaQuotations[0]->order_id);
        $order->currency = ($order->currency == 'MYR') ? 'RM' : $order->currency;
        $currency = Currency::where(['account_id' => $currentAccountId, 'isDefault' => 1])->first();
        $unfulfilledOrderDetails = OrderDetail::where('order_id', $order->id)
            ->where('fulfillment_status', 'Unfulfilled')
            ->where('is_virtual', 0)
            ->get();
        $location = Location::first();
        $delyvaInfo = Delyva::firstwhere([
            'account_id' => $currentAccountId,
        ]);
        return Inertia::render('order/pages/DelyvaOrderFulfillment', compact('order', 'unfulfilledOrderDetails', 'location', 'currency', 'delyvaInfo'));
    }

    public function updateFulfillment(Request $request)
    {
        $currentOrder = Order::with('easyParcelShipments')->find($request->orderID);
        ++$currentOrder->maximum_indicator;
        $courierInfo =  $request->trackingInfo['selectedTracking'];
        $trackingInfo = [];
        if ($courierInfo) {
            $trackingInfo['courierName'] = $courierInfo['label'];
            if ($courierInfo['label'] == 'Others') {
                $parseUrl = parse_url($request->trackingInfo['trackingUrl']);
                if (in_array('http', $parseUrl) || in_array('https', $parseUrl)) {
                    $trackingInfo['trackingUrl'] = $request->trackingInfo['trackingUrl'];
                } else {
                    $trackingInfo['trackingUrl'] = "http://" . $request->trackingInfo['trackingUrl'];
                }
            } else {
                $trackingInfo['trackingNumber'] = $request->trackingInfo['trackingNumber'];
                if ($courierInfo['source'] == 'tracking-my') {
                    $trackingInfo['trackingUrl'] =  'https://www.tracking.my/'
                        . $courierInfo['value'] . '/' . $request->trackingInfo['trackingNumber'];
                } else if ($courierInfo['source'] == 'aftership') {
                    $trackingInfo['trackingUrl'] = 'https://track.aftership.com/trackings?tracking-numbers='
                        . $request->trackingInfo['trackingNumber'] . '&courier=' . $courierInfo['value'];
                }
            }
        }
        $fulfilledOrderDetailIds = [];
        foreach ($request->unfulfillItem as $item) {
            $orderDetail = OrderDetail::find($item['id']);
            $order_number = $currentOrder->order_number;
            if ($courierInfo) {
                $orderDetail->tracking_courier_service = $trackingInfo['courierName'];
                $orderDetail->tracking_url = $trackingInfo['trackingUrl'];
                $orderDetail->tracking_number = ($courierInfo['label'] == 'Others') ? null : $trackingInfo['trackingNumber'];
            }
            if ($item['quantity'] == $item['max']) {
                $orderDetail->order_number = "#" . $order_number . "-F" . $currentOrder->maximum_indicator;
                $orderDetail->fulfillment_status = "Fulfilled";
                $orderDetail->fulfilled_at = now();
                $orderDetail->save();
                $fulfilledOrderDetailIds[] = $orderDetail->id;
            } else if ($item['quantity'] < $item['max'] && $item['quantity'] > 0) {
                /* Declare variable */
                $totalOri = $item['unit_price'] * $item['quantity'];
                $unitWeight = $item['weight'] / $item['max'];
                $difference = $item['max'] - $item['quantity'];
                $total = $item['unit_price'] * $difference;
                /** Saving Process **/
                $orderDetail->fulfillment_status = "Unfulfilled";
                $orderDetail->quantity = $difference;
                $orderDetail->weight = $unitWeight * $difference;
                $orderDetail->order_number = "";
                $orderDetail->total = $total;
                $orderDetail->save();

                $newOrderDetail = $orderDetail->replicate();
                $newOrderDetail->parent_id = $orderDetail->id;
                $newOrderDetail->order_number = "#" . $order_number . "-F" . $currentOrder->maximum_indicator;
                $newOrderDetail->fulfilled_at = now();
                $newOrderDetail->fulfillment_status = "Fulfilled";
                $newOrderDetail->quantity = $item['quantity'];
                $newOrderDetail->weight = $unitWeight * $item['quantity'];
                $newOrderDetail->total = $totalOri;
                $newOrderDetail->save();
                $fulfilledOrderDetailIds[] = $newOrderDetail->id;
            }
        };

        $totalProduct = OrderDetail::where('order_id', $request->orderID)->count();
        $fulfillProduct = OrderDetail::where('order_id', $request->orderID)->where('fulfillment_status', "Fulfilled")->count();
        $unfulfillProduct = OrderDetail::where('order_id', $request->orderID)->where('fulfillment_status', "Unfulfilled")->count();
        $removedProduct = OrderDetail::where('order_id', $request->orderID)->where('fulfillment_status', "Removed")->count();
        if ($totalProduct - $removedProduct == $fulfillProduct) {
            $currentOrder->fulfillment_status = "Fulfilled";
            $currentOrder->additional_status = "Closed";
        } else if ($totalProduct - $removedProduct == $unfulfillProduct) {
            $currentOrder->fulfillment_status = "Unfulfilled";
            $currentOrder->additional_status = "Open";
        } else {
            $currentOrder->fulfillment_status = "Partially Fulfilled";
            $currentOrder->additional_status = "Open";
        }
        $currentOrder->save();

        $account = Auth::user()->currentAccount();
        $notifiableSetting = $account->notifiableSetting;
        if ($notifiableSetting->is_fulfill_order_notifiable) {
            if (app()->environment() !== 'local') {
                $data = [
                    'fulfillmentNumber' => "#" . $order_number . "-F" . $currentOrder->maximum_indicator,
                    'courierInfo' => $trackingInfo,
                    'fulfillOrderDetails' => OrderDetail::find($fulfilledOrderDetailIds),
                    'accountId' => $account->id,
                    'orderId' => $currentOrder->id,

                ];
                if ($currentOrder->processedContact && !is_null($currentOrder->processedContact->email)) {
                    Mail::to($currentOrder->processedContact->email)->send(new OrderShippedEmail($data));
                }
            }
        }
    }

    /**
     * @param $reference_key
     * @param \Illuminate\Http\Request $request
     * @param \App\Services\EasyParcelAPIService $easyParcelAPIService
     * @return \Illuminate\Http\Response
     * @throws \Throwable
     */
    public function fulfillOrderWithEasyParcel($reference_key, Request $request, EasyParcelAPIService $easyParcelAPIService): \Illuminate\Http\Response
    {
        $request->validate([
            'unfulfilledOrderDetails' => 'required|array',
            'courier' => 'required|array',
            'unsettledCourier' => 'nullable|array'
        ]);

        $currentAccountId = $this->getCurrentAccountId();
        $order = Order
            ::where('account_id', $currentAccountId)
            ->where('reference_key', $reference_key)
            ->firstOrFail();
        $courier = $request->courier;

        DB::beginTransaction();

        try {
            ++$order->maximum_indicator;

            // update some EasyParcel related info & pricing
            $order->shipping_method_name = 'EasyParcel';
            $order->shipping_method = $courier['courier_name'];
            $order->total = $order->total - $order->shipping + $courier['price'];
            $order->shipping = $courier['price'];
            $order->save();

            $fulfilledOrderDetailIds = $this->fulfillOrderDetails($request->unfulfilledOrderDetails);

            $unfulfilledOD = OrderDetail
                ::where('order_id', $order->id)
                ->where('fulfillment_status', "Unfulfilled")
                ->get();

            if ($unfulfilledOD->isEmpty()) {
                $order->fulfillment_status = "Fulfilled";
                $order->additional_status = "Closed";
            } else {
                $order->fulfillment_status = "Partially Fulfilled";
                $order->additional_status = "Open";
            }
            $order->save();

            $unsettledEasyParcel = $request->unsettledCourier;

            // if unsettled easy parcel present and selected courier service_id !== unsettled one,
            // means that unsettled easy parcel has been abandoned/reset
            if (
                $unsettledEasyParcel &&
                $unsettledEasyParcel['service_id'] !== $courier['service_id']
            ) {
                EasyParcelShipment::where('service_id', $unsettledEasyParcel['service_id'])->delete();
            }

            $easyParcelShipment = EasyParcelShipment::firstOrCreate(
                [
                    'order_id' => $order->id,
                    'service_id' => $courier['service_id']
                ],
                $courier
            );

            foreach ($fulfilledOrderDetailIds as $id) {
                EasyParcelFulfillment::updateOrCreate(
                    [
                        'order_detail_id' => $id
                    ],
                    [
                        'easy_parcel_shipment_id' => $easyParcelShipment->id,
                    ]
                );
            }

            $newEasyParcelShipment = $this->makeEasyParcelOrder($easyParcelShipment, $fulfilledOrderDetailIds, $easyParcelAPIService);
            $this->makeEasyParcelOrderPayment($newEasyParcelShipment, $easyParcelAPIService);

            DB::commit();
        } catch (\Throwable $th) {
            \Log::error('Failed to fulfill order with EasyParcel', [
                'msg' => $th->getMessage(),
                'order' => $order->id
            ]);
            DB::rollBack();
            throw new \RuntimeException('Failed to fulfill order with EasyParcel. Please contact support');
        }

        return response()->noContent();
    }

    /**
     * @param $reference_key
     * @param Request $request
     * @param LalamoveAPIService $lalamoveAPIService
     * @return \Illuminate\Http\JsonResponse
     * @throws \JsonException|\Throwable
     */
    public function fulfillOrderWithLalamove(
        $reference_key,
        Request $request,
        LalamoveAPIService $lalamoveAPIService
    ): \Illuminate\Http\JsonResponse {
        $request->validate([
            'unfulfilledOrderDetails' => 'required|array',
            'quotation' => 'required|array',
            'scheduledAt' => 'nullable',
            'serviceType' => 'required|string',
            'unsettledQuotation' => 'nullable|array'
        ]);

        $currentAccountId = $this->getCurrentAccountId();
        $order = Order
            ::with('lalamoveQuotations')
            ->where('account_id', $currentAccountId)
            ->where('reference_key', $reference_key)
            ->firstOrFail();
        $quotation  = $request->quotation;
        $shippingAmount = $quotation['totalFee'];
        $shippingCurrency = $quotation['totalFeeCurrency'];
        $serviceType = $request->serviceType;
        $senderLocation = Location::first();

        if (!$senderLocation) {
            return response()->json([
                'message' => 'Sender location is empty'
            ], 409);
        }

        DB::beginTransaction();

        try {
            ++$order->maximum_indicator;

            $order->shipping_method_name = 'Lalamove';
            $order->shipping_method = 'Lalamove - ' . $serviceType;
            $order->total = $order->total - $order->shipping + (float)$shippingAmount;
            $order->shipping = (float)$shippingAmount;

            $fulfilledOrderDetailIds = $this->fulfillOrderDetails($request->unfulfilledOrderDetails);

            $unfulfilledOD = OrderDetail
                ::where('order_id', $order->id)
                ->where('fulfillment_status', "Unfulfilled")
                ->get();

            if ($unfulfilledOD->isEmpty()) {
                $order->fulfillment_status = "Fulfilled";
                $order->additional_status = "Closed";
            } else {
                $order->fulfillment_status = "Partially Fulfilled";
                $order->additional_status = "Open";
            }

            $order->save();

            $unsettledQuotation = $request->unsettledQuotation;

            if ($unsettledQuotation && $unsettledQuotation['id'] !== $quotation['id']) {
                LalamoveQuotation::find($unsettledQuotation['id'])->delete();
            }

            $lalamoveQuotation = LalamoveQuotation::firstOrCreate(
                [
                    'id' => $unsettledQuotation['id'] ?? null
                ],
                [
                    'order_id' => $order->id,
                    'scheduled_at' => $request->scheduledAt,
                    'service_type' => $serviceType,
                    'stops' => $lalamoveAPIService->makeStops(
                        $senderLocation->displayAddr,
                        $order->displayShippingAddr
                    ),
                    'deliveries' => $lalamoveAPIService->makeDeliveries(
                        $order->shipping_name,
                        $order->shipping_phoneNumber
                    ),
                    'requester_contacts' => $lalamoveAPIService->makeRequesterContact($senderLocation),
                    'special_requests' => [],
                    'total_fee_amount' => $shippingAmount,
                    'total_fee_currency' => $shippingCurrency
                ]
            );

            foreach ($fulfilledOrderDetailIds as $id) {
                LalamoveFulfillment::updateOrCreate(
                    [
                        'order_detail_id' => $id
                    ],
                    [
                        'lalamove_quotation_id' => $lalamoveQuotation->id,
                    ]
                );
            }

            // external API
            $this->placeLalamoveOrder($lalamoveQuotation, $lalamoveAPIService);

            DB::commit();
        } catch (exception $e) {
            abort($e->getCode(), $e->getMessage());
            DB::rollBack();
        }

        return response()->json([
            'order_id' => $order->reference_key
        ]);
    }

    /**
     * @param \App\EasyParcelShipment $easyParcelShipment
     * @param array $orderDetailIds
     * @param \App\Services\EasyParcelAPIService $easyParcelAPIService
     * @return \App\EasyParcelShipment
     */
    private function makeEasyParcelOrder(
        EasyParcelShipment $easyParcelShipment,
        array $orderDetailIds,
        EasyParcelAPIService $easyParcelAPIService
    ): EasyParcelShipment {
        // make order in EasyParcel if order is fully fulfilled and it's using EasyParcel shipping
        $easyParcelRes = $easyParcelAPIService->makeOrder($easyParcelShipment, $orderDetailIds, []);

        if (isset($json['api_status']) && $json['api_status'] !== 'Success') {
            throw new \RuntimeException($json['error_remark']);
        }

        $resResultData = $easyParcelRes['result'][0] ?? [];

        if ($resResultData['status'] === 'fail') {
            throw new \RuntimeException($resResultData['remark']);
        }

        $easyParcelShipment->update(['order_number' => $resResultData['order_number']]);

        EasyParcelShipmentParcel::firstOrCreate(
            ['easy_parcel_shipment_id' => $easyParcelShipment->id],
            [
                'parcel_number' => $resResultData['parcel_number']
            ]
        );

        return $easyParcelShipment;
    }

    private function makeEasyParcelOrderPayment($easyParcelShipment, EasyParcelAPIService $easyParcelAPIService): void
    {
        // make order payment
        $paymentRes = $easyParcelAPIService->makeOrderPayment($easyParcelShipment);

        // handle general error
        if ($paymentRes['api_status'] !== 'Success') {
            throw new \RuntimeException($paymentRes['error_remark']);
        }

        $paymentResult = $paymentRes['result'][0];

        // here comes the interesting part about EasyParcel order payment:
        //
        // There's one important message we want in success payment messagenow: Insufficient Credit,
        // Weird right, why is it success? We'd want to show to user this message as
        // a warning/error instead of success (in order page).
        $easyParcelShipment->update([
            'order_status' => $paymentResult['messagenow'] ?? '',
        ]);

        // parcel array will be empty if "Insufficient Credit" case occurs,
        $easyParcelShipment->easyParcelShipmentParcel()->update([
            'tracking_url' => $paymentResult['parcel'][0]['tracking_url'] ?? null,
            'awb' => $paymentResult['parcel'][0]['awb'] ?? null,
            'awb_id_link' => $paymentResult['parcel'][0]['awb_id_link'] ?? null,
        ]);
    }

    /**
     * Place a delivery order using Lalamove API
     *
     * @param LalamoveQuotation $lalamoveQuotation
     * @param LalamoveAPIService $lalamoveAPIService
     * @throws \JsonException
     */
    private function placeLalamoveOrder($lalamoveQuotation, LalamoveAPIService $lalamoveAPIService)
    {
        // place order and create lalamove delivery order
        try {
            $lalamoveOrderCreated = $lalamoveAPIService->placeOrder($lalamoveQuotation);
        } catch (ClientException $e) {
            // for all available code and its msg, kindly refer to
            // https://developers.lalamove.com/?plaintext--sandbox#place-order
            $resCode = $e->getCode();
            $resBody = json_decode($e->getResponse()->getBody(), true, 512, JSON_THROW_ON_ERROR);
            $message = $resBody['message'];
            $order = $lalamoveQuotation->order;

            if ($message === 'ERR_INSUFFICIENT_CREDIT') {
                abort($resCode, 'Your Lalamove account has insufficient credit. Please top up your wallet.');
            }

            if ($message === 'ERR_INVALID_CURRENCY') {
                \Log::error('Invalid currency when placing Lalamove order.', [
                    'order_id' => $order->id
                ]);

                abort($resCode, 'Invalid currency. Please contact our support team.');
            }

            if ($message === 'ERR_PRICE_MISMATCH') {
                abort($resCode, 'The amount or currency provided doesn\'t match quotation.');
            }

            if ($message === 'ERR_INVALID_SCHEDULE_TIME') {
                abort($resCode, 'Invalid schedule time format. Please contact our support team.');
            }

            if ($resCode === 429) {
                abort($resCode, 'Too many attempts. Please wait for awhile before fulfilling again.');
            }

            abort(500, 'Unknown error');
        } catch (\JsonException $e) {
            \Log::error('JSON exception when placing Lalamove order', [
                'order_id' => $lalamoveQuotation->order->id
            ]);

            abort(500, 'Unexpected error on JSON decoding. Please contact our support team.');
        }

        // save entry in hypershapes db if Lalamove order is successfully placed
        $lalamoveDeliveryOrder = LalamoveDeliveryOrder::updateOrCreate(
            ['lalamove_quotation_id' => $lalamoveQuotation->id],
            [
                'customer_order_id' => $lalamoveOrderCreated['customerOrderId'] ?? null,
                'order_ref' => $lalamoveOrderCreated['orderRef']
            ]
        );

        // create an initial entry for lalamove delivery order details
        $lalamoveDeliveryOrder->lalamoveDeliveryOrderDetail()->create([
            'status' => 'ASSIGNING_DRIVER', // first status after placing order

            // temporary info only
            'price' => [
                'amount' => $lalamoveQuotation->total_fee_amount,
                'currency' => $lalamoveQuotation->total_fee_currency
            ]
        ]);
    }

    public function cancelFulfillment(Request $request, LalamoveAPIService $lalamoveAPIService)
    {
        $orderId = $request->orders['id'];
        $account = $this->currentAccount();
        $allOrderDetail = Order::where('id', $orderId)->where('account_id', $account->id)->first()->orderDetails;
        $order = Order
            ::where('account_id', $account->id)
            ->where('id', $orderId)
            ->first();

        //cancel delyva order
        $hasDelyva = DB::table('delyva_quotations')
            ->where('order_id', $order->id)
            ->where('order_number', $request->order_number)
            ->join('delyva_delivery_orders', 'delyva_delivery_orders.delyva_quotation_id', '=', 'delyva_quotations.id')
            ->join('delyva_delivery_order_details', 'delyva_delivery_order_details.delyva_delivery_order_id', '=', 'delyva_delivery_orders.id')
            ->get();
        $orderNumber = $request->items[0]['order_number'];
        if (!$hasDelyva->isEmpty()) {
            try {
                $delyvaQuotation = DelyvaQuotation::where('order_id', $order->id)->first();
                $delyvaDeliveryOrder = DelyvaDeliveryOrder::where('delyva_quotation_id', $delyvaQuotation->id)
                    ->where('order_number', $orderNumber)
                    ->first();
                $delyvaInfo = Delyva::firstwhere([
                    'account_id' => $account->id,
                ]);
                $delyvaOrderInfo = Http::baseUrl('https://api.delyva.app/v1.0/')
                    ->withHeaders([
                        'X-Delyvax-Access-Token' => $delyvaInfo->delyva_api,
                    ])
                    ->get("/order/{$delyvaDeliveryOrder->delyva_order_id}");
                $delyvaOrderInfoDetail = DelyvaDeliveryOrderDetail::where('delyva_delivery_order_id', $delyvaDeliveryOrder->id)
                    ->update([
                        'consignmentNo' => $delyvaOrderInfo['data']['consignmentNo'],
                        'statusCode' => $delyvaOrderInfo['data']['statusCode'],
                        'status' => $delyvaOrderInfo['data']['status'],
                        'invoiceId' => $delyvaOrderInfo['data']['invoiceId'],
                    ]);
                //delete delyva order
                if (
                    $delyvaOrderInfo['data']['statusCode'] >= 0
                    && $delyvaOrderInfo['data']['statusCode'] <= 110
                    || $delyvaOrderInfo['data']['statusCode'] <= 900
                ) {
                    echo ($delyvaDeliveryOrder->delyva_order_id);
                    $response = Http::baseUrl('https://api.delyva.app/')
                        ->withHeaders([
                            'X-Delyvax-Access-Token' => $delyvaInfo->delyva_api,
                        ])
                        ->post("order/{$delyvaDeliveryOrder->delyva_order_id}/cancel", ["" => ""]);
                    $DelyvaOrderDelete = DelyvaDeliveryOrder::findOrFail($delyvaDeliveryOrder->id)
                        ->delete();
                } else {
                    abort(409, 'Delyva service cant be cancel, please go to check the order status');
                }
            } catch (Exception $e) {
                abort(500, 'Unexpected error to cancel Delyva' . $e->getMessage());
            }
        }

        // try cancel lalamove order first if present
        $lalamoveDeliveryOrders = $order->lalamoveDeliveryOrders;
        foreach ($lalamoveDeliveryOrders as $lalamoveDeliveryOrder) {
            if ($lalamoveDeliveryOrder->lalamoveDeliveryOrderDetail->status === 'CANCELED') {
                continue;
            }

            $lalamoveOrderRef = $lalamoveDeliveryOrder->order_ref;

            try {
                $lalamoveAPIService->cancelOrder(
                    $lalamoveOrderRef,
                    $order->account_id
                );
            } catch (ClientException $e) {
                $resCode = $e->getCode();

                if ($resCode === 404) {
                    abort(404, 'Cannot cancel Lalamove delivery. Lalamove order ref: ' . $lalamoveOrderRef);
                }

                $resBody = json_decode($e->getResponse()->getBody(), true, 512, JSON_THROW_ON_ERROR);
                $msg = $resBody['message'];

                if ($msg === 'ERR_CANCELLATION_FORBIDDEN') {
                    conflictAbort('Lalamove refuse to cancel this order with ref ' . $lalamoveOrderRef);
                }

                conflictAbort('Unexpected HTTP exception ' . $e->getMessage());
            } catch (\JsonException $e) {
                conflictAbort('JSON decode exception. Please contact our support team');
            } catch (\Exception $e) {
                abort(500, 'Unexpected error ' . $e->getMessage());
            }
        }

        foreach ($request->items as $item) {
            $orderDetail = OrderDetail::find($item['id']);
            $unfulfillOrderDetail =  OrderDetail::where('order_id', $orderId)
                ->where('fulfillment_status', 'Unfulfilled')
                ->where('users_product_id', $item['users_product_id'])->get();
            if (count($unfulfillOrderDetail) > 0) {
                foreach ($unfulfillOrderDetail as $product) {
                    $result_variant = strcmp($product['variant'], $item['variant']);
                    $result_customization =  strcmp($product['customization'], $item['customization']);
                    if ($result_variant == 0 && $result_customization == 0) {
                        $product->quantity += $item['quantity'];
                        $product->weight += $item['weight'];
                        $orderDetail->quantity -= $item['quantity'];
                        $product->total = $product->quantity * $product->unit_price;
                        $product->order_number = $item['order_number'];
                        $product->save();
                    }
                }
            }
            $orderDetail->fulfillment_status = "Unfulfilled";
            $orderDetail->fulfilled_at = null;
            $orderDetail->tracking_courier_service = null;
            $orderDetail->tracking_number = null;
            $orderDetail->tracking_url = null;
            $orderDetail->save();
            if ($orderDetail->quantity == 0) {
                $orderDetail->delete();
            }
        }
        $fulfillOrder = OrderDetail::where('order_id', $orderId)->where('fulfillment_status', "Fulfilled")->get();
        $order = Order::where('account_id', $account->id)->where('id', $item['order_id'])->first();
        $order->fulfillment_status = $fulfillOrder->isEmpty() ? "Unfulfilled" : "Partially Fulfilled";
        $order->additional_status = "Open";
        $order->save();

        // ===============================================
        // For EasyParcel
        // ===============================================
        // Note that parcel that already created in EasyParcel website
        // can't be deleted. User has to manually cancel/delete themselves at
        // EasyParcel site.
        if ($order->easyParcelShipment) {
            $order->easyParcelShipment()->delete();
        }

        return response()->json([
            'order_id' => $request->orders['reference_key']
        ]);
    }
    public function markPayment(Request $request)
    {
        $order_id = $request->orders['id'];
        $timestamp = now();
        $paidOrder = Order::find($order_id);
        $paidOrderDetail = $paidOrder->orderDetails;
        $balance = (floatval($request->total)) - ($paidOrder->paided_by_customer);
        if ($paidOrder->payment_status == "" || $paidOrder->payment_status == "Unpaid") {
            $paidOrder->payment_status = "Paid";
        }
        $paidOrder->paided_by_customer += $balance;
        $paidOrder->paid_at = $timestamp;
        $paidOrder->save();
        $saveTransaction = new OrderTransactions();
        $saveTransaction->order_id = $order_id;
        $saveTransaction->transaction_key = $this->getRandomId('order_transactions', 'transaction_key');
        $saveTransaction->total = $balance;
        $saveTransaction->paid_at = $timestamp;
        $saveTransaction->payment_status = "Paid";
        $saveTransaction->save();
        if ($request->orders['processed_contact_id'] != '') {
            $processedContact = ProcessedContact::find($request->orders['processed_contact_id']);
            $processedContact->totalSpent += $balance;
            $processedContact->save();
        }

        $isAllFulfilled = true;
        foreach ($paidOrderDetail as $value) {
            $value->payment_status = "Paid";
            $value->paid_at = $timestamp;
            if ($value->is_virtual) {
                $value->fulfillment_status = CheckoutOrderService::ORDER_FULFILLED_STATUS;
                $value->order_number = "#" . $paidOrder->order_number . "-F" . ($paidOrder->maximum_indicator ?? 1);
            }
            $value->save();
            $isAllFulfilled = $isAllFulfilled && ($value->fulfillment_status === CheckoutOrderService::ORDER_FULFILLED_STATUS);
        }
        if ($isAllFulfilled) {
            $paidOrder->additional_status = "Closed";
            $paidOrder->fulfillment_status = CheckoutOrderService::ORDER_FULFILLED_STATUS;
            $paidOrder->fulfilled_at = date('Y-m-d H:i:s');
        }
        $paidOrder->save();

        $processedContact = ProcessedContact::find($paidOrder->processed_contact_id);
        $paidOrder->name = $processedContact != null ? $processedContact->displayName : '';
        $paidOrder->email = $processedContact != null ? $processedContact->email : '';
        $paidOrder->phoneNo = $processedContact != null ? $processedContact->phone_number : '';
        $paidOrder->fname =  $processedContact != null ? $processedContact->fname : '';
        $paidOrder->lname = $processedContact != null ? $processedContact->lname : '';

        $cashback = json_decode($paidOrder->cashback_detail ?? '[]', true);
        if (isset($cashback['cashbackDetail'])) {
            $cashbackService = new CashbackService($processedContact);
            $cashbackService->recordCashbackEarned($paidOrder);
        }
        return response($paidOrder);
    }

    public function updateAdditionalStatus(Request $request)
    {
        $order = Order::find($request->orderId);
        $order->additional_status = ($request->type == 'archive') ? "Archived" : "Open";
        $order->save();
        $processedContact = ProcessedContact::find($order->processed_contact_id);
        $order->name = $processedContact != null ? $processedContact->displayName : '';
        $order->email = $processedContact != null ? $processedContact->email : '';
        $order->phoneNo = $processedContact != null ? $processedContact->phone_number : '';
        $order->fname =  $processedContact != null ? $processedContact->fname : '';
        $order->lname = $processedContact != null ? $processedContact->lname : '';
        $order->processedRandomId = $processedContact != null ? $processedContact->contactRandomId : '';
        return response($order);
    }

    public function saveShippingAddress(Request $request)
    {
        // Validator::make($request->address, [
        //     'fullname' => ['required'],
        //     'address' => ['required'],
        //     'city' => ['required'],
        //     'zip' => 'required',
        //     'state' => 'required',
        //     'country' =>'required',
        //     'phoneNo' => 'required'
        // ],[
        //     'required'=>'Field is required'
        // ])->validate();
        $currentAccountId = $this->getCurrentAccountId();
        $order = Order::where('account_id', $currentAccountId)->where('id', $request->orders['id'])->first();
        $order->shipping_company_name = $request->address['company'];
        $order->shipping_name = $request->address['fullname'];
        $order->shipping_address = $request->address['address'];
        $order->shipping_city = $request->address['city'];
        $order->shipping_zipcode = $request->address['zip'];
        $order->shipping_state = $request->address['state'];
        $order->shipping_country = $request->address['country'];
        $order->shipping_phoneNumber = $request->address['phoneNo'];
        $order->save();
        $processedContact = ProcessedContact::find($order->processed_contact_id);
        $order->name = $processedContact != null ? $processedContact->displayName : '';
        $order->email = $processedContact != null ? $processedContact->email : '';
        $order->phoneNo = $processedContact != null ? $processedContact->phone_number : '';
        $order->fname =  $processedContact != null ? $processedContact->fname : '';
        $order->lname = $processedContact != null ? $processedContact->lname : '';
        $order->processedRandomId = $processedContact != null ? $processedContact->contactRandomId : '';
        return response($order);
    }

    public function saveBillingAddress(Request $request)
    {
        $currentAccountId = $this->getCurrentAccountId();
        $order = Order::where('account_id', $currentAccountId)->where('id', $request->orders['id'])->first();
        $order->billing_company_name = $request->address['company'];
        $order->billing_name = $request->address['fullname'];
        $order->billing_address = $request->address['address'];
        $order->billing_city = $request->address['city'];
        $order->billing_zipcode = $request->address['zip'];
        $order->billing_state = $request->address['state'];
        $order->billing_country = $request->address['country'];
        $order->billing_phoneNumber = $request->address['phoneNo'];
        $order->save();
        $processedContact = ProcessedContact::find($order->processed_contact_id);
        $order->name = $processedContact != null ? $processedContact->displayName : '';
        $order->email = $processedContact != null ? $processedContact->email : '';
        $order->phoneNo = $processedContact != null ? $processedContact->phone_number : '';
        $order->fname =  $processedContact != null ? $processedContact->fname : '';
        $order->lname = $processedContact != null ? $processedContact->lname : '';
        $order->processedRandomId = $processedContact != null ? $processedContact->contactRandomId : '';
        return response($order);
    }

    public function saveContactInfo(Request $request)
    {
        $account = $this->currentAccount();
        $order = Order::find($request->orderId);
        $processedContactFromOrder = ProcessedContact::find($order->processed_contact_id);
        if (count($request->customer) > 0 && $request->customer['email'] != '') {
            $processedContact = ProcessedContact::where('account_id', $account->id)->firstOrNew(['email' => $request->customer['email']]);
            $processedContact->fname = $request->customer['fname'];
            $processedContact->lname = $request->customer['lname'];
            $processedContact->phone_number = $request->customer['phoneNo'];

            if ($processedContact->exists == false) {
                $processedContact->contactRandomId = $this->getRandomId('processed_contacts', 'contactRandomId');
                $processedContact->account_id = $account->id;
                $processedContact->acquisition_channel = "Manually create order";
            }
            $allPCOrder = Order::where('account_id', $account->id)->where('processed_contact_id', $processedContact->id)->get();
            $processedContact->ordersCount = $allPCOrder->count();
            $processedContact->totalSpent = $allPCOrder->sum('paided_by_customer');
            $processedContact->save();
            $order->processed_contact_id = $processedContact->id;
            $order->save();

            if ($processedContactFromOrder !== null) {
                $allEPCOrder = Order::where('account_id', $account->id)->where('processed_contact_id', $processedContactFromOrder->id)->get();
                $processedContactFromOrder->ordersCount = $allEPCOrder->count();
                $processedContactFromOrder->totalSpent = $allEPCOrder->sum('paided_by_customer');
                $processedContactFromOrder->save();
            }

            ProcessedAddress::updateOrCreate(['processed_contact_id' => $processedContact->id]);
            $order->name = $processedContact->displayName;
            $order->email = $processedContact->email;
            $order->phoneNo = $processedContact->phone_number;
            $order->fname =  $processedContact->fname;
            $order->lname = $processedContact->lname;
        }
        return response($order);
    }

    public function saveNote(Request $request)
    {
        $order = Order::find($request->orderId);
        $processedContact = ProcessedContact::find($order->processed_contact_id);
        $order->notes = $request->notes;
        $order->save();
        $order->name = $processedContact != null ? $processedContact->displayName : '';
        $order->email = $processedContact != null ? $processedContact->email : '';
        $order->phoneNo = $processedContact != null ? $processedContact->phone_number : '';
        $order->fname =  $processedContact != null ? $processedContact->fname : '';
        $order->lname = $processedContact != null ? $processedContact->lname : '';
        $order->processedRandomId = $processedContact != null ? $processedContact->contactRandomId : '';

        return response($order);
    }

    public function makeRefund($reference_key)
    {

        $currentAccountId = $this->getCurrentAccountId();
        $currency = Account::find($currentAccountId)->currency;
        $order = Order::where('reference_key', $reference_key)->where('account_id', $currentAccountId)->first();
        $order->currency = ($order->currency == 'MYR') ? 'RM' : $order->currency;
        $orderDetail = OrderDetail::where('order_id', $order->id)->where('payment_status', 'Paid')->get();
        $transactionLog = OrderTransactions::where('order_id', $order->id)->where('payment_status', '!=', 'Refunded')->get();
        return Inertia::render('order/pages/MakeRefund', compact('order', 'orderDetail', 'currency', 'transactionLog'));
    }

    public function orderRefund(Request $request)
    {

        $currentAccountId = $this->getCurrentAccountId();
        $order_id = $request->orderJs['id'];
        $newRefundLog = new OrderRefundLog();
        $order = Order::where('account_id', $currentAccountId)->where('id', $order_id)->first();
        $processedContact = ProcessedContact::where('account_id', $currentAccountId)->find($request->orderJs['processed_contact_id']);

        foreach ($request->transactionLogJs as $transactionLog) {
            $orderTransaction = OrderTransactions::where('order_id', $order_id)->where('transaction_key', $transactionLog['transaction_key'])->first();
            $balance = $orderTransaction->total - $transactionLog['refundTotal'];
            if ($transactionLog['refundTotal'] > 0) {
                if ($transactionLog['refundTotal'] == $orderTransaction->total) {
                    $orderTransaction->payment_status = "Refunded";
                    $orderTransaction->refundTotal += $transactionLog['refundTotal'];
                } else if ($transactionLog['refundTotal'] < $orderTransaction->total) {
                    $orderTransaction->payment_status = "Partially Refunded";
                    $orderTransaction->refundTotal = $transactionLog['refundTotal'];
                }
                $orderTransaction->total = $balance;
                $orderTransaction->save();
            }
        }

        $newRefundLog->order_id = $order_id;
        $newRefundLog->refundAmount = $request->calculateRefund;
        $newRefundLog->refunded_reason = $request->refundReason;
        $newRefundLog->save();
        $order->refunded += $request->calculateRefund;
        $order->refund_shipping += $request->refundShippingFee;
        $order->payment_status = (($order->paided_by_customer - $order->refunded) == 0) ? "Refunded" : "Partially Refunded";
        $order->save();

        if (count($request->removedProduct) > 0) {
            foreach ($request->removedProduct as $removedProduct) {
                $orderDetail = OrderDetail::find($removedProduct['id']);
                $orderDetail->quantity -= $removedProduct['quantity'];
                $orderDetail->total = $orderDetail->quantity * $orderDetail->unit_price;
                $orderDetail->save();
                $removedProductsInOrder = OrderDetail::where('order_id', $order_id)
                    ->where('fulfillment_status', 'Removed')
                    ->where('users_product_id', $removedProduct['users_product_id'])->get();
                if (count($removedProductsInOrder) > 0) {
                    foreach ($removedProductsInOrder as $product) {
                        $variant = json_decode($product['variant'], TRUE);
                        $customization = json_decode($product['customization'], TRUE);
                        $result_variant = array_diff($variant, json_decode($removedProduct['variant']));
                        $result_customization =  array_diff($customization, json_decode($removedProduct['customization']));

                        if (empty($result_variant) && empty($result_customization)) {
                            $product->quantity += $removedProduct['quantity'];
                            $product->total = $product->quantity * $product->unit_price;
                            $product->save();
                        }
                    }
                } else {
                    $newOrderDetail = $orderDetail->replicate();
                    $newOrderDetail->quantity = $removedProduct['quantity'];
                    $newOrderDetail->fulfillment_status = "Removed";
                    $newOrderDetail->payment_status = "Refunded";
                    $newOrderDetail->parent_id = $orderDetail->id;
                    $newOrderDetail->total = $newOrderDetail->quantity * $newOrderDetail->unit_price;
                    $newOrderDetail->save();
                }
                if ($orderDetail->quantity == 0) {
                    $orderDetail->delete();
                }
            }
        }
    }

    private function fulfillOrderDetails(array $unfulfilledOrderDetails): array
    {
        $fulfilledOrderDetailIds = [];

        foreach ($unfulfilledOrderDetails as $unfulfilledOD) {
            $orderDetail = OrderDetail::find($unfulfilledOD['id']);
            $order = $orderDetail->order;
            $order_number = $order->order_number;

            // full fulfillment
            if ($unfulfilledOD['quantity'] === $unfulfilledOD['max']) {
                $orderDetail->order_number = "#" . $order_number . "-F" . $order->maximum_indicator;
                $orderDetail->fulfillment_status = "Fulfilled";
                $orderDetail->fulfilled_at = now();
                $orderDetail->save();
                $fulfilledOrderDetailIds[] = $orderDetail->id;
            }

            // partial fulfillment
            if (
                $unfulfilledOD['quantity'] < $unfulfilledOD['max']
                && $unfulfilledOD['quantity'] > 0
            ) {
                $totalOri = $unfulfilledOD['unit_price'] * $unfulfilledOD['quantity'];
                $unitWeight = $unfulfilledOD['weight'] / $unfulfilledOD['max'];
                $difference = $unfulfilledOD['max'] - $unfulfilledOD['quantity'];
                $total = $unfulfilledOD['unit_price'] * $difference;

                $orderDetail->fulfillment_status = "Unfulfilled";
                $orderDetail->quantity = $difference;
                $orderDetail->weight = $unitWeight * $difference;
                $orderDetail->order_number = "";
                $orderDetail->total = $total;
                $orderDetail->save();

                $newOrderDetail = $orderDetail->replicate();
                $newOrderDetail->parent_id = $orderDetail->id;
                $newOrderDetail->order_number = "#" . $order_number . "-F" . $order->maximum_indicator;
                $newOrderDetail->fulfilled_at = now();
                $newOrderDetail->fulfillment_status = "Fulfilled";
                $newOrderDetail->quantity = $unfulfilledOD['quantity'];
                $newOrderDetail->weight = $unitWeight * $unfulfilledOD['quantity'];
                $newOrderDetail->total = $totalOri;
                $newOrderDetail->save();
                $fulfilledOrderDetailIds[] = $newOrderDetail->id;
            }
        };

        return $fulfilledOrderDetailIds;
    }

    /**
     * @deprecated
     *
     * @param $reference_key
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response|void
     */
    public function saveEasyParcelTracking($reference_key, Request $request)
    {
        $order = Order
            ::with('easyParcelShipmentParcels')
            ->where('account_id', $this->getCurrentAccountId())
            ->where('reference_key', $reference_key)
            ->firstOrFail();

        $order->easyParcelShipmentParcels()->update($request->all());

        return response()->noContent();
    }

    public function printPackingSlip($reference_key)
    {

        $currentAccountId = $this->getCurrentAccountId();

        if (Order::where(['reference_key' => $reference_key, 'account_id' => $currentAccountId])->exists()) {
            $account = Account::where('id', $currentAccountId)->first();
            $processedContact = ProcessedContact::where('account_id', $currentAccountId)->get();
            $orders = Order::where('orders.reference_key', $reference_key)->where('orders.account_id', $currentAccountId)->first();

            $accountTimeZone = Account::find($currentAccountId)->timeZone;
            $customer = $processedContact->find($orders['processed_contact_id']);

            if ($customer == null) {
                $orders->name = "No customer";
                $orders->email = "";
                $orders->mobileNo = "";
                $orders->fname = "";
                $orders->lname = "";
            } else {
                $orders->name = $customer->displayName;
                $orders->email = $customer->email;
                $orders->mobileNo = $customer->phone_number;
                $orders->fname = $customer->fname;
                $orders->lname = $customer->lname;
            }

            $orderTime = new Carbon($orders->created_at);
            $orders->convertTime = $orderTime->timezone($accountTimeZone)->format('d/m/Y,h:i a');
            $orders->issueDate = now()->timezone($accountTimeZone)->format('d/m/Y,h:i a');

            $domain = (AccountDomain::where('account_id', $currentAccountId)->first())->domain;
            $unfulfilledOrderDetails = OrderDetail::where('order_id', $orders->id)
                ->where('fulfillment_status', 'Unfulfilled')
                ->get();

            $fulfilledOrderDetails = OrderDetail::where('order_id', $orders->id)
                ->where('fulfillment_status', 'Fulfilled')
                ->get()
                ->groupBy('order_number');

            $removedOrderDetails = OrderDetail::where('order_id', $orders->id)
                ->where('fulfillment_status', 'Removed')
                ->get();

            $pdf = PDF::loadView('fulfillPackingSlip', compact('orders', 'fulfilledOrderDetails', 'unfulfilledOrderDetails', 'removedOrderDetails', 'account', 'domain'));

            return $pdf->stream('packing_slip_' . $reference_key . '.pdf');
        } else {
            return redirect('/order/details/' . $reference_key);
        }
    }

    public function findByPaymentRef(Request $request)
    {
        $order = Order::where('payment_references', $request->paymentRef)->first();
        if (!empty($request->paymentRef) && !isset($order)) abort(500, 'Order not found');

        $hasDelyva = false;
        $trackOrder = DB::table('delyva_quotations')
            ->where('order_id', $order->id)
            ->join('delyva_delivery_orders', 'delyva_delivery_orders.delyva_quotation_id', '=', 'delyva_quotations.id')
            ->join('delyva_delivery_order_details', 'delyva_delivery_order_details.delyva_delivery_order_id', '=', 'delyva_delivery_orders.id')
            ->get();

        $visitorId = $_COOKIE["visitor_id"] ?? null;
        $cartId = $_COOKIE["cart_id"] ?? null;

        $isDiffUser = false;

        if ($visitorId) {

            $processedContact = ProcessedContact::where([
                'account_id' => $order->account_id,
                'id' => $order->processed_contact_id
            ])->first();

            $visitor = EcommerceVisitor::firstWhere('reference_key', $visitorId);

            $conversionTrackingLog = EcommerceTrackingLog::where('visitor_id', $visitor->id)
                ->whereIn('type', ['form', 'order'])->get();


            //only can check whether it is place order (cannot check filled form)
            if (count($conversionTrackingLog) && $visitor->processed_contact_id !== $order->processed_contact_id && isset($visitor->processed_contact_id)) {

                $lastConversionActivity = $conversionTrackingLog[count($conversionTrackingLog) - 1];

                $oldVisitorId = $visitor->id;
                $trackingLogToBeMoved = EcommerceTrackingLog::where([
                    ['visitor_id', '=', $oldVisitorId],
                    ['id', '>', $lastConversionActivity->id],
                ])->get();

                // in case the customer checkout 2 times using different account
                $visitor = EcommerceVisitor::create(
                    [
                        'account_id' => $order->account_id,
                        'processed_contact_id' => $order->processed_contact_id,
                        'sales_channel' => $visitor->sales_channel,
                        'reference_key' => $this->getRandomId('ecommerce_visitors', 'reference_key'),
                    ]
                );
                $newCart = EcommerceAbandonedCart::create(
                    [
                        'visitor_id' => $visitor->id,
                        'product_detail' => null,
                        'status' => false,
                        'abandoned_at' => $this->convertDatetimeToSelectedTimezone(date('Y-m-d H:i:s')),
                        'reference_key' => $this->getRandomId('ecommerce_abandoned_carts', 'reference_key'),
                    ]
                );

                foreach ($trackingLogToBeMoved as $trackingLog) {
                    $trackingLog->visitor_id = $visitor->id;
                    $trackingLog->save();
                }

                $isDiffUser = true;
            }

            $trackingActivityData = new Request;
            $trackingActivityData->pageName = 'Checkout Success';
            $trackingActivityData->visitorRefKey = $visitor->reference_key;

            app('App\Http\Controllers\OnlineStoreTrackingController')->trackPageView($trackingActivityData);

            if (!(EcommerceTrackingLog::where([
                'type' => 'order',
                'value' => $order->id,
            ])->exists())) {
                $trackingConversionData = new Request;
                $trackingConversionData->pageName = $order->id;
                $trackingConversionData->pageType = 'order';
                $trackingConversionData->visitorRefKey = $visitor->reference_key;

                $storeOrderTracking = app('App\Http\Controllers\OnlineStoreTrackingController')->trackPageView($trackingConversionData);
            }

            $visitor->processed_contact_id = $order->processed_contact_id ?? null;

            $visitor->is_completed = true;
            $visitor->order_id = $order->id;
            $visitor->save();
        }

        if ($cartId) {
            $cart = EcommerceAbandonedCart::firstWhere('reference_key', $cartId);

            $cart->product_detail = null;
            $cart->status = false;

            $cart->save();
        }

        if (!$trackOrder->isEmpty())
            $hasDelyva = true;

        return response()->json([
            'order' => $order,
            'sellerEmail' => $order->sellerEmail(),
            'orderDetail' => OrderDetail::where('order_id', $order['id'])->get(),
            'hasDelyva' => $hasDelyva,
            'delyvaDetail' => $trackOrder ?? "",
            'visitorId' => $isDiffUser ? $visitor->reference_key : null,
            'cartId' => $isDiffUser ? $newCart->reference_key : null,
        ]);
    }

    private function getAllOrderProducts()
    {

        return $this->getAllActiveProducts($this->getCurrentAccountId())->map(function ($item) {
            ProductVariant::where('product_id', $item->id)->each(function ($pv) use ($item) {
                $variant = Variant::find($pv->variant_id);
                $item->variant_values[] = (object)[
                    'id' => $variant->id,
                    'name' => $variant->variant_name,
                    'display_name' => $variant->display_name,
                    'is_shared' => $variant->is_shared,
                    'type' => $variant->type,
                    'valueArray' => $variant->values
                ];
            });
            $item->variant_details->map(function ($variant) {
                $variant->title = $this->variantCombinationName($variant);
                return $variant;
            });
            return $item;
        });
    }

    public function viewCreateOrder($reference_key = null)
    {
        $isEdit = !empty($reference_key);
        $currentAccountId = $this->getCurrentAccountId();
        $order = Order::firstWhere('reference_key', $reference_key);

        // TODO: Write as tinker script for old order
        // TODO: Update capped at of order discount for checkout
        // $orderDiscount = $order->orderDiscount()->where(['promotion_category' => 'Order', 'discount_type' => 'percentage'])->whereNotNull('discount_code')->first();
        // if (isset($orderDiscount)) {
        //     $promotion = Promotion::firstWhere('discount_code', $orderDiscount->discount_code);
        //     $orderDiscount->discount_capped_at = $promotion->promotionType->order_discount_cap ?? null;
        //     $orderDiscount->save();
        // }

        $allProducts = $this->getAllOrderProducts();
        $processedContact = ProcessedContact::where('account_id', $currentAccountId)->get();
        $selectedProcessedContact = $processedContact->find(optional($order)->processed_contact_id);
        $currency = Account::find($currentAccountId)->currency;
        $currency = ($currency == 'MYR') ? 'RM' : $currency;
        $orderReferenceKey = $reference_key;
        return Inertia::render('order/pages/CreateOrder', compact(
            'isEdit',
            'order',
            'allProducts',
            'processedContact',
            'selectedProcessedContact',
            'currency',
            'orderReferenceKey',
        ));
    }

    public function saveManualOrder(Request $request)
    {
        try {
            \DB::beginTransaction();

            $orderService = new OrderService($request);
            $processedContact = $orderService->updateOrCreateProcessedContact();
            $order = $orderService->updateOrCreateOrder($processedContact);
            $orderService->updateOrCreateOrderDetail($order);
            $orderService->updateOrCreateOrderDiscount($order);

            $orderService->updateProductInventory($order);

            if ($request->isPaid) {
                $orderService->markAsPaid($order);
            }

            if ($order->payment_status === PaymentService::PAYMENT_PAID_STATUS && isset($processedContact)) {
                $orderService->updateOrCreateOrderTransaction($order);
                event(new OrderPlaced($order));
            }

            \DB::commit();
        } catch (\Throwable $th) {
            \DB::rollBack();
            Log::error($th->getMessage());
            throw new \RuntimeException('Failed to ' . ($request->isEdit ? 'update' : 'create') . ' order. Please try again later');
        }

        return response()->json([
            'orderRef' => $order->reference_key
        ]);
    }
}
