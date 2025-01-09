<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Auth;

/**
 * Class AutomationOrderSpentTrigger
 *
 * @property int $id
 * @property string $operator
 * @property float $spent
 *
 * @mixin \Eloquent
 */
class AutomationOrderSpentTrigger extends Model
{
    protected $fillable = [
        'automation_trigger_id',
        'operator',
        'spent'
    ];

    protected $appends = ['description'];

    /**
     * @return string
     */
    public function getDescriptionAttribute(): string
    {
        $user = Auth::user();

        if (!$user) {
            return '';
        }

        $defaultCurrency = Currency
            ::where([
                'account_id' => $user->currentAccountId,
                'isDefault' => 1
            ])
            ->firstOrFail()
            ->currency;

        return ucfirst($this->operator) . " $defaultCurrency {$this->spent}";
    }
}
