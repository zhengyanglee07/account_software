<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\peopleCustomField
 *
 * @property int $id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @mixin \Eloquent
 */
class peopleCustomField extends Model
{
    //
    protected $fillable = [
        'account_id',
        'processed_contact_id',
        'people_custom_field_name_id',
        'custom_field_content',
    ];

    public function peopleCustomFieldName(): BelongsTo
    {
        return $this->belongsTo(peopleCustomFieldName::class);
    }
}
