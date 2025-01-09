<?php

namespace App\Mail;

use App\SubscriptionInvoice;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Carbon\Carbon;

class SubscriptionInvoiceEmail extends Mailable implements ShouldQueue
{
    use Queueable;
    public $subscriptionInvoice;
    public $title;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($subscriptionInvoiceId)
    {
        $subscriptionInvoice = SubscriptionInvoice::find($subscriptionInvoiceId);
        $date = Carbon::parse($subscriptionInvoice->plan_start)->isoFormat('YYYY_MM_DD');
        $title = $subscriptionInvoice->plan_name.'_'.$date;
        $this->subscriptionInvoice = $subscriptionInvoice;
        $this->title = $title;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this
        ->subject('Subscription Invoice')
        ->view('emailTemplates.subscriptionInvoice.subscriptionInvoice')
        ->with([
            'subscriptionInvoice' => $this->subscriptionInvoice,
            'title' => $this->title
        ]);
    }
}
