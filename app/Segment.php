<?php

namespace App;

use App\Traits\SegmentContactsTrait;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * App\Segment
 *
 * @property int $id
 * @property string|null $reference_key
 * @property int $account_id
 * @property string $segmentName
 * @property array $conditions
 * @property int $people
 * @property string $contactsID
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection|Email[] $emails
 * @property-read int|null $emails_count
 * @method static Builder|Segment newModelQuery()
 * @method static Builder|Segment newQuery()
 * @method static Builder|Segment query()
 * @method static Builder|Segment whereAccountId($value)
 * @method static Builder|Segment whereContactsID($value)
 * @method static Builder|Segment whereCreatedAt($value)
 * @method static Builder|Segment whereId($value)
 * @method static Builder|Segment wherePeople($value)
 * @method static Builder|Segment whereSegmentName($value)
 * @method static Builder|Segment whereUpdatedAt($value)
 * @mixin Eloquent
 */
class Segment extends Model
{
    use SegmentContactsTrait;

    protected $fillable = [
        'reference_key',
        'account_id',
        'segmentName',
        'conditions',

        // Note: as of Aug 2020, people and contactsID columns
        //       are redundant with the usage of dynamic segment
        'people',
        // Note: as of Jun 2022, contactsID column
        //       are used for record contact entered segment for automation trigger
        'contactsID',
    ];

    protected $casts = [
        'conditions' => 'array'
    ];

    public function emails(): BelongsToMany
    {
        return $this->belongsToMany(Email::class);
    }

    public function adAudiences(): HasMany
    {
        return $this->hasMany(AdAudience::class);
    }

    public static function boot(){
        parent::boot();

        static::created(function($segment){
            $account = Account::find($segment->account_id);
            $accountPlan = $account->accountPlanTotal;
            $accountPlan->total_segment = $account->segments->count();
            $accountPlan->save();
         });

         static::deleted(function($segment){
            $account = Account::find($segment->account_id);
            $accountPlan = $account->accountPlanTotal;
            $accountPlan->total_segment = $account->segments->count();
            $accountPlan->save();
         });

    }
}
