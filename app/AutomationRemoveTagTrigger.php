<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class AutomationRemoveTagTrigger
 *
 * @property int $id
 * @property int $processed_tag_id
 * @property-read ProcessedTag $processedTag
 *
 * @mixin \Eloquent
 */
class AutomationRemoveTagTrigger extends Model
{
    protected $fillable = [
        'automation_trigger_id',
        'processed_tag_id'
    ];

    protected $appends = ['description'];

    public function processedTag(): BelongsTo
    {
        return $this->belongsTo(ProcessedTag::class);
    }

    /**
     * @return string
     */
    public function getDescriptionAttribute(): string
    {
        $tagName = optional($this->processedTag)->tagName ?? '[empty name]';
        return "Remove \"$tagName\" tag";
    }
}
