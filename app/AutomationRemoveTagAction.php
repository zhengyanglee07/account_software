<?php

namespace App;

use App\Interfaces\IAutomationStepKind;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class AutomationRemoveTagAction

 * @property-read ProcessedTag $processedTag
 * @mixin \Eloquent
 */
class AutomationRemoveTagAction extends Model implements IAutomationStepKind
{
    protected $fillable = [
        'automation_step_id',
        'processed_tag_id'
    ];

    /**
     * The relations to eager load on every query.
     *
     * @var array
     */
    protected $with = ['processedTag'];

    protected $appends = ['description'];

    public function processedTag(): BelongsTo
    {
        return $this->belongsTo(ProcessedTag::class);
    }

    public function getDescriptionAttribute(): string
    {
        $tagName = optional($this->processedTag)->tagName ?? '[empty name]';

        return "Remove \"$tagName\" tag";
    }
}
