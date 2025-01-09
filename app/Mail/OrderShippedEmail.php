<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Services\EmailService;
use App\Account;
use App\AffiliateUser;
use App\User;
use App\Order;
use Symfony\Component\Mime\Email as MimeEmail;

class OrderShippedEmail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($data)
    {
        $this->email_data = $data;
    }

    private function getSubject(): string
    {
        return 'Order ' . $this->email_data['fulfillmentNumber'] . ' has been shipped';
    }

    /**
     * Build the message.
     *
     * @param \App\Services\EmailService $emailService
     * @return $this
     */
    public function build(EmailService $emailService):self
    {

        $account = Account::find($this->email_data['accountId']);
        $senderName = $account->company;
        $senderAddress = $emailService->formatTransactionalSenderAddress($senderName);
        $user = User::find($account->subscription->user_id);
        $affiliate = AffiliateUser::where('email',$user->email)->first();
        $order = Order::find($this->email_data['orderId']);
		$hasAffiliateBadge = $account->has_email_affiliate_badge ?? true;
        $this->withSymfonyMessage(static function (MimeEmail $message) use ($account) {
            $message->getHeaders()->addTextHeader('X-Account-ID', $account->id);
        });
        $order->currency = ($order->currency =='MYR') ? 'RM' : $order->currency;

        return $this
            ->from($senderAddress, $senderName ?: 'Hypershapes')
            ->subject($this->getSubject())
            ->view('emailTemplates.orderFulfillment.fulfillment')
            ->with([
                'order' => $order,
				'emailData' => $this->email_data,
				'affiliate' => $affiliate,
				'hasAffiliateBadge' => $hasAffiliateBadge
			]);





        return $this->view('view.name');
    }
}
