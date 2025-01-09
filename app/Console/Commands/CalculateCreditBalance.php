<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\ProcessedContact;
use App\StoreCredit;
use App\Currency;
use Carbon\Carbon;

class CalculateCreditBalance extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'calculate:creditBalance';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Filter out expired credits and recalculate the credit balance for every user.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        ProcessedContact::with(['storeCredits' => function ($credits) {
            $credits->where('credit_type', 'Add')
                ->where('expire_date', '<=', Carbon::now())
                ->where('balance', '>', 0)
                ->orderBy('expire_date');
        }])->chunkById(100, function ($contacts) {
            if (count($contacts) === 0) {
                return;
            }
            foreach ($contacts as $contact) {
                $currencyArray = Currency::where('account_id', $contact->account_id)
                    ->pluck('exchangeRate', 'currency')->toArray();
                if (count($contact->storeCredits) === 0) {
                    continue;
                }
                $accountId = $contact->account_id;
                $processedContactId = $contact->id;
                $totalExpiredCredits = 0;
                foreach ($contact->storeCredits as $expired) {
                    if(!isset($currencyArray[$expired->currency])) continue;
                    $totalExpiredCredits += ($expired->balance / (float)$currencyArray[$expired->currency]);
                    StoreCredit::create([
                        'account_id' => $expired->account_id,
                        'processed_contact_id' => $expired->processed_contact_id,
                        'currency' => $expired->currency,
                        'credit_amount' => $expired->balance,
                        'credit_type' => 'Deduct',
                        'source' => 'Credit Expired.',
                        'reason' => 'Expired',
                    ]);
                    $expired->balance = 0;
                    $expired->save();
                }
                $contact->credit_balance = $contact->credit_balance - $totalExpiredCredits;
                $contact->save();
            }
        });
        $this->info('Credit Balance recalculated.');
    }
}
