<?php

namespace App\Http\Controllers;

use Auth;
use App\Account;
use App\FacebookPixel;
use APP\Order;
use App\OrderDetail;
use App\ProcessedContact;
use App\Location;
use Inertia\Inertia;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;

class FacebookController extends Controller
{

    
    public function accountId()
    {
        return Auth::user()->currentAccountId;
    }

    public function facebookPixelSetting(){
        $account = Account::firstwhere([
            'id' => $this->accountId(),
        ]);
        $facebookPixelInfo = FacebookPixel::firstwhere([
            'account_id' => $this->accountId(),
         ]);
    // return view('facebook/facebookSettingPage', compact(['facebookPixelInfo', 'account']));
    return Inertia::render('app/pages/FacebookSettingPage',compact(['facebookPixelInfo', 'account']));
    }
    public function facebookPixelSaveSetting(Request $request){

        $facebookPixel = FacebookPixel::updateOrCreate([
            'account_id'   => $this->accountId(),
        ],[
            'pixel_id'     => $request->get('pixelID'),
            'api_token' => $request->get('accessToken'),
            'account_id'    => $this->accountId(),
            'facebook_selected'    => true,
        ]);
        return;
    }
}
