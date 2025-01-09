<?php

namespace App\Http\Controllers;

use Auth;
use App\Account;
use App\MiniStore;
use App\Delyva;
use App\FacebookPixel;
use App\App;
use App\SaleChannel;
use App\UsersProduct;
use App\AccountDomain;
use Illuminate\Http\Request;
use App\MiniStoreChecklistHeader;
use App\Http\Controllers\Controller;
use Inertia\Inertia;

class ApplicationController extends Controller
{
    public function accountId()
    {
        return Auth::user()->currentAccountId;
    }

    public function getAllApplication()
    {
        $saleChannels = SaleChannel::get();
        $apps = App::get();
        $account = Account::where('id', $this->accountId())->first();
        $selectedSaleChannels =  $account->activeSaleChannels()->get();
        $selectedType = [];
        $selectedAppType = [];
        foreach ($selectedSaleChannels as $selectedSaleChannel) {
            $selectedType[] = $selectedSaleChannel->type;
        }

        $appsArray = ['Delyva' => 'delyva', 'EasyParcel' => 'easyparcel', 'Lalamove' => 'lalamove', 'FacebookPixel' => 'facebook'];
        foreach ($appsArray as $model => $value) {
            $appInfo = ('\\App\\' . $model)::where('account_id', $account->id)->first();
            if (isset($appInfo) && $appInfo[$value . '_selected']) {
                $selectedAppType[] = $value;
            }
        }

        $domainsInfo = AccountDomain::where('account_id', $this->accountId())->where('type', '!=', null)->get();
        return Inertia::render('app/pages/AllApplicationPage', compact(['saleChannels', 'selectedType', 'domainsInfo', 'apps', 'selectedAppType']));
    }

    public function setupMiniStore()
    {
        $account = Account::where('id', $this->accountId())->first();
        if (in_array('mini-store', Account::saleChannelsType())) $account->update(['selected_mini_store' => true,]);
        $header = MiniStoreChecklistHeader::where('account_id', $account->id)->first();
        if ($header) {
            $header->update([
                'header_index' => 0,
                'checked_list' => [],
                'is_show' => true,
            ]);
        }
        return response()->json([
            'message' => 'Success',
        ]);
    }
    public function selectApp(Request $request)
    {
        $account = Account::where('id', $this->accountId())->first();
        $delyvaInfo = Delyva::where('account_id', $account->id)->first();
        $facebookPixelInfo = FacebookPixel::where('account_id', $account->id)->first();
        $appType = $request->currentChannel;
        //Delyva Service
        if ($appType == "delyva") {
            if (!$delyvaInfo) {
                return response()->json([
                    'message' => "Delyva Service Activated, Redirect to Setup",
                    'link' => "/delyva/setup"
                ]);
            }
            $isDelyvaActivated = in_array('delyva', $request->selectedApp);
            $delyvaInfo->update([
                'delyva_selected' => $isDelyvaActivated,
            ]);
            $result = $isDelyvaActivated ? "Activated" : "Deactivated";
            return response()->json([
                'message' => "Delyva Service {$result}",
            ]);
        } elseif ($appType == "facebook") {
            if (!$facebookPixelInfo) {
                return response()->json([
                    'message' => "Facebook Pixel Service Activated, Redirect to Setup",
                    'link' => "/facebook/setting"
                ]);
            }
            $isActivated = in_array('facebook', $request->selectedApp);

            $facebookPixelInfo->facebook_selected = $isActivated;
            $facebookPixelInfo->save();

            $result = $isActivated ? "Activated" : "Deactivated";
            return response()->json([
                'message' => "Facebook Service {$result}",
            ]);
        }

        // Easyparcel / Lalamove service
        $appsName = [
            'easyparcel' => 'EasyParcel',
            'lalamove' => 'Lalamove',
        ];
        $appInfo = ('\\App\\' . $appsName[$appType])::where('account_id', $account->id)->first();
        if (!$appInfo) {
            return response()->json([
                'message' => $appsName[$appType] . "Service app activated. Redirecting to setup page",
                'link' => "/apps/" . $appType
            ]);
        }
        $isAppActivated = in_array($appType, $request->selectedApp);
        $appInfo->update([
            $appType . '_selected' => $isAppActivated,
        ]);
        $result = $isAppActivated ? "Activated" : "Deactivated";

        return response()->json([
            'message' => $appsName[$appType] . " Service {$result}",
        ]);
    }
    public function selectSaleChannel(Request $request)
    {
        $saleChannels = SaleChannel::get();
        $account = Account::where('id', $this->accountId())->first();
        $account->update(['selected_salechannel' => true,]);
        $selectedSaleChannelId = [];
        foreach ($request->selectedSaleChannel as $selected) {
            foreach ($saleChannels as $saleChannel) {
                if ($saleChannel->type === $selected)
                    $selectedSaleChannelId[] = $saleChannel->id;
            }
        }
        $account->activeSaleChannels()->sync($selectedSaleChannelId);
        $miniStore = MiniStore::where('account_id', $account->id)->first();
        if (!in_array('mini-store', Account::saleChannelsType())) $account->update(['selected_mini_store' => false,]);
        if (in_array('mini-store', Account::saleChannelsType()) && !$miniStore) {
            MiniStore::create([
                'account_id' =>  $account->id,
                'name' =>  $account->company,
                'image' => $account->company_logo ?? 'https://media.hypershapes.com/images/hypershapes-favicon.png',
            ]);
        }

        if (count($request->selectedSaleChannel) === 3) {
            $message = 'Sale channel is set to Mini Store, Onlin Store, and Sale Funnel!';
        } elseif (count($request->selectedSaleChannel) === 0) {
            $message = 'No set any sale channels!';
        } elseif (count($request->selectedSaleChannel) === 1) {
            if ($request->selectedSaleChannel[0] === 'funnel')   $message = 'Sale channel is set to Sale Funnel!';
            if ($request->selectedSaleChannel[0] === 'online-store')   $message = 'Sale channel is set to Online Store!';
            if ($request->selectedSaleChannel[0] === 'mini-store')   $message = 'Sale channel is set to Mini Store!';
        } else {
            $str = '';
            foreach ($request->selectedSaleChannel as $key => $selected) {
                if ($key == 1) $str .= ' and ';
                if ($selected === 'funnel') $str .= 'Sale Funnel';
                if ($selected === 'online-store') $str .= 'Online Store';
                if ($selected === 'mini-store') $str .= 'Mini Store';
            }
            $message = 'Sale channel is set to ' . $str . '!';
        }
        return response()->json([
            'message' => $message,
        ]);
    }

    public function boarded()
    {
        $account =  Account::find($this->accountId());
        if (!$account->is_onboarded) {
            $account->is_onboarded = true;
            $account->save();
        }
    }

    public function saveSaleChannel(Request $request)
    {
        $account = Account::where('id', $this->accountId())->first();
        $saleChannelType = SaleChannel::firstwhere('type', $request->saleChannelType);
        $saleChannel[] = $saleChannelType->id;
        $account->activeSaleChannels()->sync($saleChannel);
        $account->update(['was_selected_goal' => true, 'selected_salechannel' => true, 'selected_mini_store' => $saleChannelType->type == 'mini-store' ? true : false,]);
        $account->update(['is_onboarded' => true]);

        $miniStore = MiniStore::where('account_id', $account->id)->first();
        if ($saleChannelType->type == 'mini-store' &&  !$miniStore) {
            MiniStore::create([
                'account_id' =>  $account->id,
                'name' =>  $account->company,
                'image' => $account->company_logo ?? 'https://media.hypershapes.com/images/hypershapes-favicon.png',
            ]);
        }
        if ($saleChannelType->type === 'funnel') $this->boarded();
        return response()->json([
            'message' => "Sale channel is selected successfully",
        ]);
    }

    public function saveProductSaleChannel($request, $product)
    {
        $saleChannels = SaleChannel::get();
        $selectedSaleChannelId = [];
        foreach ($request->input('details.sale_channels') as $selected) {
            foreach ($saleChannels as $saleChannel) {
                if ($saleChannel->type === $selected)
                    $selectedSaleChannelId[] = $saleChannel->id;
            }
        }
        $product->saleChannels()->sync($selectedSaleChannelId);
    }

    public function selectFeature($feature)
    {
        $app = App::firstWhere('type', $feature);
        $account = Account::where('id', $this->accountId())->first();
        $isExisted = $account->apps()->where('type', $feature)->exists();
        $account->apps()->{$isExisted ? 'detach' : 'attach'}($app->id);
        return response($app);
    }
}
