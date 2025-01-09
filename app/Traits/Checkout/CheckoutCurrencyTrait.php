<?php

namespace App\Traits\Checkout;

use App\Repository\Checkout\CheckoutData;
use App\Traits\CurrencyConversionTraits;

trait CheckoutCurrencyTrait
{
	use CurrencyConversionTraits;

	public function getCheckoutCurrency()
	{
		return CheckoutData::$currency->currency;
	}

	public function getConversionPrice($price)
	{
		return $this->convertCurrency($price, $this->getCheckoutCurrency());
	}

	/**
	 * Convert price for stripe payment
	 * 
	 * ! Price should be multiply by 10 except zero decimal currency !
	 * 
	 * View more detail about stripe currency: https://stripe.com/docs/currencies
	 *
	 * @param  int|float $price
	 * @return int|float
	 */
	public function getConversionPriceForStripe($price): int|float
	{
		$currency = $this->getCheckoutCurrency();
		$zeroDecimalCurrencies = ['BIF', 'CLP', 'DJF', 'GNF', 'JPY', 'KMF', 'KRW', 'MGA', 'PYG', 'RWF', 'UGX', 'VND', 'VUV', 'XAF', 'XOF', 'XPF'];
		if (!in_array($currency, $zeroDecimalCurrencies)) $price *= 100;
		return $price;
	}
}
