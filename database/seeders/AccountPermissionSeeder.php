<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\AccountPermission;
use App\AccountRole;
use App\RoleAccountPermission;
use App\User;

class AccountPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Account Role
        $roles = [
            ['name' => 'owner'],
            ['name' => 'admin']
        ];
        foreach ($roles as $role)
        {
            AccountRole::create($role);
        }
        
        // Account Permissions
        $accountPermissions = [
            ['name' => 'view-order'],
            ['name' => 'edit-subscription'],
            ['name' => 'edit-payment'],
            ['name' => 'assign-role']
        ];
        foreach ($accountPermissions as $permission)
        {
            $accountPermission = AccountPermission::create($permission);
            RoleAccountPermission::create([
                'role_id' => 1,
                'account_permission_id' => $accountPermission->id,
            ]);
        }

    //     foreach(User::all() as $user){
    //         $accountuser = $user->accounts;
    //         foreach($accountuser as $account){
    //             if($account->pivot->role == 'owner'){
    //                 $account->pivot->account_role_id = 1;
    //                 $account->pivot->save();
    //             }
    //         }
    //    }
    }
}
