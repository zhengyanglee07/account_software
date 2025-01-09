<?php

namespace App\Console\Commands;

use App\Events\CartAbandoned;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;

use Carbon\Carbon;
use App\EcommerceVisitor;

class CheckAbandonedCart extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'auto:abandoned-cart';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Mark as abandoned cart if CRM session expired';

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
        $visitors = EcommerceVisitor::where('is_completed', false)->whereHas('abandonedCart', function (Builder $query) {
            $query->whereNull('abandoned_at')->whereJsonLength('cart_products', '>', 0);
        })->get();

        foreach ($visitors as $visitor) {
            $lastActivityDateTime = $visitor->activityLogs()->latest()->value('created_at');

            if (Carbon::now() > $lastActivityDateTime->addMinutes(30)) {
                $visitor->abandonedCart->update([
                    'abandoned_at' => $lastActivityDateTime
                ]);
            }
        }

        // event(new CartAbandoned($visitors));

        return 0;
    }
}
