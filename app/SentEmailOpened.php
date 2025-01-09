<?php

namespace App;

use App\Traits\BelongsToAccount;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use jdavidbakr\MailTracker\Model\SentEmail;

class SentEmailOpened extends Model
{
    protected $table = 'sent_emails_opened';

    protected $fillable = [
        'sent_email_id',
        'opened_at',
    ];
}
