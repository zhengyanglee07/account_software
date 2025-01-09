<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Auth;
use App\Account;

class image_gallery extends Model
{
    protected $guarded= [];

    protected $fillable = [
        'account_id',
        'name',
        'size',
        'width',
        'height',
        's3_name',
        's3_webp_name',
        's3_url',
        's3_webp_url',
        's3_webp_size',
        'local_path'
    ];

    public static function boot(){
        parent::boot();

        static::created(function($image_gallery){

            $accountPlan = Auth::user()->currentAccount()->accountPlanTotal;
            // dd($image_gallery);
            if($image_gallery->s3_webp_size!== null){
                $imageTotal = $image_gallery->size + $image_gallery->s3_webp_size;
            }else{
                $imageTotal =$image_gallery->size;
            }

            $accountPlan->total_image_storage += $imageTotal;
            $accountPlan->save();

         });

         static::deleted(function($image_gallery){
            $accountPlan =  Auth::user()->currentAccount()->accountPlanTotal;
            if($image_gallery->s3_webp_size!== null){

                $imageTotal = $image_gallery->size + $image_gallery->s3_webp_size;
            }else{
                $imageTotal =$image_gallery->size;
            }

            $accountPlan->total_image_storage -= $imageTotal;
            $accountPlan->save();

         });
    }
}
