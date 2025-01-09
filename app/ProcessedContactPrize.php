<?php

namespace App;

use Illuminate\Database\Eloquent\Relations\Pivot;
use App\Observers\ProcessedContactPrizeObserver;

class ProcessedContactPrize extends Pivot
{
    protected $table = 'processed_contact_prize';

    public $timestamps = null;
    
    public static function boot(){
        parent::boot();

        ProcessedContactPrize::observe(ProcessedContactPrizeObserver::class);
    }
}
