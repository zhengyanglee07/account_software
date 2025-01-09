<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\User;
use App\ProcessedContact;
use Carbon\Carbon;

class SignupAutomationEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $receiverAccount;
    public $receiverName;
    public $emailData;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($receiverAccount, $emailData)
    {
        $this->receiverAccount = $receiverAccount;
        $receiverUser = $receiverAccount->user->first();
        $this->receiverName = $receiverUser->firstName ?? '' . 
            (isset($receiverUser->firstName) ? ' ' . ($receiverUser->lastName ?? '')
                                    : ($receiverUser->lastName ?? ''));
        $this->emailData = $emailData;

    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build(): self
    {
        $senderEmail = app()->environment() == 'local' ? 'steve@gmail.com' : 'steve@rocketlaunch.my';
        $sender = User::firstWhere('email', $senderEmail);
        $senderName = $sender->firstName . ' ' . $sender->lastName;
        $senderEmailAddress = $sender->email;

        return $this
            ->from($senderEmailAddress, $senderName)
            ->subject($this->emailData['subject'])
            ->markdown('email.signupAutomation.'.$this->emailData['fileName'])
            ->with([
                'receiver' => $this->receiverAccount,
                'receiverName' => $this->receiverName,
            ]);
    }
}
