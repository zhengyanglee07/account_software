<?php

namespace App\Traits;

use App\Sender;
use Aws\Laravel\AwsFacade;

trait SenderTrait
{
    /**
     * Check if sender's email is verified previously
     *
     * @param $sender Sender|null
     * @return bool
     */
    private function senderStatusVerified($sender): bool
    {
        return $sender && strtolower($sender->status) === 'verified';
    }

    /**
     * Get TemplateName for sending SES custom verification email
     *
     * @return string
     */
    public function getSesCustomVerificationTemplateName(): string
    {
        $env = app()->environment();

        if ($env === 'production') {
            return 'hypershapes-email-verification-template';
        }

        if ($env === 'staging') {
            return 'hypershapes-email-verification-template--staging';
        }

        // Note: currently custom verification template redirect URL only supports
        //       *.dev locally. If you're using *.test locally then you might want to
        //       find a way to alias *.dev with the URL.
        if ($env === 'local') {
            return 'hypershapes-email-verification-template--local-dev';
        }

        return '';
    }

    /**
     * Check whether a sender email address is verified by Amazon SES
     *
     * @param string $email Email address to verify
     * @return bool
     */
    public function sesCheckEmailVerified(string $email): bool
    {
        $ses = AwsFacade::createClient('ses');
        $emailArray = explode(' ', $email);

        $result = $ses->getIdentityVerificationAttributes([
            'Identities' => $emailArray
        ]);
        $verificationAttributes = $result['VerificationAttributes'];

        return count($verificationAttributes) !== 0 &&
            $verificationAttributes[$email]['VerificationStatus'] === 'Success';
    }
}
