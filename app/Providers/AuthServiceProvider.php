<?php

namespace App\Providers;

use App\Automation;
use App\Email;
use App\funnel;
use App\Order;
use App\AccountPermission;
use App\Policies\AutomationPolicy;
use App\Policies\EmailPolicy;
use App\Policies\FunnelPolicy;
use App\Policies\OrderPolicy;
use App\Policies\ProcessedContactPolicy;
use App\Policies\SenderPolicy;
use App\ProcessedContact;
use App\Sender;
use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use App\Permission;
use Illuminate\Support\Facades\Schema;
use Auth;
class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        // 'App\Model' => 'App\Policies\ModelPolicy',
        Email::class => EmailPolicy::class,
        Sender::class => SenderPolicy::class,
        Automation::class => AutomationPolicy::class,
        funnel::class => FunnelPolicy::class,
        ProcessedContact::class => ProcessedContactPolicy::class,
        Order::class => OrderPolicy::class
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPermissionGate();
        $this->registerGenericAccountGate();
        $this->registerMasterAdminApiUserGate();
    }

    public function registerPermissionGate(){

        if (Schema::hasTable('permissions')) {
            $permissions = Permission::where('type','integer')->get();
            if($permissions !== null){
                foreach( $permissions as $permission){
                    Gate::define($permission->slug,function($user,$total_property,$permission){
                        $account = $user->currentAccount();
                        $accountPlanTotal = $account->accountPlanTotal;
                        if($total_property == null){
                            return $account->hasPermission($permission)!== null;
                        }else{
                            return $accountPlanTotal->$total_property < $account->permissionMaxValue($permission);
                        }
                    });
                }
                $permissionBoolean = Permission::where('type','boolean')->get();
                foreach($permissionBoolean as $permission){
                    Gate::define($permission->slug,function($user,$permissions){
                        $account = $user->currentAccount();
                        return $account->hasPermission($permissions)!==null;
                    });
                }
            }
        }

        Gate::define('viewWebTinker', function ($user = null) {
            return Auth::guard('masterAdmin')->user()!== null;
        });

        if (Schema::hasTable('account_permissions')) {
            $accountPermissions = AccountPermission::all();

            foreach($accountPermissions as $permission) {
                Gate::define($permission->name, function($user,$permit) {
                    return in_array($permit, $user->getPermission()->toArray());
                });
            }
        }
    }

    /**
     * This is generic account-level authorized action gate on model.
     * Use this if you're lazy to define individual policy
     */
    public function registerGenericAccountGate(): void
    {
        Gate::define('account', static function ($user, $model) {
            return isUserOwningModel($user, $model);
        });
    }

    /**
     * User gate for master admin API
     */
    public function registerMasterAdminApiUserGate(): void
    {
        Gate::define('access-ma', static function ($user) {
            return in_array($user->email, config('master-admin.allowedEmails'), true);
        });
    }


}
