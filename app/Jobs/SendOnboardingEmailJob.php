<?php

namespace App\Jobs;

use App\Email;
use App\Services\EmailService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendOnboardingEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $email;
    public $receiverEmail;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Email $email, $receiverEmail)
    {
        $this->email = $email;
        $this->receiverEmail = $receiverEmail;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(EmailService $emailService)
    {
        $emailService->sendOnboardingEmail($this->email, $this->receiverEmail);
    }
}
