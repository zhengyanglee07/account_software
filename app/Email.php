<?php

namespace App;

use App\Models\EmailGroup;
use App\Traits\RefKeyRouteKeyNameTrait;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Carbon;
use jdavidbakr\MailTracker\Model\SentEmail;
use App\ProcessedContact;

/**
 * App\Email
 *
 * @property int $id
 * @property string $reference_key
 * @property int $account_id
 * @property string $type
 * @property string $name
 * @property string|null $schedule
 * @property int $email_status_id
 * @property int|null $sender_id
 * @property string|null $subject
 * @property string|null $preview_text
 * @property int|null $email_design_id
 * @property string|null $segment_id
 * @property string|null $queue_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Account|null $account
 * @property-read EmailDesign|null $emailDesign
 * @property-read Sender|null $sender
 * @property-read EmailStatus $emailStatus
 * @property-read string $status
 * @property-read string $timezone
 * @property-read string $email_design_reference_key
 * @method static Builder|Email newModelQuery()
 * @method static Builder|Email newQuery()
 * @method static Builder|Email query()
 * @method static Builder|Email whereReferenceKey($value)
 * @method static Builder|Email whereAccountId($value)
 * @method static Builder|Email whereCreatedAt($value)
 * @method static Builder|Email whereEmailDesignId($value)
 * @method static Builder|Email whereId($value)
 * @method static Builder|Email whereName($value)
 * @method static Builder|Email whereSchedule($value)
 * @method static Builder|Email whereEmailStatusId($value)
 * @method static Builder|Email whereType($value)
 * @method static Builder|Email wherePreviewText($value)
 * @method static Builder|Email whereQueueId($value)
 * @method static Builder|Email whereSegmentId($value)
 * @method static Builder|Email whereSenderId($value)
 * @method static Builder|Email whereSubject($value)
 * @method static Builder|Email whereUpdatedAt($value)
 * @mixin Eloquent
 */
class Email extends Model
{
    use RefKeyRouteKeyNameTrait;

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = ['status', 'timezone', 'email_design_reference_key','group'];

    protected $fillable = [
        'reference_key',
        'account_id',
        'type',
        'name',
        'schedule',
        'email_status_id',
        'segment_id',
        'sender_id',
        'email_design_id',
        'subject',
        'preview_text',
        'tag_id',
        'target',
        'sender_name',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'account_id');
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(Sender::class, 'sender_id');
    }

    public function emailDesign(): BelongsTo
    {
        return $this->belongsTo(EmailDesign::class, 'email_design_id');
    }

    public function emailStatus(): BelongsTo
    {
        return $this->belongsTo(EmailStatus::class, 'email_status_id');
    }

    public function sentEmails(): BelongsToMany
    {
        return $this->belongsToMany(SentEmail::class)
            ->withPivot('account_id')
            ->withTimestamps();
    }

    public function processedContacts(): BelongsToMany
    {
        return $this->belongsToMany(ProcessedContact::class)
            ->withPivot('status')
            ->withTimestamps();
    }

    public function emailGroups(): BelongsToMany
    {
        return $this->belongsToMany(EmailGroup::class,'email_group_email','email_id','email_group_id');
    }

    public function getStatusAttribute(): string
    {
        return $this->emailStatus->status;
    }

    public function getTimezoneAttribute()
    {
        return $this->account->timeZone ?? "Asia/Kuala_Lumpur";
    }

    public function getEmailDesignReferenceKeyAttribute()
    {
        return optional($this->emailDesign)->reference_key;
    }

    public function getGroupAttribute(){
        return $this->emailGroups()->pluck('name');
    }
}
