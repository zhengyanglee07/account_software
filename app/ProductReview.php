<?php

namespace App;

use App\Traits\BelongsToAccount;
use Illuminate\Database\Eloquent\Model;

class ProductReview extends Model
{
    use BelongsToAccount;
    protected $guarded = [];
}
