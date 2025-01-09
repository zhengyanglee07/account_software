<?php

namespace App;

use Eloquent;
use App\SaleChannel;
use App\ProductRecommendation;
use App\App;
use App\NotificationSetting;
use App\EcommercePreferences;
use App\Subscription;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;
use Auth;


/**
 * App\Account
 *
 * @property int $id
 * @property string|null $accountRandomId
 * @property int|null $shoptype_id
 * @property string|null $api
 * @property string|null $password
 * @property string|null $domain
 * @property string|null $shopName
 * @property string|null $accountName
 * @property string|null $contactName
 * @property string|null $company
 * @property string|null $address
 * @property string|null $city
 * @property string|null $state
 * @property string|null $zip
 * @property string|null $email
 * @property string|null $timeZone
 * @property string|null $date
 * @property string|null $country
 * @property string|null $currency
 * @property string|null $industry
 * @property string|null $emailFooter
 * @property string|null $status
 * @property string $emailSent
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read funnel $funnel
 * @property-read Segment[] $segments
 * @property-read ShopType|null $shopType
 * @property-read Collection|User[] $user
 * @property-read int|null $user_count
 * @property-read Collection|ProcessedContact[] $processedContacts
 * @method static Builder|Account newModelQuery()
 * @method static Builder|Account newQuery()
 * @method static Builder|Account query()
 * @method static Builder|Account whereAccountName($value)
 * @method static Builder|Account whereAccountRandomId($value)
 * @method static Builder|Account whereAddress($value)
 * @method static Builder|Account whereApi($value)
 * @method static Builder|Account whereCity($value)
 * @method static Builder|Account whereCompany($value)
 * @method static Builder|Account whereContactName($value)
 * @method static Builder|Account whereCountry($value)
 * @method static Builder|Account whereCreatedAt($value)
 * @method static Builder|Account whereCurrency($value)
 * @method static Builder|Account whereDate($value)
 * @method static Builder|Account whereDomain($value)
 * @method static Builder|Account whereEmail($value)
 * @method static Builder|Account whereEmailFooter($value)
 * @method static Builder|Account whereEmailSent($value)
 * @method static Builder|Account whereId($value)
 * @method static Builder|Account whereIndustry($value)
 * @method static Builder|Account wherePassword($value)
 * @method static Builder|Account whereShopName($value)
 * @method static Builder|Account whereShoptypeId($value)
 * @method static Builder|Account whereState($value)
 * @method static Builder|Account whereStatus($value)
 * @method static Builder|Account whereTimeZone($value)
 * @method static Builder|Account whereUpdatedAt($value)
 * @method static Builder|Account whereZip($value)
 * @mixin Eloquent
 */
class Account extends Model
{
    use SoftDeletes;

    protected $fillable =
    [
        'accountRandomId',
        'user_id',
        'shoptype_id',
        'api',
        'subscription_plan_id',
        'subscription_status',
        'password',
        'domain',
        'shopName',
        'company',
        'address',
        'city',
        'state',
        'country',
        'zip',
        'industry',
        'emailSent',
        'refer_by',
        'has_email_affiliate_badge',
        'was_educated',
        'was_selected_goal',
        'selected_mini_store',
        'selected_salechannel',
        'terminate_cycle',
        'send_onboarding_email_at',
        'onboarding_email_to_sent',
        'is_onboarded',
    ];

    public function user()
    {
        return $this->belongsToMany(User::class)->withTimestamps();
    }

    public function owner()
    {
        return $this->belongsToMany(User::class)->withPivot(['account_role_id', 'role'])->orderBy('account_role_id');
    }

    public function saleChannels()
    {
        return $this->belongsToMany(SaleChannel::class);
    }

    public function activeSaleChannels()
    {
        return $this->belongsToMany(SaleChannel::class)->where('is_active', 1);
    }

    public function apps()
    {
        return $this->belongsToMany(App::class,'account_apps');
    }

    public function productRecommendations()
    {
        return $this->belongsToMany(ProductRecommendation::class);
    }

    public function productRecommendationsTypes()
    {
        return DB::table('account_product_recommendation')->where('account_id', $this->id)
            ->join('product_recommendations', 'account_product_recommendation.product_recommendation_id', '=', 'product_recommendations.id')
            ->select('account_product_recommendation.priority','product_recommendations.type')
            ->orderBy('priority')->get();
    }

    public static function saleChannelsType()
    {
        $currentAccountId = Auth::user()->currentAccountId;
        $account = Account::where('id', $currentAccountId)->first();
        if(!$account) return [];
        $selectedSaleChannels = $account->activeSaleChannels()->get();
        $selectedType = [];
        foreach ($selectedSaleChannels as $selectedSaleChannel) {
            $selectedType[] = $selectedSaleChannel->type;
        }
        if ($account->selected_salechannel === 0)  $selectedType = ['funnel', 'online-store', 'mini-store'];
        return $selectedType;
    }
    public function shopType()
    {
        return $this->belongsTo(ShopType::class, 'shoptype_id');
    }

    public function funnel()
    {
        return $this->hasOne(funnel::class);
    }

    public function segments(): HasMany
    {
        return $this->hasMany(Segment::class);
    }

    public function domains(): HasMany
    {
        return $this->hasMany(AccountDomain::class);
    }
    public function creditCardDetail()
    {
        return $this->hasOne(CreditCardDetail::class);
    }

    public function subscription()
    {
        return $this->hasOne(Subscription::class);
    }

    public function subscriptionInvoice()
    {
        return $this->hasMany(SubscriptionInvoice::class);
    }

    public function subscriptionPlan(): BelongsTo
    {
        return $this->belongsTo(SubscriptionPlan::class);
    }

    public function accountPlanTotal()
    {
        return $this->hasOne(AccountPlanTotal::class);
    }

    public function processedContacts(): HasMany
    {
        return $this->hasMany(ProcessedContact::class);
    }

    public function usersProduct()
    {
        return $this->hasMany(UsersProduct::class);
    }

    public function allCurrency()
    {
        return $this->hasMany(Currency::class);
    }

    public function hasPermission($permissions)
    {
        return $this->subscription->hasPermission($permissions);
    }

    public function permissionMaxValue($permissions)
    {

        return $this->subscription->hasPermission($permissions)->pivot->max_value;
    }

    public function products()
    {
        return $this->hasMany(UsersProduct::class);
    }


    public function shippingZone()
    {
        return $this->hasMany(ShippingZone::class);
    }

    public function taxSetting()
    {

        return $this->hasOne(Tax::class);
    }

    public function taxCountry()
    {
        return $this->hasManyThrough(TaxCountry::class, Tax::class, 'account_id', 'tax_setting_id');
    }

    public function notifiableSetting()
    {
        return $this->hasOne(NotifiableSetting::class);
    }

    public static function boot()
    {
        parent::boot();
        static::created(function ($account) {
            EcommercePreferences::create(['account_id' => $account->id]);
            NotificationSetting::create(['account_id' => $account->id]);
        });
    }

    public function easyParcel(): HasOne
    {
        return $this->hasOne(EasyParcel::class);
    }

    public function lalamove(): HasOne
    {
        return $this->hasOne(Lalamove::class);
    }

    public function automations()
    {
        return $this->hasMany(Automation::class);
    }

    public function customfields()
    {
        return $this->hasMany(peopleCustomFieldName::class);
    }

    public function referralCampaign(): HasMany
    {
        return $this->hasMany(ReferralCampaign::class);
    }

    public function affiliateMembers(): HasMany
    {
        return $this->hasMany(AffiliateMemberAccount::class);
    }

    public function affiliateMemberPaticipants(): HasMany
    {
        return $this->hasMany(AffiliateMemberParticipant::class);
    }

    public function affiliateMemberCommissions(): HasMany
    {
        return $this->hasMany(AffiliateMemberCommission::class);
    }

    public function affiliateMemberCommissionPayout(): HasMany
    {
        return $this->hasMany(AffiliateMemberCommissionPayout::class);
    }

    public function affiliateCampaign()
    {
        return $this->hasMany(AffiliateMemberCampaign::class);
    }

    public function location()
    {
        return $this->hasOne(Location::class)->ignoreAccountIdScope();
    }

    public function orders()
    {
        return $this->hasMany(Order::class)->where(function ($query) {
            $query->where('payment_process_status', 'Success')->orWhereNull('payment_process_status');
        });
    }

    public static function showUpsellHeader()
    {
        $account = Account::where('id', Auth::user()->currentAccountId)->first();
        $emailToSent = $account['onboarding_email_to_sent'];
        $isTimeToShow = Carbon::now() >= $account['send_onboarding_email_at'];
        $show = ($emailToSent >= 12 && $emailToSent <= 14) && $isTimeToShow;
        // Temp disabled upsell banner
        return $show && false;
    }

    public static function unfulfilledOrder()
    {
        $account = Account::find(Auth::user()->currentAccountId);
        if(!isset($account)) return collect();
        return $account->orders->where('fulfillment_status','!=','Fulfilled');
    }
    public static function pendingAffiliateMember()
    {
        $account = Account::find(Auth::user()->currentAccountId);
        if(!isset($account)) return collect();
        return $account->affiliateMemberPaticipants->where('status','pending');
    }
    public static function pendingAffiliateCommissions()
    {
        $account = Account::find(Auth::user()->currentAccountId);
        if(!isset($account)) return collect();
        return $account->affiliateMemberCommissions->where('status','pending');
    }
    public static function pendingAffiliatePayouts()
    {
        $account = Account::find(Auth::user()->currentAccountId);
        if(!isset($account)) return collect();
        return $account->affiliateMemberCommissionPayout->where('status','pending');
    }
    public function affiliateMemberSettings(): HasOne
    {
        return $this->hasOne(AffiliateMemberSetting::class);
    }
}
