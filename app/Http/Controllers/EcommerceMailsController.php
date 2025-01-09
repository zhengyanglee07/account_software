<?php

namespace App\Http\Controllers;

use Mail;
use App\Mail\EcommerceSignupEmail;

class EcommerceMailsController extends Controller
{
    public static function sendSignupEmail($ecommerceAccount, $sellerInfo)
    {
        $data = [
            'ecommerceAccount' => $ecommerceAccount,
            'sellerInfo' => $sellerInfo
        ];
        Mail::to($ecommerceAccount->email)->send(new EcommerceSignupEmail($data));
    }
}
