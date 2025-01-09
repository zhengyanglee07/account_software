<?php

namespace App;

use App\Notifications\UserSignupVerifyEmailNotification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Notifications\DatabaseNotificationCollection;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Laravel\Sanctum\HasApiTokens;

/**
 * App\User
 *
 * @property int $id
 * @property string|null $firstName
 * @property string|null $lastName
 * @property string $email
 * @property Carbon|null $email_verified_at
 * @property string $password
 * @property string|null $phone
 * @property string|null $dialCode_iso2
 * @property int|null $currentAccountId
 * @property int|null $currentMessageId
 * @property int|null $currentEmailId
 * @property int|null $currentSenderId
 * @property string|null $remember_token
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string|null $customDomain1
 * @property string|null $customDomain2
 * @property-read Collection|Account[] $accounts
 * @property-read int|null $accounts_count
 * @property-read DatabaseNotificationCollection|DatabaseNotification[] $notifications
 * @property-read int|null $notifications_count
 * @method static Builder|User newModelQuery()
 * @method static Builder|User newQuery()
 * @method static Builder|User query()
 * @method static Builder|User whereCreatedAt($value)
 * @method static Builder|User whereCurrentAccountId($value)
 * @method static Builder|User whereCurrentEmailId($value)
 * @method static Builder|User whereCurrentMessageId($value)
 * @method static Builder|User whereCurrentSenderId($value)
 * @method static Builder|User whereDialCodeIso2($value)
 * @method static Builder|User whereEmail($value)
 * @method static Builder|User whereEmailVerifiedAt($value)
 * @method static Builder|User whereFirstName($value)
 * @method static Builder|User whereId($value)
 * @method static Builder|User whereLastName($value)
 * @method static Builder|User wherePassword($value)
 * @method static Builder|User wherePhone($value)
 * @method static Builder|User whereRememberToken($value)
 * @method static Builder|User whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class User extends Authenticatable implements MustVerifyEmail
{
    use Notifiable, SoftDeletes, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'subscription_plan_id',
        'firstName',
        'lastName',
        'email',
        'password',
        'mobileNo',
        'currentAccountId',
        'currentMessageId',
        'currentEmailId',
        'currentSenderId',
        'last_login_at',
        'webhook_id'
    ];

    protected $appends = ['displayName'];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = ['password', 'remember_token'];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = ['email_verified_at' => 'datetime'];

    // public function shopProfiles()
    // {
    //     return $this->belongsToMany('App\ShopProfile')->withTimestamps();
    // }

    public function accounts()
    {
        return $this->belongsToMany(Account::class)->withTimestamps()->withPivot(['account_role_id', 'role'])->orderBy('account_role_id');
    }

    public function getRoleFromCurrentAccount($accountId)
    {
        return $this->role($accountId)->name;
    }

    public function changeCurrentAccount($accountId)
    {
        $this->currentAccountId = $accountId;
        $this->save();
        return;
    }

    public function ownAccountId()
    {
        return $this->accounts->first()->id;
    }

    public function role($accountId)
    {
        $role = null;
        $account = AccountUser::where('user_id', $this->id)->where('account_id', $accountId)->first();
        if ($account !== null) {
            $role = AccountRole::find($account->account_role_id);
        }
        // return $this->belongsToMany('App\AccountRole', 'account_user', 'user_id', 'account_role_id');
        return $role;
    }

    public function getPermission()
    {
        $currentAccountId = $this->currentAccountId;
        return $this->role($currentAccountId)->accountPermissions->unique()->pluck('name');
    }

    /**
     * Obtain current account of logged in user, by using currentAccountId
     *
     * @return Collection|Model|\App\Account
     */
    public function currentAccount()
    {
        return $this->accounts()->find($this->currentAccountId);
    }

    public function getAccountById($id)
    {
        return $this->accounts()->find($id);
    }

    public function subscription(): HasOne
    {
        return $this->hasOne(Subscription::class);
    }

    /**
     * Send the email verification notification.
     *
     * @return void
     */
    public function sendEmailVerificationNotification(): void
    {
        $this->notify(new UserSignupVerifyEmailNotification);
    }

    public function getDisplayNameAttribute(): string
    {
        return !$this->firstName && !$this->lastName
            ? '-'
            : trim($this->firstName . ' ' . $this->lastName);
    }
}
