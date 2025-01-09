<?php

namespace App;

use App\Traits\Automations\HasTriggerKinds;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class AutomationTrigger
 *
 * @property int $id
 * @property string $kind
 * @property-read Model $triggerKind
 * @property-read AutomationSubmitFormTrigger $automationSubmitFormTrigger
 * @property-read AutomationPurchaseProductTrigger $automationPurchaseProductTrigger
 * @property-read AutomationDateBasedTrigger $automationDateBasedTrigger
 * @property-read AutomationAddTagTrigger $automationAddTagTrigger
 * @property-read AutomationRemoveTagTrigger $automationRemoveTagTrigger
 * @property-read AutomationOrderSpentTrigger $automationOrderSpentTrigger
 *
 * @mixin \Eloquent
 */
class AutomationTrigger extends Model
{
    use HasTriggerKinds;

    protected $table = 'automation_trigger';

    protected $fillable = [
        'automation_id',
        'trigger_id',
        'segment_id',
        'kind',
        'automation_provider_id'
    ];

    protected $with = ['trigger', 'segment'];

    protected $appends = ['triggerKind'];

    public function automation(): BelongsTo
    {
        return $this->belongsTo(Automation::class);
    }

    public function trigger(): BelongsTo
    {
        return $this->belongsTo(Trigger::class);
    }

    public function segment(): BelongsTo
    {
        return $this->belongsTo(Segment::class);
    }

    public function getTriggerKindAttribute()
    {
        return $this->{$this->kind};
    }
}
