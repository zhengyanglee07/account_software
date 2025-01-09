<?php

namespace App;

use App\Traits\BelongsToAccount;
use Illuminate\Database\Eloquent\Model;

class IntegrationSetting extends Model
{
    use BelongsToAccount;
    protected $guarded = [];
}
