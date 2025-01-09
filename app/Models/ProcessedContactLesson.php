<?php

namespace App\Models;

use App\Traits\BelongsToAccount;
use Illuminate\Database\Eloquent\Model;

class ProcessedContactLesson extends Model
{
    use BelongsToAccount;
}
