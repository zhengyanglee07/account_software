<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\LegalPolicyType;

class LegalPolicy extends Model
{
    protected $fillable = [
        'account_id',
        'type_id',
        'status',
        'template',
    ];

    public function legalPolicyType() {
        return $this->belongsTo(LegalPolicyType::class, 'type_id');
    }
}
