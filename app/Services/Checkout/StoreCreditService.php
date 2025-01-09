<?php

namespace App\Services\Checkout;

use App\Currency;
use App\Order;
use App\StoreCredit;

use App\Repository\CheckoutRepository;
use App\Repository\Checkout\CheckoutData;
use App\Services\ProcessedContactService;

use App\Traits\AuthAccountTrait;
use App\Traits\SalesChannelTrait;
use App\Traits\Checkout\CheckoutCurrencyTrait;
use App\Traits\Checkout\CheckoutOrderTrait;

use Carbon\Carbon;


class StoreCreditService
{
    use AuthAccountTrait, CheckoutOrderTrait, CheckoutCurrencyTrait, SalesChannelTrait;

    public const AMOUNT_MULTIPLY_BY = 100;

    protected $processedContactService;
    protected $processedContact;

    public function __construct()
    {
        $email = CheckoutData::$formDetail->customerInfo->email;
        $this->processedContactService = new ProcessedContactService();
        $this->processedContact = $this->processedContactService->getProcessedContactByEmail($email);
    }


    public function getStoreCreditToBeUsed()
    {
        if (!isset($this->processedContact)) return 0;

        $grandTotal = CheckoutRepository::$grandTotalWithoutStoreCredit;
        $creditToUsed = $this->getConversionPrice($this->processedContact->credit_balance / self::AMOUNT_MULTIPLY_BY);
        if ($creditToUsed > $grandTotal) $creditToUsed = $grandTotal;
        return $creditToUsed;
    }

    public function recordStoreCreditUsage(Order $order)
    {
        $currencyArray = Currency::where('account_id', $this->getCurrentAccountId())->pluck('exchangeRate', 'currency')->toArray();
        $currency = $order->currency;
        $contact = $this->processedContact;
        $storeCreditUsed = $order->used_credit_amount;
        if ($storeCreditUsed == 0) return;
        StoreCredit::create([
            'account_id' => $contact->account_id,
            'processed_contact_id' => $contact->id,
            'credit_amount' => $storeCreditUsed,
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
        $leftOverCredit = $storeCreditUsed;
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

        $contact->credit_balance -= ($storeCreditUsed / ((float)$currencyArray[$currency]));
        $contact->save();
    }
}
