<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\NotifiableSetting;
use Illuminate\Http\Request;
use Auth;
use Inertia\Inertia;
use Illuminate\Support\Facades\Validator;

class NotificationController extends Controller
{
    public function index()
    {

        $account = Auth::user()->currentAccount();
        $notifiableSetting = $account->notifiableSetting;
        $notifiableSetting->notification_email = $notifiableSetting->notification_email ?: $account->user()->first()->email;

        return Inertia::render('setting/pages/NotificationSetting', compact('notifiableSetting'));
    }

    public function saveOrderEmailNotification(Request $request)
    {
        // dd($request);
        $account = Auth::user()->currentAccount();
        $notifiableSetting = $account->notifiableSetting;
        $notifiableSetting->is_fulfill_order_notifiable = $request->setting['isFulfillNotifiable'];
        $notifiableSetting->save();
    }

    public function saveOrderNotificationEmail(Request $request)
    {
        $emailList = $request->emailList;
        if (isset($request->email)) array_push($emailList, $request->email);
        
        if (count($emailList) === 0) {
            return back()->withErrors([
                'message' => 'Please enter at least one email address',
            ]);
        }

        $validator = Validator::make(['emails' => $emailList], ['emails.*' => 'email:filter']);

        $errors = collect($validator->errors())->map(function ($errors, $key) use ($emailList) {
            $key = str_replace('emails.', '', $key);

            return collect($errors)->map(function ($error) use ($emailList, $key) {
                return $emailList[$key];
            });
        })->flatten();

        if ($errors->isNotEmpty()) {
            return back()->withErrors([
                'message' => 'Email address isn\'t valid: ' . $errors->implode(', ')
            ]);
        }

        if ($validator->passes()) {
            $account = Auth::user()->currentAccount();
            $account->notifiableSetting->update(['notification_email' => implode(', ', $emailList)]);
        }
    }
}
