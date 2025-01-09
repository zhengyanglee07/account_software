<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Auth;
use Carbon\Carbon;
use App\Account;
use App\Order;
use App\OrderSubscription;
use App\Traits\PublishedPageTrait;


class EcommerceSubscriptionController extends Controller
{
    use PublishedPageTrait;
    private function user()
    {
        return Auth::guard('ecommerceUsers')->user();
    }

    private function convertDatetimeToSelectedTimezone($datetime)
    {
        $accountTimeZone = Account::find($this->getAccountId())->timeZone;
        $datetime = new Carbon($datetime);
        return $datetime->timezone($accountTimeZone)->isoFormat('YYYY-MM-DD\\ h:mm:ss');
    }

    public function showSubscriptionDashboard()
    {
        $headerFooterMiscSettings = $this->getHeaderFooterSection();
        $orderSubscriptions = OrderSubscription::where('processed_contact_id', $this->user()->processed_contact_id)->get();
        $terminateCycle = Account::find($this->getAccountId())->terminate_cycle;
        $subscription = [];
        foreach ($orderSubscriptions as $orderSubscription) {
            $orders = Order::where('order_subscription_id', $orderSubscription->id)->orderBy('created_at', 'DESC')->get();
            foreach ($orders as $order) {
                $order->converted_time = $this->convertDatetimeToSelectedTimezone($order->created_at);
            }
            $obj = (object)[
                'id' => $orderSubscription->id,
                'subscriptionId' => $orderSubscription->subscription_id,
                'productName' => $orders[0]->orderDetails[0]->product_name,
                'status' => $orderSubscription->status,
                'nextPayment' => $orderSubscription->next_payment,
                'endPayment' => $orderSubscription->end_payment,
                'invoice' => $orders,
            ];
            array_push($subscription, $obj);
        }
        return view(
            'onlineStore/dashboard/subscriptionDashboard',
            compact('subscription', 'terminateCycle'),
            array_merge($headerFooterMiscSettings)
        );
    }
}
