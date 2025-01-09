<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class LandingPageFormContent extends Model
{
    //
    protected $fillable = [
        'account_id',
        'landing_page_form_id',
        'landing_page_form_label_id',
        'landing_page_form_content',
		'visitor_id',
		'reference_key',
    ];

    public function landingPageFormLabel()
    {
        return $this->belongsTo(LandingPageFormLabel::class);
    }

    public function form()
    {
        return $this->belongsTo(LandingPageForm::class, 'landing_page_form_id');
    }

    public function visitor()
    {
        return $this->belongsTo(EcommerceVisitor::class, 'visitor_id');
    }

    protected function getProcessedContactIdAttribute()
    {
        return $this->visitor?->processed_contact_id;
    }

    public static function boot(){
        parent::boot();

        static::created(function($landingPageFormLabel){

           $account = Account::find($landingPageFormLabel->account_id);
           $landingPageForm = LandingPageFormContent::where('account_id',$account->id)->get();
           $uid = [];
           foreach($landingPageForm as $form){
                if(!in_array($form->reference_key,$uid)){
                    array_push($uid,$form->reference_key);
                }
           }
           $accountPlan = $account->accountPlanTotal;
           $accountPlan->total_form = count($uid);
           $accountPlan->save();

         });

         static::deleted(function($landingPageFormLabel){
            $account = Account::find($landingPageFormLabel->account_id);
            $landingPageForm = LandingPageFormContent::where('account_id',$account->id)->get();
            $uid = [];
            foreach($landingPageForm as $form){
                 if(!in_array($form->reference_key,$uid)){
                     array_push($uid,$form->reference_key);
                 }
            }
            $accountPlan = $account->accountPlanTotal;
            $accountPlan->total_form = count($uid);
            $accountPlan->save();

         });

    }
}
