<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
use App\EcommerceAccount;
use App\ProcessedContact;
use Illuminate\Support\Facades\Hash;
use App\Traits\PublishedPageTrait;
use Inertia\Inertia;

class EcommerceSettingsController extends Controller
{
    use PublishedPageTrait;

    public function user()
    {
        return Auth::guard('ecommerceUsers')->user();
    }

    public function showAccountSettings()
    {
        $publishPageBaseData = $this->getPublishedPageBaseData();
        $pageName = 'Account Settings Page';
        $ecommerceUser = $this->user();
        $ecommerceUser->full_name = $this->user()->processedContact->fname;
        $ecommerceUser->phone_number = $this->user()->processedContact->phone_number;
        return Inertia::render(
            'customer-account/pages/Profile',
            array_merge(
                $publishPageBaseData, 
                compact('ecommerceUser', 'pageName')
            ),
        );
    }

    public function saveUsername(request $request)
    {
        ProcessedContact::find($this->user()->processed_contact_id)->update(
            [
                'fname' => $request->name,
                'phone_number' => $request->phoneNumber
            ]
        );
    }

    public function savePassword(request $request)
    {
        if (Hash::check($request->oldPassword, $this->user()->password)) {
            EcommerceAccount::find($this->user()->id)->update(
                [
                    'password' => Hash::make($request->newPassword),
                ]
            );
            return response()->json(['status' => 'success']);
        } else {
            return response()->json(['status' => 'fail']);
        }
    }
}
