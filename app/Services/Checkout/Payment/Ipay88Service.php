<?php

namespace App\Services\Checkout\Payment;

use App\Order;
use App\OrderDetail;
use App\PaymentAPI;
use App\Services\Checkout\PaymentService;
use App\Traits\AuthAccountTrait;

class Ipay88Service
{
	use AuthAccountTrait;

	protected $paymentSetting;

	public function __construct(Order $order = null)
	{
		$this->order = $order;
		$this->paymentSetting = PaymentAPI::firstWhere(['account_id' => $this->getCurrentAccountId(), 'payment_methods' => PaymentService::PAYMENT_METHOD_IPAY88]);
	}

	public function getMerchantCode()
	{
		return $this->paymentSetting->publishable_key;
	}


	/**
	 * Get required parameter/value to submit from payment checkout
	 *
	 * @param integer $orderId Id of order model
	 * @return string[]|void only return if payment method enabled
	 */
	public function getPaymentRequestParameter()
	{
		if (!optional($this->paymentSetting)->enabled_at) return;

		$processedContact = $this->order->processedContact;
		$mandatoryFields = [
			'MerchantCode' => $this->getMerchantCode(),
			'RefNo' => $this->order->reference_key,
			'Amount' => $this->getAmount($this->order->total),
			/**
			 * Refer to https://hypershapes.slack.com/files/U02AK5S2N9H/F04AH2UAPR7/appendix_i.pdf for myr gateway
			 * Refer to https://hypershapes.slack.com/files/U02AK5S2N9H/F049PSWJ03Z/appendix_ii.pdf for multi-currency gateway
			 */

			'Currency' => $this->order->currency,
			'ProdDesc' => $this->getProductDescription(),
			'UserName' => $processedContact->fname,
			'UserEmail' => $processedContact->email,
			'UserContact' =>  $processedContact->phone_number,
			'SignatureType' => config('ipay88.signature_type'),
			'Signature' => $this->generateSignature(),
			'ResponseURL' => $this->getResponseUrl(),
			'BackendURL' => config('ipay88.backend_url'),
		];
		// Parameter of optional field must exists in request
		$optionalFields = [
			'PaymentId' => '',
			'Remark' => '',
			'Lang' => '',
		];

		// Custom parameter will not submit to ipay88 opsg
		$customFields = [
			'actionLink' => config('ipay88.opsg_request_url')
		];
		return array_merge($mandatoryFields, $optionalFields, $customFields);
	}

	/**
	 * Get amount with 2 decimal and thousand symbol (exp: 1,278.99)
	 * 
	 * Noted: For demo account, will return specified transaction amount by ipay88
	 * @see https://hypershapes.slack.com/files/U02AK5S2N9H/F04A1EX5MBP/ipay88_technical_spec_v1.6.4.pdf
	 *
	 * @return string|null
	 */
	public function getAmount(float $price)
	{
		$price = $this->isDemoAccount() ? config('ipay88.transaction_amount')[$this->order->currency] ?? null : $price;
		return  number_format($price, 2);
	}


	/**
	 * Generate product description (max length: 100)
	 *
	 * @return string
	 */
	public function getProductDescription()
	{
		$detail = '';
		OrderDetail::where('order_id', $this->order->id)->each(function ($item) use (&$detail) {
			$detail .= (empty($detail) ? '' :  ', ') . $item->product_name . ' x ' . $item->quantity;
		});
		return strlen($detail) > 100 ? $detail : substr($detail, 0, 97) . '...';
	}

	public function generateSignature()
	{
		$merchantKey = $this->paymentSetting->secret_key;
		$merchantCode = $this->paymentSetting->publishable_key;
		$refNo = $this->order->reference_key;
		$amount = number_format((float)$this->getAmount($this->order->total), 2, '', ''); // : Remove the ',' and '.' in the string before hash
		$currency = $this->order->currency;

		$str = $merchantKey . $merchantCode . $refNo . $amount . $currency;
		$hash = hash(config('ipay88.signature_type'), $str);
		return $hash;
	}

	public function getSupportedCurrency()
	{
		return array_keys(config('ipay88.currency.' . config('ipay88.gateway')));
	}

	public function isSupportedCurrency($currency)
	{
		return in_array($currency, $this->getSupportedCurrency());
	}

	public function isDemoAccount()
	{
		return in_array($this->paymentSetting->publishable_key, config('ipay88.demo_accounts'));
	}

	public function getResponseUrl(){
		$domain = $this->getCurrentAccountId(true)['domain'];
		return 'https://' . $domain->domain . config('ipay88.response_path');
	}

	/**
	 * Get response parameter from ipay88 OPSG after transaction only if payment success
	 * 
	 * @return void
	 */
	public function callback()
	{
	}
}
