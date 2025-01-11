<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AccountAddress extends Model
{
    protected $guarded = ['id'];

    protected $table = 'account_addresses';

    public function account()
    {
        return $this->belongsTo(Account::class, 'account_id');
    }
}
