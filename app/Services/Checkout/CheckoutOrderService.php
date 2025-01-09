<?php

namespace App\Services\Checkout;

use Cookie;
use Mail;

use App\Account;
use App\EcommerceAbandonedCart;
use App\EcommerceVisitor;
use App\funnel;
use App\Page;
use App\Location;
use App\Order;
use App\OrderDetail;
use App\OrderSubscription;
use App\OrderTransactions;
use App\ProcessedContact;
use App\ProductSubscription;
use App\UsersProduct;
use App\VariantDetails;
use App\Models\Orders\OrderDiscount;
use App\Models\Promotion\Promotion;
use App\Models\Promotion\PromotionRedemptionLog;

use App\Mail\OrderPaymentBuyerEmail;
use App\Mail\OrderPaymentSellerEmail;

use App\Repository\CheckoutRepository;
use App\Repository\Checkout\CheckoutData;
use App\Services\AffiliateCookieService;
use App\Services\ProcessedContactService;
use App\Services\RefKeyService;
use App\Services\Checkout\CartService;
use App\Services\Checkout\FormServices;
use App\Traits\AffiliateMemberCommissionTrait;
use App\Traits\AuthAccountTrait;
use App\Traits\CurrencyConversionTraits;
use App\Traits\SalesChannelTrait;
use App\Traits\ReferralCampaignTrait;
use App\Traits\SegmentTrait;

use Illuminate\Http\Request;

class CheckoutOrderService
{
    use AuthAccountTrait, SalesChannelTrait, CurrencyConversionTraits, SegmentTrait, AffiliateMemberCommissionTrait, ReferralCampaignTrait;

    public const ORDER_FULFILLED_STATUS = 'Fulfilled';
    public const ORDER_UNFULFILLED_STATUS = 'Unfulfilled';
    public const AMOUNT_MULTIPLY_BY = 100;

    protected $formService, $cartService, $refKeyService, $taxService, $paymentService;
    public $formDetail, $paymentMethod, $grandTotal, $promotions;
    protected $accountId;
    public $isFunnel, $landingPage, $funnelParams;

    public function __construct()
    {
        new CheckoutData();

        $this->formService = new FormServices();
        $this->cartService = new CartService();
        $this->refKeyService = new RefKeyService();
        $this->taxService = new TaxService();
        $this->paymentService = new PaymentService();

        $this->accountId = $this->getCurrentAccountId();
        $this->formDetail = CheckoutData::$formDetail;
        $this->promotions = CheckoutRepository::$availablePromotions;
        $this->paymentMethod =  $this->paymentService->getSelectedPaymentMethod();
        $this->grandTotal = CheckoutRepository::$grandTotal;
    }

    public function setGrandTotal()
    {
        $this->grandTotal = CheckoutRepository::$grandTotal;
    }


    public function setFunnelCheckout($landingId, $params)
    {
        $this->landingPage = Page::find($landingId);
        $this->funnelParams = $params;
        $this->promotions = CheckoutRepository::$availablePromotions;
    }

    public function updateOrCreateProcessedContact()
    {
        $acquisitionChannel = $this->getCurrentSalesChannel(true);
        if (CheckoutRepository::$isFunnel) {
            $funnel = funnel::ignoreAccountIdScope()->where('id', $this->landingPage->funnel_id)->first();
            $acquisitionChannel = $funnel->funnel_name . " - " . $this->landingPage->name;
        }

        $processedContactService = new ProcessedContactService();
        $processedContact = $processedContactService->updateOrCreateProcessedContact(
            $this->formDetail,
            [
                'ordersCount' => 1,
                'totalSpent' => $this->grandTotal,
                'acquisition_channel' => $acquisitionChannel
            ],
        );

        $processedContact->segmentIds = '[]';
        return $processedContact;
    }

    public function createOrder($processedContact)
    {
        $order = new Order();

        // For two step form
        $isSameOrder = false;
        $isFunnel = CheckoutRepository::$isFunnel;
        if ($isFunnel) {
            $latestOrder = Order::where('account_id', $this->accountId)
                ->where('processed_contact_id', $processedContact->id)
                ->orderBy('created_at', 'desc')
                ->first();

            $paymentReference = isset($latestOrder) ? $latestOrder->payment_references : '';

            $isSameOrder = $this->funnelParams->fId === $paymentReference;
            if ($isSameOrder) {
                $order = $latestOrder;
                $order->order_number = $latestOrder->order_number;
            }
            $order->funnel_id = $this->landingPage->funnel_id;
            $order->landing_id = $this->landingPage->id;
        }

        /* Form Detail */
        // Shipping Detail
        $isShippingRequired = $this->formService->isShippingRequired();
        if ($isShippingRequired) {
            $shipping = $this->formDetail->shipping;
            $order->shipping_name = $shipping->fullName;
            $order->shipping_company_name = $shipping->companyName;
            $order->shipping_phoneNumber = $shipping->phoneNumber;
            $order->shipping_address = $shipping->address;
            $order->shipping_city = $shipping->city;
            $order->shipping_state = $shipping->state;
            $order->shipping_zipcode = $shipping->zipCode;
            $order->shipping_country = $shipping->country;
        }
        // Billing Detail
        $isBillingRequired = $this->formService->isBillingAddressRequired();
        if ($isBillingRequired) {
            $billing = $this->formDetail->billing;
            $order->billing_name = $billing->fullName;
            $order->billing_company_name = $billing->companyName;
            $order->billing_address = $billing->address;
            $order->billing_city = $billing->city;
            $order->billing_state = $billing->state;
            $order->billing_zipcode = $billing->zipCode;
            $order->billing_country = $billing->country;
        }

        /* Shipping Option (Delivery/Pickup) */
        $shippingOption = CheckoutData::$shippingOption;
        if ($this->formService->isShippingOptionRequired() && count((array)$shippingOption)) {
            $order->delivery_hour_type = 'custom';
            $order->delivery_date = $shippingOption->deliveryDate;
            $order->delivery_timeslot = $shippingOption->selectDeliverySlot;
            $order->delivery_type = $shippingOption->type;
            $order->pickup_location = ($shippingOption->type === 'pickup') ? json_encode(Location::first()) : json_encode([]);
        }

        /* Shipping Method */
        if ($isShippingRequired && !$isSameOrder) {
            $shippingMethod = CheckoutData::$shippingMethod;
            $order->shipping_method_name = $shippingMethod->shipping_name;
            $order->shipping_method = $shippingMethod->shipping_method;
            $order->shipping = $shippingMethod->charge;
        }

        /* Payment */
        $order->payment_status = PaymentService::PAYMENT_UNPAID_STATUS;
        $order->payment_process_status = PaymentService::PAYMENT_PROCESS_PENDING_STATUS;
        $order->payment_references = $this->refKeyService->getRefKey(new Order, 'payment_references');
        $order->payment_method = $this->paymentMethod->displayName;
        $order->paid_at = date('Y-m-d H:i:s');
        $order->paided_by_customer = 0;

        /* Currency */
        $currency = CheckoutData::$currency;
        $order->currency = $currency->currency;
        $order->exchange_rate = $currency->exchangeRate;

        /* Tax */
        $taxSetting = CheckoutRepository::$taxSettngs;

        if (isset($taxSetting)) {
            $taxSetting = (object)$taxSetting;
            if ($isSameOrder) {
                $hasShippingPromo = $order->orderDiscount()->where('promotion_category', 'Shipping')->exists();
            } else {
                $hasShippingPromo = $isShippingRequired && collect($this->promotions)->contains(function ($promo) {
                    return $promo['promotion']['discount_type'] === PromotionService::PROMOTION_SHIPPING_DISCOUNT;
                });
            }
            $shippingFee = $hasShippingPromo ? 0 : ($order->shipping ?? 0);
            $order->tax_name = $taxSetting->taxName;
            $order->tax_rate = $taxSetting->taxRate;
            $order->is_product_include_tax = $taxSetting->isProductIncludeTax;
            $order->is_shipping_fee_taxable = $taxSetting->isShippingFeeTaxable;
            $order->taxes = $this->taxService->getTotalTax(($isSameOrder ? 0 : $shippingFee), CheckoutRepository::$taxableProductTotal) + ($isSameOrder ? $order->taxes : 0);
            $order->shipping_tax =  $this->taxService->calculateTax($shippingFee);
        }

        /* Cashback */
        $cashback = $isFunnel ? [] : (CheckoutRepository::$cashback ?? []);
        $order->cashback_amount = ($cashback['cashbackDetail']['total'] ?? 0) * self::AMOUNT_MULTIPLY_BY;
        $order->used_credit_amount = $isFunnel ? 0 : (CheckoutRepository::$storeCredit * self::AMOUNT_MULTIPLY_BY);
        $order->cashback_detail = json_encode($cashback);

        /* Order */
        $order->subtotal = CheckoutRepository::$subTotal + ($isSameOrder ? $order->subtotal : 0);
        $order->total = (float)$this->grandTotal + ($isSameOrder ? $order->total : 0);

        /* Fulfillment */
        $order->fulfillment_status = self::ORDER_UNFULFILLED_STATUS;
        // Mark as fulfilled and close order for pure virtual product order
        if ($isSameOrder && $latestOrder->fulfillment_status !== self::ORDER_UNFULFILLED_STATUS) {
            $order->fulfillment_status = "Fulfilled";
            $order->additional_status = "Closed";
            $order->fulfilled_at = date('Y-m-d H:i:s');
        }

        /* Others */
        $order->account_id = $this->accountId;
        $order->acquisition_channel = $isFunnel ? 'Funnel' : $this->getCurrentSalesChannel(true);
        $order->notes = CheckoutData::$remark;
        $order->processed_contact_id = $processedContact->id;
        $order->reference_key = $isSameOrder ? $order->reference_key : $this->refKeyService->getRefKey(new Order, 'order_number');
        $order->segmentIds = $processedContact->segmentIds;

        $order->save();

        return $order;
    }

    public function createOrderDetail(Order $order)
    {
        $orderDetails = [];
        $cartItems = CheckoutRepository::$cartItemsWithPromotion;

        foreach ($cartItems as $key => $product) {
            $isPhysicalProduct = $this->cartService->isPhysicalProduct($product);
            $hasVariant = $product->hasVariant;
            $productVariant = (object)$this->cartService->getCartItemVariant($product);

            $orderDetail = new OrderDetail();
            $orderDetail->order_id = $order->id;

            /* Product */
            $orderDetail->users_product_id = $product->id;
            $orderDetail->product_name = $product->productTitle;
            $orderDetail->image_url = $product->productImagePath;
            $orderDetail->asset_url = $product->asset_url;
            $orderDetail->weight = (float)($hasVariant ? $productVariant->weight : $product->weight) * (int)$product->qty;
            $orderDetail->SKU = $hasVariant ? $productVariant->sku : $product->SKU;
            $orderDetail->quantity = $product->qty;
            $orderDetail->unit_price = $product->price; // original price of single product
            $orderDetail->total = $orderDetail->unit_price * $product->qty;

            // Variant
            $orderDetail->is_virtual = !$isPhysicalProduct;
            $orderDetail->variant = json_encode($product->variations ?? []);
            $orderDetail->variant_combination_id = $product->variantRefKey ?? null;
            $orderDetail->variant_name = $this->cartService->getCartItemVariantName($product->variations ?? []);
            $orderDetail->customization = json_encode($product->customizations ?? []);

            /* Product Tax */
            $orderDetail->is_taxable = $product->isTaxable;
            $orderDetail->tax =  $product->isTaxable ? $this->taxService->calculateTax($orderDetail->total) : 0;

            /* Product Disount */
            $orderDetail->discount = ($product->discount['value'] ?? 0) * self::AMOUNT_MULTIPLY_BY;
            $orderDetail->discount_details = json_encode($product->discount ?? []);
            $orderDetail->is_discount_applied = $product->isDiscountApplied;

            /* Product Payment */
            $orderDetail->payment_status = PaymentService::PAYMENT_UNPAID_STATUS;
            $orderDetail->paid_at = date('Y-m-d H:i:s');

            /* Product Fulfillment */
            $orderDetail->fulfillment_status = self::ORDER_UNFULFILLED_STATUS;
            $orderDetail->fulfilled_at = null;

            $orderDetail->save();
            array_push($orderDetails, $orderDetail);
        }
        return $orderDetails;
    }

    public function createOrderDiscount(Order $order)
    {
        $processedContact = ProcessedContact::find($order->processed_contact_id);
        foreach ($this->promotions as $promotion) {
            if (!$promotion['valid_status']) continue;
            $promoDiscountType = $promotion['promotion']['discount_type'];
            if ($promoDiscountType === PromotionService::PROMOTION_ORDER_DISCOUNT)
                $discountType = $promotion['promotion']['promotion_type']['order_discount_type'];
            if ($promoDiscountType === PromotionService::PROMOTION_PRODUCT_DISCOUNT)
                $discountType = $promotion['promotion']['promotion_type']['product_discount_type'];
            $isShippingDiscount = $promoDiscountType === PromotionService::PROMOTION_SHIPPING_DISCOUNT;
            if ($isShippingDiscount && empty($order->shipping_method)) continue;

            OrderDiscount::create([
                'order_id' => $order->id,
                'discount_code' => $promotion['promotion']['discount_code'],
                'discount_value' => ($isShippingDiscount ? $order->shipping : $promotion['discountValue']['value']) * 100,
                'display_name' => $promotion['promotion']['display_name'],
                'promotion_name' => $promotion['promotion']['promotion_name'],
                'promotion_method' => $promotion['promotion']['promotion_method'],
                'promotion_category' => $promotion['promotion']['promotion_category'],
                'discount_type' => $discountType ?? null,
            ]);

            $extraCondition = Promotion::find($promotion['promotion']['id'])->extraCondition;
            $extraCondition->store_usage += 1;
            $extraCondition->save();
            $customerRedemption = PromotionRedemptionLog::firstOrNew([
                'promotion_id' =>  $promotion['promotion']['id'],
                'customer_email' => $processedContact->email
            ]);
            $customerRedemption->discount_code = $promotion['promotion']['discount_code'];
            $customerRedemption->applied_usage += 1;
            $customerRedemption->save();

            $totalUsage = PromotionRedemptionLog::where('promotion_id', $promotion['promotion']['id'])->sum('applied_usage');
            $promotion = Promotion::find($promotion['promotion']['id']);
            $promotion->promotion_usage = $totalUsage;
            $promotion->save();
        }
    }

    public function createOrderTransaction(Order $order)
    {
        OrderTransactions::create([
            'order_id' => $order->id,
            'transaction_key' => $this->getRandomId('order_transactions', 'transaction_key'),
            'total' => $order->total,
            'paid_at' => now(),
            'payment_status' => PaymentService::PAYMENT_PAID_STATUS,
        ]);
    }

    /**
     * Create order subscription
     *
     * !! Not available yet (on 08/2022)
     *
     * @param  mixed $subscription
     * @param  mixed $order
     * @return void
     */
    public function createOrderSubscription($subscription, $order)
    {
        $productSubscription = ProductSubscription::where('price_id', $subscription['plan']['id'])->first();
        $orderSubscription = OrderSubscription::updateOrcreate(
            ['subscription_id' => $subscription['id']],
            [
                'processed_contact_id' => $order->processed_contact_id,
                'account_id' => $this->accountId,
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

    public function updateProductInventory(Order $order, $isSubscription = false)
    {
        OrderDetail::where('order_id', $order->id)->each(function ($orderDetail) use ($isSubscription) {
            $isVariant = $orderDetail->variant !== '[]';
            $inventory = $isVariant
                ? VariantDetails::where('reference_key', $orderDetail['variant_combination_id'])
                : UsersProduct::where('id', $orderDetail->users_product_id);
            $remainingStock = $inventory->first()->quantity;
            if ($isSubscription) {
                $inventory->update(['quantity' => $remainingStock - $orderDetail->quantity]);
            } else {
                if ($remainingStock !== 0) $inventory->update(['quantity' => $remainingStock - $orderDetail->quantity]);
            }
        });
    }


    public function updateOrderPaymentStatus(Order $order, $subscription = null)
    {
        if (isset($subscription)) $this->createOrderSubscription($subscription, $order);

        // Skip if payment via ipay88 not successfully processed
        $isIpay88 = $this->paymentMethod->name === PaymentService::PAYMENT_METHOD_IPAY88;
        $isPaymentProcessed = $order->payment_process_status === PaymentService::PAYMENT_PROCESS_SUCCESS_STATUS;
        if ($isIpay88 && !$isPaymentProcessed) return;

        $order->payment_process_status = PaymentService::PAYMENT_PROCESS_SUCCESS_STATUS;
        if ($this->paymentMethod->name !== PaymentService::PAYMENT_METHOD_MANUAL) {
            $order->paided_by_customer = $order->total;
            $order->payment_status = PaymentService::PAYMENT_PAID_STATUS;
        }

        $isCOD = $this->paymentMethod->name === PaymentService::PAYMENT_METHOD_MANUAL;
        $isOrderFulfilled = !$isCOD && !CheckoutRepository::$hasPhysicalProduct;
        if ($isOrderFulfilled) {
            $order->fulfillment_status = "Fulfilled";
            $order->additional_status = "Closed";
            $order->fulfilled_at = date('Y-m-d H:i:s');
        }

        // Only set order number after order successfully proccesed
        $order->order_number = $order->order_number ?? $this->getLastestOrderNumber();
        $order->orderDetails->each(function ($orderDetail) use ($order, $isCOD) {
            $data = [];
            if (!$isCOD) {
                $data['payment_status'] = PaymentService::PAYMENT_PAID_STATUS;
                $data['paid_at'] = date('Y-m-d H:i:s');
            }
            if (!$isCOD && $orderDetail->is_virtual) {
                $data['fulfillment_status'] = self::ORDER_FULFILLED_STATUS;
                $data['fulfilled_at'] = date('Y-m-d H:i:s');
                $data['order_number'] = "#" . $order->order_number . "-F1";
            }
            $orderDetail->update($data);
        });

        $order->save();
    }


    public function handleReferralAction(Request $request, $order, $isOneClickUpsell = false)
    {
        if (AffiliateCookieService::hasReferToken()) {
            $this->calculateAffiliateMemberCommissions($order, $request->getHost(), $isOneClickUpsell);
        }

        $people = ProcessedContact::find($order->processed_contact_id);

        if (CheckoutRepository::$isFunnel) {
            $orderCount = Order::where([
                'processed_contact_id' => $order->processed_contact_id,
                'funnel_id' => $this->landingPage->funnel_id
            ])->get()->count();

            $user = $this->referralUser($people, $this->landingPage->funnel_id);
            $cookie = $user ? cookie('funnel#user#' . $user['campaign'], $user['referralCode'], (3 * 30 * 24 * 60)) : null; // 90 days
        } else {
            $type = $this->getCurrentSalesChannel(true);
            $orderCount = Order::where(['processed_contact_id' => $order->processed_contact_id, 'acquisition_channel' => $type])->get()->count();
        }

        if ($request->hasCookie('referral') && $orderCount == 1) {
            $this->checkReferralCampaignAction($request->getHost(), 'purchase', $this->landingPage->funnel_id ?? null, $people, $order);
        }
        return [
            'cookie' => $cookie ?? null,
        ];
    }

    public function getOrderByPaymentRef($paymentRef)
    {
        return Order::firstWhere('payment_references', $paymentRef);
    }

    public function isProcessOrderInstantly()
    {
        $paymentMethodForInstantProcess = [PaymentService::PAYMENT_METHOD_MANUAL, PaymentService::PAYMENT_METHOD_STORE_CREDIT, PaymentService::PAYMENT_METHOD_NONE];
        $paymentMethodName = $this->paymentMethod->name;
        return in_array($paymentMethodName, $paymentMethodForInstantProcess);
    }

    public function sendOrderNotification(Order $order)
    {
        $notifiableSetting = Account::find($order->account_id)->notifiableSetting;
        $sellerEmails = [$order->sellerEmail()];
        if (!empty($notifiableSetting->notification_email)) {
            $sellerEmails = array_map('trim', explode(',', $notifiableSetting->notification_email));
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
    }

    public function funnelCheckoutTrack(Order $order)
    {
        if (!empty($order->funnel_id)) return;

        $visitorId = $this->funnelParams->visitorId ?? null;
        $cartId = $this->funnelParams->cartId ?? null;

        if ($visitorId) {
            $processedContact = ProcessedContact::where([
                'account_id' => $order->account_id,
                'id' => $order->processed_contact_id
            ])->first();

            $visitor = EcommerceVisitor::firstWhere('reference_key', $visitorId);

            $visitor->processed_contact_id = $processedContact->id ?? null;

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
    }

    private function getLastestOrderNumber()
    {
        $latestOrderNumber = Order::where([
            'account_id' => $this->accountId,
            'payment_process_status' => $this->paymentService::PAYMENT_PROCESS_SUCCESS_STATUS
        ])->max('order_number');
        return $latestOrderNumber === null ? 1000 : $latestOrderNumber + 1;
    }
}
