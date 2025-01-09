<?php

namespace App\Console\Commands;

use App\User;
use App\Email;
use App\Account;
use Carbon\Carbon;
use Illuminate\Console\Command;
use App\Jobs\SendOnboardingEmailJob;

class SendOnTimeEmail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'send:onTime-email';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send day 14 onboarding email on last hours in day 15';

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
        $isProduction = app()->environment() == 'production';
        $senderEmail = $isProduction ? 'steve@rocketlaunch.my' : 'steve@gmail.com';
        $senderAccountId = User::firstWhere('email', $senderEmail)->currentAccountId;

        // Day 14 onboarding email id in production
        $emailId = 557;
        // Get day 14 onboarding email id in local
        if (!$isProduction) {
            $emails = Email::where('account_id', $senderAccountId)->where('type', 'Marketing')->get();
            foreach ($emails as $key => $email) {
                if ($key == 14) $emailId = $email['id'];
            }
        }

        // filter account that need to sent day 14 onboarding email
        $accounts = Account::where('onboarding_email_to_sent', 14)
            ->whereBetween('send_onboarding_email_at', array(Carbon::now()->subMinutes(5), Carbon::now()))
            ->get();

        foreach ($accounts as $account) {
            $sentEmailAt = $account['send_onboarding_email_at'];
            if ($isProduction) {
                $diff = Carbon::now()->diffInMinutes($sentEmailAt, false);
            } else {
                $diff = Carbon::now()->diffInSeconds($sentEmailAt, false);
            }
            info("Different: " . $diff); //ToRemove

            if ($diff <= 0) {
                $receiverEmail = $account->user->first()->email;
                $email = Email::whereId($emailId)->first();

                if ($receiverEmail && isset($email)) {
                    // sent onboarding email 
                    dispatch(new SendOnboardingEmailJob($email, $receiverEmail));
                    if($isProduction){
                        $sendNextEmailAt = Carbon::parse($sentEmailAt)->addHour(1);
                    }else{
                        $sendNextEmailAt = Carbon::parse($sentEmailAt)->addSeconds(30);
                    }
                    // update total onboarding after email was sent 
                    $account->update([
                        'send_onboarding_email_at' => $sendNextEmailAt,
                        'onboarding_email_to_sent' => 15
                    ]);
                    $account->save();
                }
            };
        }
    }
}
