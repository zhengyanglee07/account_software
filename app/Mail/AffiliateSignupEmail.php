<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;
use Symfony\Component\Mime\Email as MimeEmail;

class AffiliateSignupEmail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($data)
    {
        $domainURL = (app()->environment('local')) ? 'hypershapes.test' : ((app()->environment('staging')) ? 'salesmultiplier.asia' : 'hypershapes.com');
        $subdomain = 'affiliate.' . $domainURL;
        $this->email_data = $data;
        $this->email_data['subdomain'] = $subdomain;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        // disable email tracking on this email
        $this->withSymfonyMessage(function (MimeEmail $message) {
            $message->getHeaders()->addTextHeader('X-No-Track', Str::random(10));
        });
        return $this->markdown('email.affiliate.signup-email')->with(['email_data' => $this->email_data]);
    }
}
