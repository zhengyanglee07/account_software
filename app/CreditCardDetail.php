<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CreditCardDetail extends Model
{
    protected $fillable = [
        'account_id',
        'card_number',
        'card_holder_name',
        'card_types',
        'expire_date',
        'payment_method_id',
        'last_4_digit',
    ];

    public function accounts(){
        return $this->BelongsTo(Account::class);
    }
}
