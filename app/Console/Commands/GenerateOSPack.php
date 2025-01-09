<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\User;
use App\Account;

class GenerateOSPack extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'generate:ospack {--email=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate payment, shipping, tax, promotion and cashback settings for testing purpose';

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
     * @return int
     */
    public function handle()
    {
        $email = $this->option('email');
        $account = $this->getAccountByEmail($email);
        if ($account == null) {
            return $this->error('User does not exist in database!');
        }

        $this->info('Generating...');
        $this->info('');

        // Payment Setting
        $this->generatePaymentSetting($account->id);

        // Cashback Setting
        $this->generateCashbackSettings($account->id);

        // Shipping Setting

        // Tax Setting

        // Promotion Setting

        $this->info('');
        $this->info('Done.');
        return 0;
    }

    protected function getAccountByEmail($email)
    {
        $user = User::where('email', $email)->first();
        if ($user === null) {
            return null;
        }
        return $user->currentAccount();
    }

    protected function generatePaymentSetting($accountId)
    {
        $paymentSettings = [
            [
                'account_id' => $accountId,
                'publishable_key' => 'pk_test_Xn0BfLQ7cDD5GlIJl9BOZAVy00VPFHAyEF',
                'secret_key' => 'sk_test_jiOaiu4JcFbIW8kKt9y1v0YR00y8ojNNUv',
                'payment_methods' => 'Stripe',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'account_id' => $accountId,
                'publishable_key' => '309160429580690',
                'secret_key' => '3005-739',
                'payment_methods' => 'senangPay',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
        ];

        DB::table('payment_a_p_i_s')->insert($paymentSettings);
        $this->info('Payment settings generated.');
    }

    protected function generateCashbackSettings($accountId)
    {
        $cashbacks = [
            'ref_key' => 999999999999,
            'account_id' => $accountId,
            'cashback_title' => 'TESTING CASHBACK',
            'for_all' => 1,
            'min_amount' => 0,
            'cashback_amount' => 500,
            'expire_date' => 12,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];

        DB::table('cashbacks')->insert($cashbacks);
        $this->info('Cashback settings generated.');
    }
}
