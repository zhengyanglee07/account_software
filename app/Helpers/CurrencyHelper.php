<?php
namespace App\Helpers;
use App\Currency;
class CurrencyHelper
{
    public function priceFormater($productPrice,$currency = null,$accountId = null)
    {
        $currencyObject = Currency::firstWhere(['account_id' => $accountId, 'currency' => $currency]);
        $formatPrice = (float)$productPrice;
        $formatPrice = $currencyObject->decimal_places == 0 ? floor($formatPrice) : $formatPrice;
        $formatPrice = number_format($formatPrice,$currencyObject->decimal_places,null,$currencyObject->separator_type);
        return $formatPrice;
    }
}
