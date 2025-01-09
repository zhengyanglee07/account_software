<?php

namespace App;

use App\Interfaces\IAutomationStepKind;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class AutomationSendEmailAction

 * @property-read Email $email
 * @mixin \Eloquent
 */
class AutomationSendEmailAction extends Model implements IAutomationStepKind
{
    protected $fillable = [
        'automation_step_id',
        'email_id'
    ];

    /**
     * The relations to eager load on every query.
     *
     * @var array
     */
    protected $with = ['email'];

    protected $appends = ['description'];

    public function email(): BelongsTo
    {
        return $this->belongsTo(Email::class);
    }

    public function getDescriptionAttribute(): string
    {
        $emailName = optional($this->email)->name ?? '[empty name]';

        return "Send \"$emailName\" email";
    }
}
