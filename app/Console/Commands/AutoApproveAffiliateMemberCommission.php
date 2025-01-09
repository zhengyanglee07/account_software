<?php

namespace App\Console\Commands;

use App\AffiliateMemberCommission;
use App\AffiliateMemberSetting;
use Illuminate\Console\Command;

class AutoApproveAffiliateMemberCommission extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'am:auto-approve-commission';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatically approve commission with regards to auto approval period.';

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
        $groupedMemberCommissions = AffiliateMemberCommission
            ::with('participant')
            ->where('status', '!=', 'approved')
            ->get()
            ->groupBy('account_id');

        foreach ($groupedMemberCommissions as $accountId => $memberCommissions)  {
            $settings = AffiliateMemberSetting::findOneOrCreateDefault($accountId);

            if (!$settings->auto_approve_commission) {
                continue;
            }

            // invalid period
            if ($settings->auto_approval_period <= 0) {
                continue;
            }

            foreach ($memberCommissions as $memberCommission) {
                $diffInDays = now()->diffInDays($memberCommission->created_at);

                if ($diffInDays < $settings->auto_approval_period) {
                    continue;
                }

                $memberCommission->update(['status' => 'approved']);
            }
        }

        return 0;
    }
}
