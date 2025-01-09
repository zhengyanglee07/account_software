<?php

namespace App;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Notifications\EcommerceRestPasswordNotification;
use Laravel\Sanctum\HasApiTokens;

class EcommerceAccount extends Authenticatable implements MustVerifyEmail
{
    use Notifiable, HasApiTokens;

    public function sendPasswordResetNotification($token)
    {
        $this->notify(new EcommerceRestPasswordNotification($token));
    }

    protected $fillable =
    [
        'account_id',
        'processed_contact_id',
        'full_name',
        'email',
        'password',
        'verification_code'
    ];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = ['email_verified_at' => 'datetime'];

    public function processedContact()
    {
        return $this->belongsTo(ProcessedContact::class, 'processed_contact_id');
    }
    public function addressBook()
    {
        return $this->hasMany(EcommerceAddressBook::class, 'ecommerce_account_id');
    }
    public function sellerInfo()
    {
        return $this->belongsTo(Account::class, 'account_id');
    }
}
