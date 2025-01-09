<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use App\Account;
use App\User;
use App\Email;
use App\Jobs\SendOnboardingEmailJob;
use App\Subscription;

use function React\Promise\Stream\first;

class SendOnboardingEmail extends Command
{
    public $senderAccountId;
    public $isProduction;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'send:onboarding-email';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'send an activation email every day for 16 days continuously';

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
    public function handle(): void
    {
        $this->isProduction = app()->environment() == 'production';
        $senderEmail = $this->isProduction ? 'steve@rocketlaunch.my' : 'steve@gmail.com';
        // Get sender account id
        $this->senderAccountId = User::firstWhere('email', $senderEmail)->currentAccountId;

        if ($this->isProduction) {
            $this->sendEmail();
        } else {
            // one minutes = one days
            // last 30 seconds = last 1 hours
            $this->sendEmailTest();
        }
    }

    public function sendEmail()
    {
        $accounts = Account::whereNotNull('send_onboarding_email_at')
            ->whereBetween('send_onboarding_email_at', array(Carbon::now()->subHours(1), Carbon::now()))->get();

        foreach ($accounts as $account) {
            $sendEmailAt = $account['send_onboarding_email_at'];
            $emailToSent = $account['onboarding_email_to_sent'];
            $subscriptions = Subscription::firstWhere('account_id', $account['id']);
            $hasYearlySubscription =
                $subscriptions['subscription_plan_id'] > 1 &&
                $subscriptions->subscriptionPlanType['subscription_plan_type'] == 'yearly';
            $diffInMinutes = Carbon::now()->diffInMinutes($sendEmailAt, false);

            // Skip day 1-12 in production
            if ($this->isProduction && ($emailToSent >= 1 && $emailToSent <= 12)) {
                $account->update([
                    'send_onboarding_email_at' => Carbon::parse($sendEmailAt)->addDays(13),
                    'onboarding_email_to_sent' => 13,
                ]);
                $account->save();
                continue;
            }
            // Skip day 15 in production
            if($emailToSent == 15) continue;


            // skip day (12 - 14) email if user have subscript yearly plan
            if ((($emailToSent >= 12 && $emailToSent <= 14) && $hasYearlySubscription)) {
                $account->update(
                    [
                        'send_onboarding_email_at' => Carbon::parse($sendEmailAt)->addDays(15),
                        'onboarding_email_to_sent' => 15
                    ]
                );
                $account->save();
                continue;
            }

            $email = $this->getEmail($account, $emailToSent, $hasYearlySubscription);
            if ($diffInMinutes <= 0 && isset($email)) {
                $receiverEmail = $account->user->first()->email;
                // sent onboarding email 
                dispatch(new SendOnboardingEmailJob($email, $receiverEmail));

                $sendNextEmailAt = Carbon::parse($sendEmailAt)->addDays(1);
                if ($emailToSent == 13) {
                    if ($this->isProduction) {
                        $sendNextEmailAt = Carbon::parse($sendEmailAt)->addHour(47);
                    } else {
                        $sendNextEmailAt = Carbon::parse($sendEmailAt)->addSeconds(90);
                    }
                }
                // update total onboarding after email was sent 
                $account->update(
                    [
                        'send_onboarding_email_at' => $sendNextEmailAt,
                        'onboarding_email_to_sent' => $emailToSent += 1
                    ]
                );
                $account->save();
            }
        };
    }

    // Use in local & staging for testing
    // Can view simple output in Laravel log file
    public function sendEmailTest()
    {
        $accounts = Account::whereNotNull('send_onboarding_email_at')
            ->whereDate('send_onboarding_email_at', '<=', Carbon::now())
            ->where('onboarding_email_to_sent','<', 16)
            ->get();

        foreach ($accounts as $account) {
            $sendEmailAt = $account['send_onboarding_email_at'];
            $emailToSent = $account['onboarding_email_to_sent'];
            $subscriptions = Subscription::firstWhere('account_id', $account['id']);
            $hasYearlySubscription =
                $subscriptions['subscription_plan_id'] > 1 &&
                $subscriptions->subscriptionPlanType['subscription_plan_type'] == 'yearly';
            $diffInMinutes = Carbon::now()->diffInMinutes($sendEmailAt, false);

            info("_____________________________________________START_______________________________________________________");
            info("Account Id: " . $account['id']);
            info("Diff In Minutes: $diffInMinutes");
            info("Onboarding Email to sent: $emailToSent");
            info("Has Yearly Subscription: " . ($hasYearlySubscription ? "True" : "False"));
            info("Selected mini-store: " . ($account['selected_mini_store'] ? "True" : "False"));

            // skip day (12 - 14) email if user have subscript yearly plan
            if ((($emailToSent >= 12 && $emailToSent <= 14) && $hasYearlySubscription)) {
                $account->update([
                    'send_onboarding_email_at' => Carbon::parse($sendEmailAt)->addMinutes(15),
                    'onboarding_email_to_sent' => 15,
                ]);
                $account->save();
                continue;
            }

            $email = $this->getEmail($account, $emailToSent, $hasYearlySubscription);
            if ($diffInMinutes <= 0 && isset($email)) {
                $receiverEmail = $account->user->first()->email;
                // sent onboarding email 
                dispatch(new SendOnboardingEmailJob($email, $receiverEmail));
                $sentNextEmailAt = Carbon::parse($sendEmailAt)->addMinutes(1);
                info("Sent day " . $emailToSent . " onboarding email to: $receiverEmail");
                info("Next email will send at: " . $sentNextEmailAt);
                // update info after email was sent 
                if ($emailToSent == 13) {
                    // assumes last 30 seconds = last hour
                    $sentNextEmailAt = Carbon::parse($sendEmailAt)->addSeconds(90);
                }
                $account->update(
                    [
                        'send_onboarding_email_at' => $sentNextEmailAt,
                        'onboarding_email_to_sent' => $emailToSent += 1
                    ]
                );
                $account->save();
            } else {
                info("No email to sent");
            }
            info("______________________________________________END________________________________________________________");
            info("");
        }
    }

    /**
     * Get onboarding email by id
     */
    public function getEmail($account, $emailToSent, $hasYearlySubscription)
    {
        $idArray = [];
        // set ascending order of id for onboarding email from day 0 to day 15
        if (!$this->isProduction) {
            $emails = Email::where('account_id', $this->senderAccountId)->where('type', 'Marketing')->get();
            foreach ($emails as $email) {
                array_push($idArray, $email['id']);
            }
        } else {
            $idArray = [542, 543, 544, 545, 546, 547, 548, 549, 550, 551, 552, 553, 555, 556, 557, 553];
        }

        $emailId = 0;
        if ($emailToSent == 0) {
            // Day 0 - for everyone
            $emailId = $idArray[$emailToSent];
        } elseif ($emailToSent >= 1 && $emailToSent <= 11) {
            // Day 1 to Day 11 - for the new user that select mini store as primary sales channel during onboarding
            if ($account['selected_mini_store']) $emailId = $idArray[$emailToSent];
        } elseif (($emailToSent == 12) || $emailToSent == 13) {
            // Day 12 & 13 - for every new user that haven't subscribed to any paid plan & not yealy subscription
            if (!$hasYearlySubscription) $emailId = $idArray[$emailToSent];
        } elseif ($emailToSent == 15) {
            // Day 15 - for the new user that select mini store as primary sales channel during onboarding 
            if ($account['selected_mini_store']) $emailId = $idArray[$emailToSent];
        }
        return Email::whereId($emailId)->first();
    }
}
