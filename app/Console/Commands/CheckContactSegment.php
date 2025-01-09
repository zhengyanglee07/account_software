<?php

namespace App\Console\Commands;

use App\Events\ContactEnteredSegment;
use App\Events\ContactExitedSegment;
use App\Segment;
use Illuminate\Console\Command;
use App\Account;

class CheckContactSegment extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'auto:check-contact-segment';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check contacts of segments';

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
        $accountIds = Account::whereHas('automations', function ($automation) {
            return $automation->where('automation_status_id', 2)->whereHas('automationTriggers', function ($trigger) {
                return $trigger->whereIn('trigger_id', [9, 10]);
            })->with('automationTriggers');
        })->with('automations')->where('subscription_status', 'active')->pluck('id');

        Segment::whereIn('account_id', $accountIds)->each(function ($row) {
            $contactIds = $row->contacts()->pluck('id')->toArray();
            $processedContactIds = json_decode($row->contactsID, true);
            $contactEnteredSegment = array_diff($contactIds, $processedContactIds);
            $contactExitedSegment = array_diff($processedContactIds, $contactIds);

            if (count($contactEnteredSegment))
                event(new ContactEnteredSegment($row, $contactEnteredSegment));

            if (count($contactExitedSegment))
                event(new ContactExitedSegment($row, $contactExitedSegment));

            $row->update(['contactsID' => json_encode($contactIds)]);
        });
        return 0;
    }
}
