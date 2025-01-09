<?php

namespace App\Services;

use App\Account;
use App\Traits\CurrencyConversionTraits;
use Illuminate\Support\Collection;

class ContactCurrencyService
{
    use CurrencyConversionTraits;

    public function mapContactsValuesBasedOnCurrency(Collection $contacts, $accountId = null): Collection
    {
        if ($contacts->count() === 0) {
            return $contacts;
        }

        // sampling first contact to obtain account currency
        $currency = Account::find($contacts[0]->account_id)->currency;

        return $contacts
            ->map(function ($contact) use ($currency, $accountId) {
                // convert value based on currency
                $contact->totalSales = $this->getTotalPrice($contact->orders->toArray(),false, $accountId);
                $contact->credit_balance = $this->convertCurrency($contact->credit_balance, $currency, false, false, $accountId);
                return $contact;
            });
    }}
