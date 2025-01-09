<?php

namespace App\Models;

use App\UsersProduct;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductModule extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'product_id',
        'title',
        'order',
        'is_published',
    ];
    protected $with = ['lessons.processedContact'];

    public function product(): BelongsTo
    {
        return $this->belongsTo(UsersProduct::class);
    }

    public function lessons(): HasMany
    {
        return $this->hasMany(ProductLesson::class);
    }
}
