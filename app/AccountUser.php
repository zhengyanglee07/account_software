<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AccountUser extends Model
{
    protected $guarded = [];
    protected $table = 'account_user';

    public function calculateTotalRole($accountuser){
        $account = Account::find($accountuser->account_id);
        $totalRole = AccountUser::where('account_id',$account->id)->count();
        $accountPlanTotal = $account->accountPlanTotal;
        $accountPlanTotal->total_role_assign = $totalRole;
        $accountPlanTotal->save();
    }

    public static function boot(){
        parent::boot();

        if (app()->environment('testing')) {
            return;
        }

        static::created(function($accountuser){
            $accountuser->calculateTotalRole($accountuser);
         });

         static::deleted(function($accountuser){
            $accountuser->calculateTotalRole($accountuser);
         });

    }
}
