<?php

namespace App\Traits;

use App\Order;
use App\Currency;

trait CurrencyConversionTraits
{

    use AuthAccountTrait;

    private function filteredOrders()
    {
        return Order::where('account_id', $this->getCurrentAccountId())->where(
            function ($query) {
                $query->where('payment_process_status', 'Success')->orWhereNull('payment_process_status');
            }
        )
            ->orderBy('order_number', 'desc')
            ->get()
            ->toArray();
    }

    private function getCurrencyArray($currency, $accountId = null)
    {
        if ($accountId === null) $accountId = $this->getCurrentAccountId();
        if ($currency === null) {
            return Currency::firstWhere(['account_id' => $accountId, 'isDefault' => '1']);
        }
        return Currency::firstWhere(['account_id' => $accountId, 'currency' => $currency]);
    }

    private function convertCurrency($originalPrice, $currency, $isRealTime = false, $toDefault = false, $accountId = null)
    {
        $defaultCurrency = $this->getCurrencyArray(null, $accountId);
        $exchangeCurrency = $this->getCurrencyArray($currency, $accountId);
        if (optional($defaultCurrency)->currency === $currency) {
            return (float)$originalPrice;
        }
        $exchangeRate = $isRealTime ?  $exchangeCurrency['suggestRate'] : $exchangeCurrency['exchangeRate'];
        return $toDefault ? (float)$originalPrice / (float)$exchangeRate : (float)$originalPrice * (float)$exchangeRate;
    }

    private function getCurrencyPrefix($currency)
    {
        return $this->getCurrencyArray($currency)->prefix;
    }

    private function getTotalPrice($allorders, $number_format = true, $accountId = null)
    {
        $totalSales = array_reduce(
            $allorders,
            function ($acc, $order) use ($accountId) {
                return $acc += $this->convertCurrency($order['total'], $order['currency'], true, true, $accountId);
            },
            0
        );
        return $number_format
            ? number_format($totalSales, 2)
            : $totalSales;
    }

    public function getCurrencyRange($productPrice, $currency, $calulation = true)
    {
        $currencyArray = ['BIF', 'CLP', 'DJF', 'GNF', 'JPY', 'KMF', 'KRW', 'MGA', 'PYG', 'RWF', 'UGX', 'VND', 'VUV', 'XAF', 'XOF', 'XPF'];
        if (in_array($currency, $currencyArray)) {
            if (!$calulation) return floor($productPrice);
            return floor($productPrice / 100);
        }
        return $productPrice;
    }

    public function getOrderCurrencyRange($productPrice, $currency)
    {
        $currencyArray = ['BIF', 'CLP', 'DJF', 'GNF', 'JPY', 'KMF', 'KRW', 'MGA', 'PYG', 'RWF', 'UGX', 'VND', 'VUV', 'XAF', 'XOF', 'XPF'];
        if (in_array($currency, $currencyArray)) {
            return floor($productPrice * 100);
        }
        return $productPrice;
    }

    public function priceFormater($productPrice, $currency = null, $isDisplay = true)
    {
        $currencyObject = $currency;
        if (!$currency) $currencyObject = $this->getCurrencyArray($currency);

        if ($currencyObject->separator_type === '.')
            $productPrice = preg_replace('/[^0-9.]+|\.(?=\d+\.\d+|$)/', '', $productPrice);
        else if ($currencyObject->separator_type === ',')
            $productPrice = preg_replace('/,/', '', $productPrice);

        $formatPrice = (float)$productPrice;

        if (!$isDisplay) return (float)number_format($formatPrice, 2, '.', '');

        $formatPrice = $currencyObject->decimal_places == 0 ? floor($formatPrice) : $formatPrice;
        $formatPrice = number_format($formatPrice, $currencyObject->decimal_places, null, $currencyObject->separator_type);
        return $formatPrice;
    }

    public function getDefaultCurrency(): Currency
    {
        return Currency::where('account_id', $this->getCurrentAccountId())->where('isDefault', 1)->first();
    }
}
