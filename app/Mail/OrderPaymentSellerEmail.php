<?php

namespace App\Mail;

use App\Account;
use App\Order;
use App\ProcessedContact;
use App\Services\EmailService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Symfony\Component\Mime\Email as MimeEmail;

class OrderPaymentSellerEmail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $order;

    /**
     * Create a new message instance.
     *
     * @param \App\Order $order
     */
    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    private function getSubject(): string
    {
        return '[' . $this->order->seller->company . '] Order #' . $this->order->order_number . ' placed by ' . $this->order->processedContact->fname;
    }


    /**
     * Build the message.
     *
     * @param \App\Services\EmailService $emailService
     * @return $this
     */
    public function build(EmailService $emailService): self
    {
        $account = Account::find($this->order->account_id);
        $senderName = $account->company;
        $senderAddress = $emailService->formatTransactionalSenderAddress($senderName);
		$hasAffiliateBadge = $account->has_email_affiliate_badge ?? true;
        $this->withSymfonyMessage(static function (MimeEmail $message) use ($account) {
            $message->getHeaders()->addTextHeader('X-Account-ID', $account->id);
        });
        $this->order->prefix = ($this->order->currency =='MYR') ? 'RM' : $this->order->currency;

        return $this
            ->from($senderAddress, $senderName ?: 'Hypershapes')
            ->subject($this->getSubject())
            ->view('emailTemplates.orderPayment.sellerMjml')
            ->with([
				'order' => $this->order,
				'hasAffiliateBadge' => $hasAffiliateBadge
			]);
    }
}
