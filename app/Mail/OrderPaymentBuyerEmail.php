<?php

namespace App\Mail;

use App\Account;
use App\AccountDomain;
use App\AffiliateUser;
use App\Order;
use App\Services\EmailService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\User;
use Symfony\Component\Mime\Email as MimeEmail;

class OrderPaymentBuyerEmail extends Mailable implements ShouldQueue
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
        return 'Order #' . $this->order->order_number . ' confirmed';
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
        $user = User::find($account->subscription->user_id);
        $affiliate = AffiliateUser::where('email', $user->email)->first();
        $hasAffiliateBadge = $account->has_email_affiliate_badge ?? true;
        $this->withSymfonyMessage(static function (MimeEmail $message) use ($account) {
            $message->getHeaders()->addTextHeader('X-Account-ID', $account->id);
        });
        $this->order->prefix = ($this->order->currency == 'MYR') ? 'RM' : $this->order->currency;

        $channelMap = [
            'Online Store' => 'online-store',
            'Mini Store' => 'mini-store',
            'Funnel' => '',
        ];

        $domain = optional(AccountDomain::ignoreAccountIdScope()->firstWhere(
            [
                'account_id' => $account->id,
                'type' => $channelMap[$this->order->acquisition_channel]
            ]
        ))->domain ?? '';

        return $this
            ->from($senderAddress, $senderName ?: 'Hypershapes')
            ->subject($this->getSubject())
            ->view('emailTemplates.orderPayment.buyer')
            ->with([
                'order' => $this->order,
                'affiliate' => $affiliate,
                'hasAffiliateBadge' => $hasAffiliateBadge,
                'domain' => $domain,
            ]);
    }
}
