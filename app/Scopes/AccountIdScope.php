<?php

namespace App\Scopes;

use Auth;
use App\AccountDomain;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class AccountIdScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $builder
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return void
     */
    public function apply(Builder $builder, Model $model)
    {
        if(Auth()->check()) {
            $accountId = Auth::user()->currentAccountId;
        } else {
            $accountId = optional(AccountDomain::getDomainRecord())->account_id;
        }
        $builder->where('account_id', $accountId);
    }

	/**
     * Extend the query builder with the needed functions.
     *
     * @param Builder $builder
     */
	public function extend(Builder $builder)
    {
        $builder->macro('ignoreAccountIdScope', function (Builder $builder) {
            return $builder->withoutGlobalScope($this);
        });
    }
}