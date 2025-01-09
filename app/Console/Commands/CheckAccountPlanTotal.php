<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Account;

class CheckAccountPlanTotal extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'check:accountplantotal';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'To recalculate the account plan total match the current accurate result';

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

        $this->info('Recalculating ....');

        $this->calculateDomain();
        $this->info('Calculate domain done.....');

        return 0;
    }

    public function calculateDomain(){
        $accounts = Account::all();
        foreach($accounts as $account){
            $subscription = $account->subscription;
            if($subscription){
                $accountPlan = $account->accountPlanTotal;
                $accountPlan->total_domain = $account->domains->where('is_subdomain',false)->count();
                $accountPlan->save();
            }
        }
    }
}
