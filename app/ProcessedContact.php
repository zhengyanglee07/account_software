<?php

namespace App;

use Carbon\Carbon;
use Eloquent;

use App\AccountPlanTotal;
use App\User;
use App\Order;
use App\SubscriptionPlan;
use App\Subscription;
use App\StoreCredit;
use App\ReferralCampaign;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Hashids\Hashids;
use App\Traits\ReferralCampaignTrait;
use App\Email;
use App\Models\AccessList;
use App\Models\CourseStudent;
use App\Models\ProductLesson;
use App\Notifications\CourseWelcomeNotification;
use App\Services\Checkout\CheckoutOrderService;
use App\Traits\AuthAccountTrait;
use Illuminate\Notifications\Notifiable;

/**
 * App\ProcessedContact
 *
 * @property int $id
 * @property string|null $contactRandomId
 * @property int $account_id
 * @property float|null $contactId
 * @property string|null $name
 * @property string|null $fname
 * @property string|null $lname
 * @property string|null $email
 * @property string|null $birthday
 * @property string|null $phone_number
 * @property string|null $address
 * @property string|null $gender
 * @property string|null $dateCreated
 * @property int|null $ordersCount
 * @property string|null $type
 * @property float|null $totalSpent
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read Collection|ProcessedAddress[] $addresses
 * @property-read Collection|peopleCustomField[] $peopleCustomFields
 * @property-read Collection|ProcessedTag[] $processed_tags
 * @property-read Collection|Note[] $notes
 * @method static Builder|ProcessedContact newModelQuery()
 * @method static Builder|ProcessedContact newQuery()
 * @method static Builder|ProcessedContact query()
 * @method static Builder|ProcessedContact whereAccountId($value)
 * @method static Builder|ProcessedContact whereAddress($value)
 * @method static Builder|ProcessedContact whereBirthday($value)
 * @method static Builder|ProcessedContact whereContactId($value)
 * @method static Builder|ProcessedContact whereContactRandomId($value)
 * @method static Builder|ProcessedContact whereCreatedAt($value)
 * @method static Builder|ProcessedContact whereDateCreated($value)
 * @method static Builder|ProcessedContact whereEmail($value)
 * @method static Builder|ProcessedContact whereFname($value)
 * @method static Builder|ProcessedContact whereGender($value)
 * @method static Builder|ProcessedContact whereId($value)
 * @method static Builder|ProcessedContact whereLname($value)
 * @method static Builder|ProcessedContact whereName($value)
 * @method static Builder|ProcessedContact whereOrdersCount($value)
 * @method static Builder|ProcessedContact wherePhoneNumber($value)
 * @method static Builder|ProcessedContact whereTotalSpent($value)
 * @method static Builder|ProcessedContact whereType($value)
 * @method static Builder|ProcessedContact whereUpdatedAt($value)
 * @mixin Eloquent
 */
class ProcessedContact extends Model
{
    use ReferralCampaignTrait, AuthAccountTrait, Notifiable;
    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    use SoftDeletes;
    protected $appends = ['displayName'];

    protected $with = ['orders'];

    protected $dispatchesEvent = [

        'created' => ContactCreated::class,

    ];

    protected $fillable = [
        'account_id',
        'contactId',
        'customer_id',
        'name',
        'fname',
        'lname',
        'email',
        'phone_number',
        'birthday',
        'gender',
        'address',
        'dateCreated',
        'ordersCount',
        'type',
        'totalSpent',
        'acquisition_channel',
        'contactRandomId'
    ];

    public function accessLists()
    {
        return $this->hasMany(AccessList::class);
    }

    public function courseStudents()
    {
        return $this->hasMany(CourseStudent::class);
    }

    public function lessonLog()
    {
        return $this->belongsToMany(ProductLesson::class, 'processed_contact_lessons', 'processed_contact_id', 'product_lesson_id')->withPivot('progress')->withTimestamps();
    }

    public function emails(): BelongsToMany
    {
        return $this->belongsToMany(Email::class)
            ->withPivot('status')
            ->withTimestamps();
    }

    public function addresses(): HasMany
    {
        return $this->hasMany(ProcessedAddress::class);
    }

    public function processed_tags(): BelongsToMany
    {
        return $this->belongsToMany(ProcessedTag::class)->withTimestamps();
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class)->where(
            function ($query) {
                $query->where('payment_process_status', 'Success')->orWhereNull('payment_process_status');
            }
        );
    }
    public function lastestOrders()
    {
        return $this->hasMany(Order::class)->where(
            function ($query) {
                $query->where('payment_process_status', 'Success')->orWhereNull('payment_process_status');
            }
        )->orderBy('created_at', 'DESC');
    }

    public function emailUnsub(): HasOne
    {
        return $this->hasOne(EmailUnsubscribe::class);
    }

    public function notes(): HasMany
    {
        return $this->hasMany(Note::class);
    }

    public function peopleCustomFields(): HasMany
    {
        return $this->hasMany(peopleCustomField::class);
    }

    public function storeCredits(): HasMany
    {
        return $this->hasMany(StoreCredit::class)->orderBy('id', 'DESC');
    }

    public function referralCampaigns()
    {
        return $this->belongsToMany(ReferralCampaign::class)->withTimestamps();
    }

    /**
     * Get the marketing email status of certain contact
     *
     * Three status (As of July 2020):
     * - Subscribed
     * - Unsubscribed
     * - Bounced
     *
     * @return string
     */
    public function marketingEmailStatus(): string
    {
        $isUnsub = $this->emailUnsub()->count() !== 0;
        $isBounced = EmailBounce::where('email_address', $this->email)->first();

        if ($isUnsub) {
            return 'Unsubscribed';
        }

        if ($isBounced) {
            return 'Bounced';
        }

        return 'Subscribed';
    }

    public function updateTotalPeople($processedContact)
    {
        $account = Account::find($processedContact->account_id);
        $accountPlanTotal = AccountPlanTotal::firstOrNew(['account_id' => $processedContact->account_id]);
        $accountPlanTotal->total_people = $account->processedContacts->count();
        // ++$accountPlanTotal->total_people;
        $accountPlanTotal->save();
    }
    public function updateJoinReferralCampaignsPoint()
    {
        $account = Account::find($this->account_id);
        if (!$account) return;
        ReferralCampaign::ignoreAccountIdScope()->where('account_id', $account->id)->where('funnel_id', null)->get()->map(function ($campaign) use ($account) {
            //email function
            $this->sendNotificationEmail($this, $campaign, 'register-success');

            $campaign->actions = $campaign->actions()->map(function ($el) {
                return $el->type;
            });
            if ($campaign->actions->contains('join')) {
                $eventPoint = $campaign->actions()->where('type', 'join')->first()->points;
                $campaign->processedContact()->attach([$this->id => ['point' => $eventPoint, 'is_joined' => true]]);
            }
        });
    }

    /**
     * Get a display name for contact
     *
     * Contact name is displayed with following precedence:
     * - If either contact fname or lname or both exist and not null,
     *   name will be shown in the form of (fname + lname)
     * - Else contact name will be shown as '-'
     */
    public function getDisplayNameAttribute(): string
    {
        return !$this->fname && !$this->lname
            ? '-'
            : trim($this->fname . ' ' . $this->lname);
    }

    public static function boot()
    {
        parent::boot();

        //activate when processed contact is created to count
        static::created(function ($processedContact) {
            $processedContact->updateTotalPeople($processedContact);
            $processedContact->updateJoinReferralCampaignsPoint();
        });

        //activate when proccesed contact is deleted to count
        static::deleted(function ($processedContact) {
            $processedContact->updateTotalPeople($processedContact);
        });
    }


    /**
     * Check is new participant or not to referral campaign
     */
    public function newParticipant($campaign)
    {
        $pivotData = DB::table('processed_contact_referral_campaign')->where(['processed_contact_id' => $this->id, 'referral_campaign_id' => $campaign->id])->first();
        return !$pivotData;
    }
    /**
     * Assign processed contact from funnel to referral campaign
     */
    public function assignReferralCampaign($campaign)
    {
        $referBy = null;
        if (array_key_exists('referral', request()->cookies->all())) {
            parse_str(request()->cookies->all()['referral'], $referral);
            $referralCode = $referral['invite'] ?? $referral['referral'];
            $referBy = ReferralCampaignTrait::decodeReferralCode($referralCode, $campaign->reference_key);
            if ($referBy === '') $referBy = null;
        };

        if ($this->newParticipant($campaign)) {
            $campaign->actions = $campaign->actions()->map(function ($el) {
                return $el->type;
            });
            $eventPoint = 0;
            $joined = false;
            if ($campaign->actions->contains('join')) {
                $eventPoint = $campaign->actions()->where('type', 'join')->first()->points;
                $joined = true;
            }
            $this->referralCampaigns()->syncWithoutDetaching([$campaign->id => ['point' => $eventPoint, 'is_joined' => $joined, 'refer_by' => $referBy]]);
            $this->sendNotificationEmail($this, $campaign, 'register-success');
        }
    }

    /**
     * Get processed contact unique referral code
     */
    public function referralCode($funnelId)
    {
        if ($funnelId) {
            $campaign = ReferralCampaign::where('account_id', $this->account_id)->where('funnel_id', $funnelId)->first();
            if ($campaign) {
                $hashids = new Hashids($campaign->reference_key, 6, 'ABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890');
                return $hashids->encodeHex($this->id);
            }
            return;
        }
        return;
    }

    public function sendCourseNotification($courseStudent)
    {
        $this->notify(new CourseWelcomeNotification($courseStudent));
    }
}
