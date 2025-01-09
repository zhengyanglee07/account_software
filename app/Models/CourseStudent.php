<?php

namespace App\Models;

use App\Account;
use App\Notifications\CourseWelcomeNotification;
use App\ProcessedContact;
use App\Traits\AuthAccountTrait;
use App\User;
use App\UsersProduct;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Log;

class CourseStudent extends Model
{
    use SoftDeletes, AuthAccountTrait;

    protected $guarded = ['id'];

    public function products(): BelongsTo
    {
        return $this->belongsTo(UsersProduct::class, 'product_id');
    }

    public function processedContact(): BelongsTo
    {
        return $this->belongsTo(ProcessedContact::class);
    }

    public static function boot()
    {
        parent::boot();

        function sendCourseNotification($courseStudent)
        {
            $seller = User::firstWhere('currentAccountId', $courseStudent->processedContact->account_id);
            $notification = new CourseWelcomeNotification($seller, $courseStudent);
            ProcessedContact::find($courseStudent->processedContact->id)->notify($notification);
        }

        static::created(function ($courseStudent) {
            sendCourseNotification($courseStudent);
        });

        static::restored(function ($courseStudent) {
            sendCourseNotification($courseStudent);
        });
    }
}
