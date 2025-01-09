<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Tax extends Model
{
    protected $guarded= [];

    public function taxCountry(){
        return $this->hasOne(TaxCountry::class, 'tax_setting_id');
    }
}
