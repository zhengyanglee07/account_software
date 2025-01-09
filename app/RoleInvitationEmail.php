<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class RoleInvitationEmail extends Model
{
    protected $guarded = [];

    protected $table = 'role_invitation_emails';

    public function calculateTotalRoleInvite($roleInvitation){
        $account = Account::where('accountRandomId', $roleInvitation->account_random_id)->first();
        $totalRoleInvite = RoleInvitationEmail::where('account_random_id',$account->accountRandomId)->count();
        $accountPlanTotal = $account->accountPlanTotal;
        $accountPlanTotal->total_role_invite = $totalRoleInvite;
        $accountPlanTotal->save();
    }

    public static function boot(){
        parent::boot();

        static::created(function($roleInvitation){
            $roleInvitation->calculateTotalRoleInvite($roleInvitation);
         });

         static::deleted(function($roleInvitation){
            $roleInvitation->calculateTotalRoleInvite($roleInvitation);
         });

    }



}
