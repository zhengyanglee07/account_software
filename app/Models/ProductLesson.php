<?php

namespace App\Models;

use App\ProcessedContact;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductLesson extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'title',
        'video_url',
        'description',
        'order',
        'is_published',
        'product_module_id',
        'parameter',
    ];

    public function module(): BelongsTo
    {
        return $this->belongsTo(ProductModule::class);
    }

    public function processedContact()
    {
        return $this->belongsToMany(ProcessedContact::class, 'processed_contact_lessons', 'product_lesson_id', 'processed_contact_id')
            ->select('processed_contacts.id', 'contactRandomId', 'fname', 'email')
            ->without('orders')
            ->withPivot(['id', 'progress', 'total_watched_duration', 'updated_at']);
    }
}
