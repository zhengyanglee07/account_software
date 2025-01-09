<?php

namespace App;

use Auth;
use App\Account;
use Illuminate\Database\Eloquent\Model;

class EcommerceHeaderFooter extends Model
{
    protected $fillable = [
        'account_id',
        'name',
        'is_active',
        'is_header',
        'element',
        'design',
        'is_sticky',
        'reference_key'
    ];

    protected $casts = [
        'design' => 'array',
    ];

    public function accountId()
    {
        return Auth::user()->currentAccountId;
    }

    public function scopeOfActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOfType($query, $isHeader, $accountId)
    {
        $query->where([
            'is_header' => $isHeader,
            'account_id' => $accountId,
        ]);
    }
}
