<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\ProductSubscription;
use App\PaymentAPI;
use App\Order;
use App\UsersProduct;
use App\OrderSubscription;
use app\Account;
use App\Traits\CurrencyConversionTraits;
use Carbon\Carbon;


class ProductSubscriptionController extends Controller
{
    use CurrencyConversionTraits;

    private function stripe()
    {
        $stripeApi = PaymentAPI::where('account_id',$this->getAccountId())->where('payment_methods','Stripe')->first();
        $stripe = new \Stripe\StripeClient($stripeApi->secret_key);
        return $stripe;
    }

    private function getRandomId($table,$type)
    {
        $condition = true;
        do {
            return $randomId = random_int(100000000001, 999999999999);
            $condition = DB::table($table)->where($type, $randomId)->exists();

        } while ($condition);
    }

    private function createStripeProduct($product)
    {
        $stripeProduct = (object)[];

        $stripeProduct->id = UsersProduct::find($product->id)->product_id;
        if($stripeProduct->id === null){
            $stripeProduct = $this->stripe()->products->create(
                [
                    'name' => $product->productTitle,
                ]
            );
            UsersProduct::find($product->id)->update(['product_id' => $stripeProduct->id]);
        }

        return $stripeProduct->id;
    }

    public function createProductPrice($subscription,$charge,$currency)
    {
        $productPrice = $this->stripe()->prices->create(
            [
                'unit_amount' => $this->getCurrencyRange($charge * 100, strtoupper($currency)),
                'currency' => $currency,
                'recurring' =>
                [
                    'interval' => $subscription['interval'],
                    'interval_count' => $subscription['interval_count']
                ],
                'product' => $this->createStripeProduct($subscription->product),
            ]
        );
        $subscription->update(['price_id' => $productPrice->id]);
        return $productPrice->id;
    }

    public function getProductSubscriptionSetting()
    {
        $terminate_cycle = $this->getTerminateCycle();
        return view('settings/productSubscription',compact('terminate_cycle'));
    }

    public function saveProductSubscriptionSetting(request $request){
        Account::find($this->getAccountId())->update(['terminate_cycle' => $request->terminate_cycle]);
    }

    public function getArrangedProductSubscription()
    {
        $subscription = [];
        $orderSubscriptions = OrderSubscription::where('account_id',$this->getAccountId())->get();
        foreach($orderSubscriptions as $orderSubscription)
        {
            $orders = Order::where('order_subscription_id',$orderSubscription->id)->orderBy('created_at','DESC')->get();
            $orders[0]->currency = $orders[0]->currency === 'MYR' ? 'RM' : $orders[0]->currency;
            $object = (object)[
                'id' => $orderSubscription->id,
                'subscriber' => $orderSubscription->processedContact->fname,
                'email' => $orderSubscription->processedContact->email,
                'subscriptionName' => $orderSubscription->subscription_name,
                'status' => $orderSubscription->status,
                'productTitle' => $orders[0]->orderDetails[0]->product_name,
                'total' => $orders[0]->currency.' '. $this->priceFormater($orders[0]->total),
                'startDate' => $this->convertDatetimeToSelectedTimezone($orderSubscription->start_date),
                'lastPayment' => $this->convertDatetimeToSelectedTimezone($orderSubscription->last_payment),
                'nextPayment' => $this->convertDatetimeToSelectedTimezone($orderSubscription->next_payment),
                'endDate' =>$orderSubscription->end_payment === null ? null : $this->convertDatetimeToSelectedTimezone($orderSubscription->end_payment),
            ];
            array_push($subscription,$object);
        }
        return $subscription;
    }

    public function convertDatetimeToSelectedTimezone($datetime)
    {
        $accountTimeZone = Account::find($this->getAccountId())->timeZone;
        $datetime = new Carbon($datetime);
        return $datetime->timezone($accountTimeZone)->isoFormat('YYYY-MM-DD\\ H:mm:ss');
    }
    public function getTerminateCycle()
    {
        return Account::find($this->getAccountId())->terminate_cycle;
    }

    public function showProductSubscriptionDashboard()
    {
        $subscription = $this->getArrangedProductSubscription();
        $terminateCycle = $this->getTerminateCycle();
        return view('productSubscription',compact('subscription','terminateCycle'));
    }

    public function saveProductSubscription($request,$product)
    {
        foreach($request->savedSubscriptionArray as $productSubscription)
        {
            if(!is_numeric($productSubscription['id'])) $productSubscription['id'] = null;
            if($productSubscription['changes'])
            {
                $productSubscription = ProductSubscription::updateOrCreate(
                    ['id' => $productSubscription['id']],
                    [
                        'display_name' => $productSubscription['display_name'],
                        'users_products_id' => $product->id,
                        'description' => $productSubscription['description'],
                        'type' => $productSubscription['type'],
                        'discount_rate' => $productSubscription['discount_rate'],
                        'capped_at' => $productSubscription['capped_at'],
                        'amount' => $productSubscription['amount'],
                        'interval_count' => $productSubscription['interval_count'],
                        'interval' => $productSubscription['interval'],
                        'expiration' => $productSubscription['expiration'],
                        'expiration_cycle' => $productSubscription['expiration_cycle'],
                        'reference_key' => $productSubscription['reference_key'] ?? $this->getRandomId('productSubscriptions','reference_key'),
                    ]
                );
            }
        }
    }

    public function getProductSubscription($id)
    {
        $productSubscription = ProductSubscription::find($id);
        return response()->json([$productSubscription]);
    }

    public function getAllProductSubscription($id)
    {
        $subscriptions = UsersProduct::find($id)->subscription;
        return $subscriptions;
    }

    public function deleteProductSubscription($id)
    {
        ProductSubscription::find($id)->delete();
    }
}
