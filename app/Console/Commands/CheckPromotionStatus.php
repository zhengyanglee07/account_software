<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Promotion\Promotion;

class CheckPromotionStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'check:promotionstatus';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check the expired of promotion and change the status';

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
       $promotions =  Promotion::where('end_date','<=', \Carbon\Carbon::now())->where('is_expiry',true)->get();
       foreach($promotions as $promotion){
           $promotion->promotion_status = 'expired';
           $promotion->save();
       }
       $activePromo = Promotion::where('start_date','<=',\Carbon\Carbon::now())->where('promotion_status','!=','expired')->get();
       foreach($activePromo as $promo){
           $promo->promotion_status ='active';
           $promo->save();
       }
    }
}
