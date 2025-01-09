<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\SubscriptionInvoice;
use App\Subscription;
use App\Account;
use Mail;
use App\Mail\SubscriptionInvoiceEmail;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class SubscriptionInvoiceController extends Controller
{
    /**
     * get random id 
     * 
     * @param $table string
     * 
     * @param $type string 
     */
    private function getRandomId($table, $type)
    {
        $condition = true;
        do {
            return $randomId = random_int(100000000001, 999999999999);
            $condition = DB::table($table)->where($type, $randomId)->exists();
        } while ($condition);
    }

    /**
     * create subscription invoice
     * 
     * @param $subscriptionDetail object
     * 
     * @param $event object 
     */
    public function createSubscriptionInvoice($subscriptionDetail, $event)
    {
        try {
            \DB::beginTransaction();

            $subscription = Subscription::where('subscription_id', $subscriptionDetail->subscription)->first();
            $stripeSubscription = $event->data->object->lines->data[0];
            $account = Account::find($subscription->account_id);
            $user = $account->owner;
            $lastestSubscriptionInvoice = SubscriptionInvoice::orderBy('id', 'desc')->first();
            //if this is the first invoice 
            if ($lastestSubscriptionInvoice === null) {
                $number = 1001;
            } else {
                $number = str_replace("HS", "", $lastestSubscriptionInvoice->invoice_number);
                $number = (int)$number + 1;
            }
            $invoiceNumber = 'HS' . $number;
            $startDate = Carbon::createFromTimestamp($stripeSubscription->period->start);
            $endDate = Carbon::createFromTimestamp($stripeSubscription->period->end);
            $formatStartDate = $startDate->isoFormat('DD MMM YYYY');
            $formatEndDate = $endDate->isoFormat('DD MMM YYYY');
            $description = $subscription->subscriptionPlan->plan . ' Plan' . ' (' . $formatStartDate . ' - ' . $formatEndDate . ')';
            $subscriptionInvoice = SubscriptionInvoice::create([
                'account_id' => $subscription->account_id,
                'subscription_id' => $subscriptionDetail->subscription,
                'invoice_number' => $invoiceNumber,
                'is_proration' => $stripeSubscription->proration,
                'plan_name' => $subscription->subscriptionPlan->plan . ' Plan',
                'description' => $description,
                'plan_start' => $startDate,
                'plan_end' =>  $endDate,
                'quantity' => $stripeSubscription->quantity,
                'total' => $subscriptionDetail->amount_paid / 100,
                'firstName' => $user[0]->firstName,
                'lastName' => $user[0]->lastName,
                'companyName' => $account->company,
                'address' => $account->address,
                'city' => $account->city,
                'state' => $account->state,
                'country' => $account->country,
                'zipCode' => $account->zip,
                'payment_method' => 'stripe',
                'reference_key' => $this->getRandomId('subscription_invoices', 'reference_key'),
                'paid_at' => date('Y/m/d H:i:s', $subscriptionDetail->status_transitions->paid_at),
                'status' => 'success',
            ]);
            
            // Delay 1 second to ensure SubscriptionInvoice created before email send
            sleep(1);

            Mail::to($user[0]->email)->send(new SubscriptionInvoiceEmail($subscriptionInvoice->id));
            \DB::commit();
        } catch (\Throwable $th) {
            \DB::rollBack();
            Log::error($th->getMessage());
            throw new \RuntimeException($th->getMessage());
        }
    }
}
