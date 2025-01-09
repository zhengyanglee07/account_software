<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SaleChannel extends Model
{
    public function accounts()
    {
        return $this->belongsToMany('App\Account');
    }

    public function products()
    {
        return $this->belongsToMany(UsersProduct::class, 'product_sale_channels', 'users_product_id', 'sale_channel_id');
    }

    public function cashback()
    {
        return $this->belongsToMany(Cashback::class, 'cashback_sale_channels', 'cashback_id', 'sale_channel_id');
    }

    public function isActiveSaleChannel($saleChannel, $accountId)
    {
        $saleChannel = $saleChannel === 'store' ? 'online-store' : $saleChannel;
        $saleChannels = SaleChannel::get();  // for existing users
        $account = Account::where('id', $accountId)->first();
        $selectedSaleChannels = $account->selected_salechannel === 0 ?  $saleChannels :  $account->activeSaleChannels()->get();
        $selectedType = [];
        foreach ($selectedSaleChannels as $selectedSaleChannel) {
            $selectedType[] = $selectedSaleChannel->type;
        }
        return in_array($saleChannel, $selectedType);
    }

    public function getActiveSaleChannels($accountId)
    {
        $saleChannels = SaleChannel::get();  // for existing users
        $account = Account::where('id', $accountId)->first();
        $selectedSaleChannels = $account->selected_salechannel === 0 ?  $saleChannels :  $account->activeSaleChannels()->get();
        $selectedType = [];
        foreach ($selectedSaleChannels as $selectedSaleChannel) {
            $selectedType[] = $selectedSaleChannel->type;
        }
        return $selectedType;
    }
}
