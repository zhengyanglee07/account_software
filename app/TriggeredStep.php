<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class TriggeredStep
 *
 * @property int|null $processed_contact_id
 * @property-read Automation $automation
 * @property-read ProcessedContact|null $processedContact
 * @mixin \Eloquent
 */
class TriggeredStep extends Model
{
    use SoftDeletes;
    
    protected $fillable = [
        'automation_id',
        'processed_contact_id',
        'step',
        'batch',
        'execute_at'
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = ['step' => 'array'];

    public function automation(): BelongsTo
    {
        return $this->belongsTo(Automation::class);
    }

    public function processedContact(): BelongsTo
    {
        return $this->belongsTo(ProcessedContact::class);
    }
}
