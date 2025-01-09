<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Eloquent;

class AccountRole extends Model
{
   protected $guarded = [];
   
   public function accountPermissions() 
   {
	   return $this->belongsToMany(AccountPermission::class, 'role_account_permissions', 'role_id', 'account_permission_id');
   }
}
