<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LandingPageForm extends Model
{
    protected $fillable = [
        'account_id',
		'title',
		'funnel_id',
		'landing_id',
		'element_id',
        'reference_key'
	];

	protected $table = 'landing_page_form';

    public function formContents(): HasMany
    {
        return $this->hasMany(LandingPageFormContent::class);
    }

    public function formLabels(): HasMany
    {
        return $this->hasMany(LandingPageFormLabel::class, 'landing_page_form_id');
    }

    /**
     * Get the page that owns the form.
     */
    public function page(): BelongsTo
    {
        return $this->belongsTo(Page::class, 'landing_id')->withTrashed();
    }

    public static function boot(){
        parent::boot();
    }
}
