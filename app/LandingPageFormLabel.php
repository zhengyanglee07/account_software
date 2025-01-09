<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class LandingPageFormLabel extends Model
{
    //
    protected $fillable = [
        'account_id',
        'landing_page_form_id',
        'landing_page_form_label',
    ];
}
