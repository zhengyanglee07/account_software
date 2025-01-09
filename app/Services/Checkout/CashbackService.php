<?php

namespace App\Services\Checkout;

use App\Cashback;
use App\Order;
use App\ProcessedContact;
use App\StoreCredit;

use App\Repository\CheckoutRepository;
use App\Repository\Checkout\CheckoutData;

use App\Services\ProcessedContactService;
use App\Services\SegmentService;

use App\Traits\AuthAccountTrait;
use App\Traits\SalesChannelTrait;
use App\Traits\Checkout\CheckoutCurrencyTrait;
use App\Traits\Checkout\CheckoutOrderTrait;

use Carbon\Carbon;


class CashbackService
{
    use AuthAccountTrait, CheckoutCurrencyTrait, SalesChannelTrait, CheckoutOrderTrait;

    public const AMOUNT_MULTIPLY_BY = 100;

    protected $processedContact;

    public function __construct(ProcessedContact $processedContact = null)
    {
        $this->processedContact = $processedContact;
        if (!isset($processedContact)) {
            $email = CheckoutData::$formDetail->customerInfo->email;
            $this->processedContact = (new ProcessedContactService)->getProcessedContactByEmail($email);
        }
    }

    public function getCashback()
    {
        $accountId = $this->getCurrentAccountId();
        $cashbacks = Cashback::where('account_id', $accountId)->with(['segments', 'saleChannels'])->orderBy('cashback_amount', 'DESC')->orderBy('expire_date', 'DESC')->get();
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
        return collect($cashbacks);
    }


    public function getMostBeneficialCashback()
    {
        $grandTotal = CheckoutRepository::$grandTotal;
        $cashbacks = $this->getCashback();
        $cashbackPercentage = 0;
        $currentSaleChannel = $this->getCurrentSalesChannel();

        $validCashbacks = $cashbacks->filter(function ($cashback) use ($currentSaleChannel) {
            return collect($cashback->salesChannel)->some(function ($saleChannel) use ($currentSaleChannel) {
                return $saleChannel->type === $currentSaleChannel;
            });
        });

        $availableCashback = $validCashbacks->filter(function ($cashback) use ($grandTotal) {
            return $this->getConversionPrice($cashback->min_amount / self::AMOUNT_MULTIPLY_BY) <= $grandTotal;
        });

        $availableCashback = $availableCashback->map(function ($cashback) use ($grandTotal) {
            $cashbackTotal = $grandTotal * (($cashback->cashback_amount / self::AMOUNT_MULTIPLY_BY) / 100);
            $cashback->total = round($cashbackTotal, 2);
            if ($cashback->capped_amount) {
                $cappedAmount = $cashback->capped_amount / self::AMOUNT_MULTIPLY_BY;
                $cashback->total = $cashbackTotal > $cappedAmount ? $cappedAmount : $cashbackTotal;
            }
            return $cashback;
        });

        $sortedCashback = $availableCashback->sortBy('total');
        $selectedCashback = $sortedCashback->first(function ($cashback) {
            if ($cashback->for_all) return true;
            return collect($cashback->contactIds)->contains($this->processedContact->id);
        });

        if (!isset($selectedCashback)) return;

        $cashbackPercentage = $selectedCashback->cashback_amount / self::AMOUNT_MULTIPLY_BY;
        return [
            'cashbackDetail' => $selectedCashback,
            'cashbackPercentage' => $cashbackPercentage,
        ];
    }

    public function recordCashbackEarned(Order $order)
    {
        $contact = $this->processedContact;
        $cashback = json_decode($order->cashback_detail, true);

        if (!isset($cashback['cashbackDetail'])) return;

        $cashbackAmount = $cashback['cashbackDetail']['total'];

        StoreCredit::create([
            'account_id' => $contact->account_id,
            'processed_contact_id' => $contact->id,
            'credit_amount' => $cashbackAmount * self::AMOUNT_MULTIPLY_BY,
            'balance' => $cashbackAmount * self::AMOUNT_MULTIPLY_BY,
            'currency' => $order->currency,
            'credit_type' => 'Add',
            'source' => 'Cashback',
            'reason' => 'Cashback',
            'expire_date' => Carbon::now()->addMonth($cashback['cashbackDetail']['expire_date'])->toDateString()
        ]);
    }
}
