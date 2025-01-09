<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @deprecated 
 * Please use 'steps' property in automation model
 * 
 * Class AutomationStep
 *
 * @property int $id
 * @property string $type
 * @property string $kind
 * @property-read Automation $automation
 * @property-read AutomationDelay $automationDelay
 * @property-read AutomationSendEmailAction $automationSendEmailAction
 * @property-read AutomationAddTagAction $automationAddTagAction
 * @property-read AutomationRemoveTagAction $automationRemoveTagAction
 * @mixin \Eloquent
 */
class AutomationStep extends Model
{
    protected $fillable = [
        'account_id',
        'automation_id',
        'type',
        'kind'
    ];

    protected $appends = ['stepKind'];

    public function automation(): BelongsTo
    {
        return $this->belongsTo(Automation::class);
    }

    public function automationDelay(): HasOne
    {
        return $this->hasOne(AutomationDelay::class);
    }

    public function automationSendEmailAction(): HasOne
    {
        return $this->hasOne(AutomationSendEmailAction::class);
    }

    public function automationAddTagAction(): HasOne
    {
        return $this->hasOne(AutomationAddTagAction::class);
    }

    public function automationRemoveTagAction(): HasOne
    {
        return $this->hasOne(AutomationRemoveTagAction::class);
    }

    public function getStepKindAttribute()
    {
        return $this->{$this->kind};
    }
}
