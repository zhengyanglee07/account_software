<?php

namespace App;

use Auth;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Class Automation
 *
 * @property array $steps_order
 * @property-read int $repeat
 * @property-read int $executed
 * @property-read AutomationStatus $automationStatus
 * @property-read Collection|AutomationTrigger[] $automationTriggers
 * @property-read Collection|AutomationStep[] $automationSteps
 * @mixin \Eloquent
 */
class Automation extends Model
{
    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = ['status'];

    protected $fillable = [
        'account_id',
        'automation_status_id',
        'reference_key',
        'name',
        'steps_order',
        'steps',
        'repeat',
        'executed'
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'steps_order' => 'array',
        'steps' => 'array',
    ];

    public function automationStatus(): BelongsTo
    {
        return $this->belongsTo(AutomationStatus::class);
    }

    /**
     * @deprecated
     * 
     * This relation is deprecated in favour of 'steps'
     * Kindly use 'steps' property instead of this 'automationSteps' relation
     *
     * @return HasMany
     */
    public function automationSteps(): HasMany
    {
        return $this->hasMany(AutomationStep::class);
    }

    public function automationTriggers(): HasMany
    {
        return $this->hasMany(AutomationTrigger::class);
    }

    public function triggeredContacts(): BelongsToMany
    {
        return $this
            ->belongsToMany(ProcessedContact::class)
            ->withPivot('triggered_at');
    }

    /**
     * @param int|string $referenceKey
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model
     */
    public static function findByRefKey($referenceKey)
    {
        return self
            ::where('reference_key', $referenceKey)
            ->firstOrFail();
    }

    /**
     * @param int|null $accountId
     * @param int|string $referenceKey
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model
     */
    public static function findByAccountIdAndRefKey(?int $accountId, $referenceKey)
    {
        return self
            ::where('account_id', $accountId)
            ->where('reference_key', $referenceKey)
            ->firstOrFail();
    }

    /**
     * Appends automation's status to model
     *
     * @return string
     */
    public function getStatusAttribute(): string
    {
        return $this->automationStatus->name;
    }

    public static function boot(){
        parent::boot();

        static::created(function($automation){
            $account = Auth::user()->currentAccount();
            $accountPlan = $account->accountPlanTotal;
            $accountPlan->total_automation = $account->automations->count();
            $accountPlan->save();
         });

         static::deleted(function($automation){
            $account = Auth::user()->currentAccount();
            $accountPlan = $account->accountPlanTotal;
            $accountPlan->total_automation = $account->automations->count();
            $accountPlan->save();
         });
    }
}
