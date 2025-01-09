<?php

namespace App;

// use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Delyva extends Model
{
    // use HasFactory;
    protected $table = 'delyva_settings';

    protected $fillable = [
        'account_id',
        'delyva_company_code',
        'delyva_company_id',
        'delyva_user_id',
        'delyva_customer_id',
        'delyva_api',
        'delyva_selected',
        'item_type'
    ];

}