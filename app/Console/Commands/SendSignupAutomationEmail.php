<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Carbon\Carbon;
use App\Account;
use App\User;
use App\AccountUser;
use App\Jobs\SendSignupAutomationEmailJob;
use Illuminate\Support\Facades\DB;


class SendSignupAutomationEmail extends Command
{
    private const EMAIL_TO_SEND = [
        1 => [
            'subject' => 'Need Some Help?',
            'fileName' => 'dayOne',
        ],
        3 => [
            'subject' => 'Reminder: Schedule a meeting',
            'fileName' => 'dayThree',
        ],
        6 => [
            'subject' => 'Final 24 Hours...',
            'fileName' => 'daySix',
        ],
        14 => [
            'subject' => 'Wanna Increase Your Online Sales?',
            'fileName' => 'dayFourteen',
        ],
    ];
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'send:signup-automation-email';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'send signup automation email';

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

        if($this->isProduction) {
            $this->sendEmail();
        }
        else {
            $this->sendTestEmail();
        }

    }

    public function sendEmail()
    {
        $minusFourteenDays = Carbon::now()->subDays(14);
        $accounts = Account::whereDate('created_at', '>=', $minusFourteenDays)
                    ->whereDate('created_at', '<', Carbon::now())
                    ->get();
        info('-----------------------------------------------------NOW-----------------------------------------------------------------------------------');

        foreach($accounts as $account) {
            $diffInDays = Carbon::now()->diffInDays($account->created_at);
            $emailData = self::EMAIL_TO_SEND[$diffInDays] ?? null;

            $receiver = $account->user->first() ?? $this->getUser($account);

            if(!$emailData) {
                continue;
            }

            $emailData = $this->checkIfSent($receiver, $emailData) ?? null;

            if ($diffInDays && isset($receiver->email) && $emailData) {
                dispatch(new SendSignupAutomationEmailJob( $account, $receiver->email, $emailData));
                info('receiver: '. $receiver->email);
                info('success');
            }
        }

        info('--------------------------------------------------------END-----------------------------------------------------------------------------------');

    }

    /**
     * Test function for local/staging, 1 min = 1 day
     */
    public function sendTestEmail()
    {
        // $minusOneDay = Carbon::now()->subDays(1);
        // $minusThreeDays = Carbon::now()->subDays(3);
        // $minusSixDays = Carbon::now()->subDays(6);
        // $minusFourteenDays = Carbon::now()->subDays(14);

        $minusFourteenDays = Carbon::now()->subDays(14);
        $accounts = Account::whereDate('created_at', '>=', $minusFourteenDays)
                    ->get();
        info('-----------------------------------------------------Test-----------------------------------------------------------------------------------');

        foreach($accounts as $account) {

            //test in local / staging, 1day = 1min

            $diffInHours = Carbon::now()->diffInHours($account->created_at, false);
            $emailData = self::EMAIL_TO_SEND[abs($diffInHours)] ?? null;

            $receiver = $account->user->first() ?? $this->getUser($account);

            if(!$emailData) {
                continue;
            }

            $emailData = $this->checkIfSent($receiver, $emailData) ?? null;

            if ($diffInHours <= 0 && isset($receiver->email) && $emailData) {
                dispatch(new SendSignupAutomationEmailJob( $account, $receiver->email, $emailData));
                info('receiver: '. $receiver->email);
                info('success');
            }

        }

        info('--------------------------------------------------------TestEnd-----------------------------------------------------------------------------------');

    }

    /**
     * Check if the signup automation email is sent before
     */
    public function checkIfSent($receiver, $emailData)
    {
        $recipient = ' <'.$receiver->email.'>';

        $condition = DB::table('sent_emails')
            ->where('recipient', $recipient)
            ->where('subject', $emailData['subject'])
            ->get();

        if(count($condition)) {return false;}

        return $emailData;

    }

    public function getUser($account)
    {
        $accountUser = AccountUser::where([
            'role' => 'owner',
            'account_id' => $account->id
        ])->first();

        return User::withTrashed()->find($accountUser->user_id);
    }
}
