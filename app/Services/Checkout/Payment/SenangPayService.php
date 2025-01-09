<?php

namespace App\Services\Checkout\Payment;

use App\Order;
use App\OrderDetail;
use App\Traits\AuthAccountTrait;
use App\PaymentAPI;
use App\Repository\Checkout\CheckoutData;
use App\Traits\Checkout\CheckoutOrderTrait;

class SenangPayService
{
    use AuthAccountTrait, CheckoutOrderTrait;

    public const SANDBOX_API = 'https://sandbox.senangpay.my/payment';
    public const LIVE_API = 'https://app.senangpay.my/payment';

    public $accountId, $paymentMethod, $grandTotal;

    public function __construct(protected Order $order)
    {
        $this->accountId = $this->getCurrentAccountId();
        $this->paymentMethod =  PaymentAPI::firstWhere(['account_id' => $this->accountId, 'payment_methods' => 'senangPay']);
    }

    public function getActionLink()
    {
        $merchantId = $this->paymentMethod->publishable_key;
        $api = app()->environment(['production']) ? self::LIVE_API : self::SANDBOX_API;
        return $api . '/' . $merchantId;
    }

    public function getParameters()
    {
        if(!optional($this->paymentMethod)->enabled_at) return;
        $customerInfo = CheckoutData::$formDetail->customerInfo;
        return  [
            'actionLink' => $this->getActionLink(),
            'detail' => $this->generateDetail(),
            'amount' => $this->order->total,
            'order_id' => $this->order->id,
            'name' => $customerInfo->fullName,
            'email' => $customerInfo->email,
            'phone' => $customerInfo->phoneNumber,
            'hash' => $this->generateHash(),
        ];
    }

    public function generateHash()
    {
        $verifyKey = $this->paymentMethod->secret_key;
        $hash = hash_hmac('sha256', $verifyKey . $this->generateDetail() . $this->order->total . $this->order->id, $verifyKey);
        return $hash;
    }

    public function generateDetail()
    {
        $detail = '';
        OrderDetail::where('order_id', $this->order->id)->each(function ($item) use (&$detail) {
            $detail .= (empty($detail) ? '' :  ', ') . $item->product_name . ' x ' . $item->quantity;
        });
        return $detail;
    }

}
