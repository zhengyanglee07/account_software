<?php

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

if(!function_exists('permissionChecker')){

    function permissionChecker($permission,$totalName){
        $isPermit= Gate::allows($permission,[$totalName,$permission]);
        return $isPermit;
    }
}

if(!function_exists('checkFeaturePermission')){

    function checkFeaturePermission($permission){
            $isPermit= Gate::allows($permission,$permission);
            return $isPermit;
    }
}

if(!function_exists('isUserOwningModel')) {
    /**
     * Simple assertion to check whether user's currentAccountId === model's account_id.
     * All policies created by me will be using this assertion func.
     * Throws RuntimeException if account_id not found on model.
     *
     * If multi-account feature implemented, you can change logic here
     * accordingly to cater for the the usage.
     *
     * @param $user
     * @param \Illuminate\Database\Eloquent\Model $model
     * @return bool
     */
    function isUserOwningModel($user, Model $model) {
        if (!\Schema::hasColumn($model->getTable(), 'account_id')) {
            throw new \RuntimeException('Model ' . get_class($model) . ' provided has no account_id column');
        }

        return $user->currentAccountId === $model->account_id;
    }
}
