<?php

namespace App\Services\Checkout;

use Auth;
use Illuminate\Support\Facades\Log;

class CustomerAccountService
{
    public function getCustomerAccountDetail()
    {
        if (Auth::guard('ecommerceUsers')->check() && isset(Auth::guard('ecommerceUsers')->user()->processed_contact_id)) {
            $ecommerceUser = Auth::guard('ecommerceUsers')->user();
            return [
                'user' => $ecommerceUser,
                'processedContact' => $ecommerceUser->processedContact,
                'address' => $ecommerceUser->addressBook[0],
            ];
        }
    }
}
