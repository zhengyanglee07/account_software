<?php

namespace App\Mail;

use App\Email;
use App\Services\MjmlComponentService;
use App\Services\MjmlRendererService;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;
use Symfony\Component\Mime\Email as MimeEmail;

class StandardTestEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $email;
    public $html;

    /**
     * Create a new message instance.
     *
     * @param Email $email
     */
    public function __construct(Email $email)
    {
        $this->email = $email;
    }

    /**
     * Build the message.
     *
     * @param MjmlRendererService $mjmlRendererService
     * @param MjmlComponentService $mjmlComponentService
     * @return $this
     * @throws \Exception
     */
    public function build(
        MjmlRendererService $mjmlRendererService,
        MjmlComponentService $mjmlComponentService
    ) {
        $senderAddress = $this->email->sender->email_address;
        $senderName = $this->email->sender_name;

        $this->withSymfonyMessage(function (MimeEmail $message) {
            $message->getHeaders()->addTextHeader('X-No-Track', Str::random(10));
        });

        // $this->html = $mjmlRendererService->render(
        //     $mjmlComponentService->parse($this->email->emailDesign->mjml)
        // );

        $this->html = $this->email->emailDesign->html;

        return $this
            ->from($senderAddress, $senderName)
            ->subject($this->email->subject)
            ->view('email.preview');
    }
}
