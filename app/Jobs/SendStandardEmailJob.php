<?php

namespace App\Jobs;

use App\Email;
use App\Services\EmailService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendStandardEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $email;

    /**
     * Create a new job instance.
     *
     * @param \App\Email $email
     */
    public function __construct(Email $email)
    {
        $this->email = $email;
    }

    /**
     * Execute the job.
     *
     * @param \App\Services\EmailService $emailService
     * @return void
     */
    public function handle(EmailService $emailService): void
    {
        info('------------------------------------------------------------Email Service-----------------------------------------------------');

        $emailService->sendStandardEmail($this->email);
    }
}
