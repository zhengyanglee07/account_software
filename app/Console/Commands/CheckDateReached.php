<?php

namespace App\Console\Commands;

use App\Events\TriggerDateReached;
use Illuminate\Console\Command;

class CheckDateReached extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'auto:date-reached';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Trigger a date based automation';

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
     */
    public function handle()
    {
        event(new TriggerDateReached(now()));
        return 0;
    }
}
