<?php

namespace App;

use Auth;
use App\AccountDomain;
use App\Traits\BelongsToAccount;
use Illuminate\Database\Eloquent\Model;

class MiniStoreChecklistHeader extends Model
{
    use BelongsToAccount;
    protected $guarded = [];

    public static function header()
    {
        $currentAccountId = Auth::user()->currentAccountId;
        $header = MiniStoreChecklistHeader::where('account_id', $currentAccountId)->first();
        if($header) $header->domainUrl = AccountDomain::storeDomain(true);
        return $header;
    }

    public static function showHeader()
    {
        $currentAccountId = Auth::user()->currentAccountId;
        $account = Account::where('id', $currentAccountId)->first();
        $show =  (boolean)$account->selected_mini_store;
        return $show;
    }

    public static function showCheckListHeader()
    {
        $currentAccountId = Auth::user()->currentAccountId;
        $account = Account::where('id', $currentAccountId)->first();
        $checkListHeader =  MiniStoreChecklistHeader::where('account_id', $account->id)->first();
        $show = $checkListHeader ? $account->selected_mini_store && $checkListHeader->is_show : $account->selected_mini_store;
        return $show;

    }
}
