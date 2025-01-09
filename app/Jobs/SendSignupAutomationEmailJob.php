<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Mail\SignupAutomationEmail;
use Illuminate\Support\Facades\Mail;

class SendSignupAutomationEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $receiverEmail;
    public $account;
    public $emailData;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct( $account, $receiverEmail, $emailData)
    {
        $this->receiverEmail = $receiverEmail;
        $this->account = $account;
        $this->emailData = $emailData;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if (!isset($this->receiverEmail) && !$this->emailData) {
            return false;
        }
        $mailer = app()->environment('local') ? 'smtp' : 'ses-markt';
        Mail::mailer($mailer)->to($this->receiverEmail)->send(new SignupAutomationEmail($this->account, $this->emailData));
    }


}
