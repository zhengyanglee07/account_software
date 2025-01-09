<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Account;
use Illuminate\Support\Carbon;

class CheckEmailQuota extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reset:emailquota';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check daily to see whether the user quota would reset';

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
     * @return mixed
     */
    public function handle()
    {
       $allAccount = Account::all();

       foreach($allAccount as $account){
           $subscription = $account->subscription;
           $accountPlanTotal= $account->accountPlanTotal;
           if($subscription !==null){
             	if(Carbon::parse($subscription->last_email_reset) <= Carbon::now()){
                    $accountPlanTotal->total_email = 0;
                    $accountPlanTotal->save();
                    $subscription->last_email_reset = Carbon::parse($subscription->last_email_reset)->addMonth();
                    $subscription->save();
               }
           }
       }
    }
}
