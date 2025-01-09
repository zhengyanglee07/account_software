<?php

namespace App\Services\Checkout;

use App\Tax;

use App\Repository\CheckoutRepository;
use App\Repository\Checkout\CheckoutData;

use App\Traits\AuthAccountTrait;
use App\Traits\CurrencyConversionTraits;


class TaxService
{
	use AuthAccountTrait, CurrencyConversionTraits;

	public $formService, $taxSettings;

	public function __construct()
	{
		$this->formService = new FormServices();
		$this->taxSettings = CheckoutRepository::$taxSettings ?? $this->getTaxSetting();
	}

	public function matchesCountryName($country): callable
	{
		return static function ($query) use ($country) {
			$query->where('country_name', $country);
		};
	}

	public function matchesSubRegionName($region): callable
	{
		return static function ($query) use ($region) {
			$query->where('sub_region_name', $region);
		};
	}

	public function getTaxSetting()
	{
		$address = $this->formService->getFormAddress();
		$country = $address->country;
		$region = $address->state;

		$taxes = Tax::with(['taxCountry' => $this->matchesCountryName($country)])
			->whereHas('taxCountry', $this->matchesCountryName($country))
			->where('account_id', $this->getCurrentAccountId())->first();

		if (!isset($taxes)) return;

		$regionTax = $taxes->taxCountry->firstWhere(function ($query) use ($region) {
			$query->where('has_sub_region', 1)
				->with(['taxCountryRegion' => $this->matchesSubRegionName($region)])
				->whereHas('taxCountryRegion', $this->matchesSubRegionName($region));
		});

		$taxSetting = $regionTax ?? $taxes->taxCountry;

		return [
			'taxRate' => (float)($taxSetting->total_tax ?? $taxSetting->country_tax),
			'taxName' => $taxSetting->tax_name,
			'isProductIncludeTax' => $taxes->is_product_include_tax,
			'isShippingFeeTaxable' => $taxes->is_shipping_fee_taxable
		];
	}

	public function calculateTax($price)
	{
		$taxSetting = $this->taxSettings;
		if (!isset($taxSetting)) return 0;
		$taxSetting = (object)$taxSetting;
		$taxRate = $taxSetting->taxRate / 100;
		$afterTax = $price * $taxRate;
		$tax = $taxSetting->isProductIncludeTax
			? $afterTax / (1 + $taxRate)
			: $afterTax;
		return $this->priceFormater($tax, CheckoutData::$currency, false);
	}

	public function getTotalTax($shippingFee, $productPrice, $isExcludeProductTax = false)
	{
		$taxSetting = $this->taxSettings;
		if (!isset($taxSetting)) return 0;
		$taxSetting = (object)$taxSetting;
		
		$productTax = $this->calculateTax($productPrice);
		
		// Exclude product tax from tax total if included in product price
		if ($isExcludeProductTax && $taxSetting->isProductIncludeTax) {
			$productTax = 0;
		}
		
		$shippingTax = $taxSetting->isShippingFeeTaxable ? $this->calculateTax($shippingFee) : 0;
		return $shippingTax + $productTax;
	}
}
