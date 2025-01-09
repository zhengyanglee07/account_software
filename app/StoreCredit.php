<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\ProcessedContact;
use Carbon\Carbon;

class StoreCredit extends Model
{
    protected $guarded = [];

    protected static function boot() 
    {
        parent::boot();
        static::created(function ($storeCredit) {
            $contact = ProcessedContact::find($storeCredit->processed_contact_id);
            $currencyArray = Currency::where('account_id', $storeCredit->account_id)->pluck('exchangeRate', 'currency')->toArray();
            $creditRecords = StoreCredit::where('account_id', $storeCredit->account_id)
                                ->where('processed_contact_id', $storeCredit->processed_contact_id)
                                ->where('credit_type', 'Add')
                                ->where('expire_date', '>', Carbon::now())
                                ->where('balance', '>', 0)
                                ->orderBy('expire_date')
                                ->get();
            $balanceCredit = 0;
            foreach ($creditRecords as $record) {
                $balanceCredit += ($record->balance / ((float) $currencyArray[$record->currency]));
            }

            $contact->credit_balance = $balanceCredit;
            $contact->save();
        });
    }
}
