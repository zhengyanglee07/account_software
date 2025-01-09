<?php

namespace App;

use App\Segment;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AutomationEnterSegmentTrigger extends Model
{
    protected $fillable = [
        'automation_trigger_id',
        'segment_id'
    ];

    protected $appends = ['description'];

    public function segment(): BelongsTo
    {
        return $this->belongsTo(Segment::class);
    }

    public function getDescriptionAttribute(): string
    {
		$segmentName = optional($this->segment)->segmentName ?? 'Any';
        return "Enter \"$segmentName\" segment";
    }

}
