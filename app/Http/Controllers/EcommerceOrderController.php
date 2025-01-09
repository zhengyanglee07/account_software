<?php

namespace App\Http\Controllers;

use Auth;
use App\Order;
use App\Account;
use Carbon\Carbon;
use App\UsersProduct;
use App\AccountDomain;
use App\EcommerceAccount;
use Illuminate\Http\Request;
use App\ProductReviewSetting;
use App\Traits\PublishedPageTrait;
use App\Traits\CurrencyConversionTraits;
use Inertia\Inertia;

class EcommerceOrderController extends Controller
{
    use PublishedPageTrait;
    use CurrencyConversionTraits;

    public function user()
    {
        return Auth::guard('ecommerceUsers')->user();
    }

    public function convertDatetimeToSelectedTimezone($datetime)
    {
        $accountTimeZone = Account::find($this->getCurrentAccountId())->timeZone;
        $datetime = new Carbon($datetime);
        return $datetime->timezone($accountTimeZone)->isoFormat('Do MMMM YYYY, h:mm:ss a');;
    }

    public function showOrderDashboard()
    {
        $publishPageBaseData = $this->getPublishedPageBaseData();
        $orders = $this->user()->processedContact->orders;
        foreach ($orders as $order) {
            $currency = $order->currency === 'MYR' ? 'RM' : $order->currency;
            $order->total = $currency . ' ' . $order->total;
        }
        // $headerFooterMiscSettings = $this->getHeaderFooterSection();

        return Inertia::render(
            'customer-account/pages/OrderDashboard',
            array_merge($publishPageBaseData, compact('orders'))
        );
    }

    public function getOrderDashboardData()
    {
        $publishPageBaseData = $this->getPublishedPageBaseData();
        $orders = $this->user()->processedContact->orders;
        return response()->json(array_merge($publishPageBaseData, compact('orders')));
    }

    public function showOrders($domain, $refKey)
    {
        $publishPageBaseData = $this->getPublishedPageBaseData();
        $pageName = 'Order Page';
        $order = Order::firstWhere('payment_references', $refKey);

        if (!isset($order)) abort(404);

        $order->converted_time = $this->convertDatetimeToSelectedTimezone($order->created_at);
        $order->order_details = $order->orderDetails;
        // $headerFooterMiscSettings = $this->getHeaderFooterSection();
        $reviews['product'] = UsersProduct::find($order->orderDetails->first()->users_product_id);
        $reviews['settings'] = ProductReviewSetting::firstWhere(['account_id' => $order->account_id]);
        $reviews['customer'] = Auth::guard('ecommerceUsers')->user();
        $reviews['name'] = $reviews['customer']->processedContact->fname;
        $reviews['writeReview'] = false;
        $reviews['purchased'] = false;
        if (Order::where(['payment_references' => $refKey, 'payment_status' => 'paid'])->where('payment_process_status', '!=', 'Pending')->first()) {
            $reviews['writeReview'] = (bool)$reviews['settings']->display_review;
            $reviews['purchased'] = true;
        }
        return Inertia::render(
            'customer-account/pages/OrderPage',
            array_merge($publishPageBaseData, compact('order', 'pageName', 'reviews'))
        );
    }
    public function getOrderData($refKey)
    {
        $publishPageBaseData = $this->getPublishedPageBaseData();
        $pageName = 'Order Page';
        $order = Order::firstWhere('payment_references', $refKey);

        if (!isset($order)) abort(404);

        $order->converted_time = $this->convertDatetimeToSelectedTimezone($order->created_at);
        $order->order_details = $order->orderDetails;
        // $headerFooterMiscSettings = $this->getHeaderFooterSection();
        $reviews['product'] = UsersProduct::find($order->orderDetails->first()->users_product_id);
        $reviews['settings'] = ProductReviewSetting::firstWhere(['account_id' => $order->account_id]);
        $reviews['customer'] = Auth::guard('ecommerceUsers')->user();
        $reviews['name'] = $reviews['customer']->processedContact->fname;
        $reviews['writeReview'] = false;
        $reviews['purchased'] = false;
        if (Order::where(['payment_references' => $refKey, 'payment_status' => 'paid'])->where('payment_process_status', '!=', 'Pending')->first()) {
            $reviews['writeReview'] = (bool)$reviews['settings']->display_review;
            $reviews['purchased'] = true;
        }
        return response()->json(
            array_merge(
                $publishPageBaseData,
                compact('order', 'pageName', 'reviews')
            )
        );
    }
}
