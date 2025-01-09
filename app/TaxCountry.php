<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TaxCountry extends Model
{    
    protected $with= ['taxCountryRegion'];

    protected $guarded= [];

    public $timestamps = false;

    public function taxCountryRegion(){
        return $this->hasMany(TaxCountryRegion::class,'country_id');
    }
}
