<?php

namespace App;
use App\funnel;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use App\ProcessedContact;
// use App\Currency;
use App\StoreCredit;
use App\Traits\AuthAccountTrait;

/**
 * Class Currency
 * @package App
 *
 * @mixin \Eloquent
 */
class Currency extends Model
{
    use AuthAccountTrait;
    protected $fillable = [
        'account_id',
        'currency',
        'exchangeRate',
        'suggestRate',
        'isDefault',
        'decimal_places',
        'rounding',
        'separator_type',
        'prefix',
    ];
    public function currency()
    {
        return $this->hasOne(Account::class);
    }

    public static function boot(){
        parent::boot();
        static::updated(function($data){
            if($data->exchangeRate === null){
                $funnels = funnel::where('account_id',$data->account_id)->where('currency',$data->currency)->get();
                $defaultCurrency = currency::where('account_id',$data->account_id)->where('isDefault','1')->first();
                foreach($funnels as $funnel){ $funnel->update([ 'currency' => $defaultCurrency->currency ]); }
            }

            // Recalculate Store Credit
            $allContacts = ProcessedContact::where('account_id', $data->account_id)->get();
            $currencyArray = Currency::where('account_id', $data->account_id)->pluck('exchangeRate', 'currency')->toArray();

            foreach ($allContacts as $contact){
                $creditRecords = StoreCredit::where('account_id',$data->account_id)
                                    ->where('processed_contact_id', $contact->id)
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
            }
        });
    }

    public function calculateStoreCredit($accountId)
    {

    }

    public static function currencyDetails()
    {
        $currency = new Currency;
        return Currency::where('account_id', $currency->getCurrentAccountId())->get();
    }
}
