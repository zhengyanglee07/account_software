<?php

namespace App;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Auth;
/**
 * App\peopleCustomFieldName
 *
 * @property int $id
 * @property string $type
 * @property string $account_id
 * @property string $custom_field_name
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @method static Builder|peopleCustomFieldName newModelQuery()
 * @method static Builder|peopleCustomFieldName newQuery()
 * @method static Builder|peopleCustomFieldName query()
 * @method static Builder|peopleCustomFieldName whereId($value)
 * @method static Builder|peopleCustomFieldName whereAccountId($value)
 * @method static Builder|peopleCustomFieldName whereCustomFieldName($value)
 * @method static Builder|peopleCustomFieldName whereCreatedAt($value)
 * @method static Builder|peopleCustomFieldName whereUpdatedAt($value)
 * @mixin Eloquent
 */
class peopleCustomFieldName extends Model
{
    protected $fillable = [
        'account_id',
        'type',
        'custom_field_name',
    ];

    public static function boot(){
        parent::boot();

        static::created(function($customfield){
            $account = Auth::user()->currentAccount();
            $accountPlan = $account->accountPlanTotal;
            $accountPlan->total_customfield = $account->customfields->count();
            $accountPlan->save();
         });

         static::deleted(function($customfield){
            $account =  Auth::user()->currentAccount();
            $accountPlan = $account->accountPlanTotal;
            $accountPlan->total_customfield = $account->customfields->count();
            $accountPlan->save();
         });

    }
}
