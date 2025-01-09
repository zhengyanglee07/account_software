<?php

namespace App\Http\Controllers\API;

use App\Account;
use App\Order;
use App\Http\Controllers\Controller;
use App\Traits\AuthAccountTrait;
use Carbon\Carbon;
use Illuminate\Http\Request;

class EcommerceOrderController extends Controller
{
    use AuthAccountTrait;

    public function getOrders(Request $request)
    {
        return response()->json([
            'orders' => $request->user()->processedContact->orders ?? [],
        ]);
    }

    public function getOrderDetail($refKey)
    {
        $order = Order::firstWhere('payment_references', $refKey);

        if (!isset($order)) abort(404);

        $order->converted_time = $this->convertDatetimeToSelectedTimezone($order->created_at);
        $order->order_details = $order->orderDetails;

        return response()->json(
            ['order' => $order]
        );
    }

    public function convertDatetimeToSelectedTimezone($datetime)
    {
        $accountTimeZone = Account::find($this->getCurrentAccountId())->timeZone;
        $datetime = new Carbon($datetime);
        return $datetime->timezone($accountTimeZone)->isoFormat('Do MMMM YYYY, h:mm:ss a');;
    }
}
