<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Note
 *
 * @property int $id
 * @property string $reference_key
 * @property int $processed_contact_id
 * @property string|null $content
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @method static Builder|Note whereCreatedAt($value)
 * @method static Builder|Note whereReferenceKey($value)
 * @method static Builder|Note whereProcessedContactId($value)
 * @method static Builder|Note whereContent($value)
 * @method static Builder|Note whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Note extends Model
{
    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = ['created_at_time'];

    protected $fillable = [
        'reference_key',
        'processed_contact_id',
        'content'
    ];

    public function getCreatedAtTimeAttribute()
    {
        return $this->created_at->format('h:i A');
    }
}
