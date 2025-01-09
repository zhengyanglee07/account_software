<?php

namespace App\Http\Controllers;
use Mail;
use App\Mail\AffiliateSignupEmail;


use Illuminate\Http\Request;

class AffiliateMailsController extends Controller
{
    public static function sendSignupEmail($name, $email,$verification_code){

        $data=[
            'name'=>$name,
            'verification_code'=>$verification_code,
            'url'=>'/affiliate/verify?code='.$verification_code,
        ];

        // dd($email);
        // $mail= str_replace("\xE2\x80\x8B", "", $email);

        Mail::to($email)->send(new AffiliateSignupEmail($data));
    }
}
