<?php

namespace App\Traits;

use Auth;
use App\Account;
use App\Scopes\AccountIdScope;

trait BelongsToAccount {

    protected static function bootBelongsToAccount()
    {
        static::addGlobalScope(new AccountIdScope);

        static::creating(function($model) {
            if(Auth()->check()) {
                $model->account_id = Auth::user()->currentAccountId;
            }
        });
    }

    public function account()
    {
        return $this->belongsTo(Account::class);
    }
}