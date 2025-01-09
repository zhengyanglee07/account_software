<?php

namespace App\Console\Commands;

use App\Email;
use App\Services\EmailService;
use App\Jobs\SendStandardEmailJob;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SendStandardEmail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'emails:send-standard';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send scheduled standard email';

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
    public function handle(EmailService $emailService)
    {
        $emails = Email::where('type', 'Standard')->get();

        if ($emails->count() === 0) {
            return;
        }

        info('---------------------------------Send Email---------------------------------');

        foreach ($emails as $email) {
            $schedule = $email->schedule;

            // skip if the email is not scheduled or is already sent
            if (!$schedule || strtolower($email->status) === 'sent') {
                continue;
            }

            info('schedule:- '.$schedule);

            $carbonSchedule = Carbon::createFromFormat('Y-m-d H:i:s', $schedule);
            $diffInMins = Carbon::now()->diffInMinutes($carbonSchedule, false);

            info('diff in min:- '.$diffInMins);

            // send email if current time is exactly or slightly over (negative diff)
            // the scheduled time
            if ($diffInMins <= 0) {
                $emailService->sendStandardEmail($email);
                info('-----dispatch-----');
            }
        }
        info('------------------------------------------------------------END-----------------------------------------------------');

    }
}
