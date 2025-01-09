<?php

namespace App;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Carbon;

/**
 * App\ProcessedTag
 *
 * @property int $id
 * @property int $account_id
 * @property string $tagName
 * @property string $typeOfTag
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection|ProcessedContact[] $processedContacts
 * @property-read int|null $processed_contacts_count
 * @method static Builder|ProcessedTag newModelQuery()
 * @method static Builder|ProcessedTag newQuery()
 * @method static Builder|ProcessedTag query()
 * @method static Builder|ProcessedTag whereAccountId($value)
 * @method static Builder|ProcessedTag whereCreatedAt($value)
 * @method static Builder|ProcessedTag whereId($value)
 * @method static Builder|ProcessedTag whereTagName($value)
 * @method static Builder|ProcessedTag whereTypeOfTag($value)
 * @method static Builder|ProcessedTag whereUpdatedAt($value)
 * @mixin Eloquent
 */
class ProcessedTag extends Model
{
    protected $table = 'processed_tags';

    protected $fillable = ['tagName', 'typeOfTag', 'account_id'];

    public function processedContacts(): BelongsToMany
    {
        return $this->belongsToMany(ProcessedContact::class)->withTimestamps();
    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeContactTag(Builder $query): Builder
    {
        return $query->where('typeOfTag', 'contact');
    }
}
