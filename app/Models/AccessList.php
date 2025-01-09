<?php

namespace App\Models;

use App\ProcessedContact;
use App\UsersProduct;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class AccessList extends Model
{
    use SoftDeletes;

    protected $guarded = ['id'];

    public function products(): BelongsTo
    {
        return $this->belongsTo(UsersProduct::class, 'product_id');
    }

    public function processedContact(): BelongsTo
    {
        return $this->belongsTo(ProcessedContact::class);
    }
}
