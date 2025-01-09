<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class AutomationSubmitFormTrigger
 *
 * @property int $id
 * @property int $automation_trigger_id
 * @property int $landing_page_form_id
 * @property-read LandingPageForm $landingPageForm
 * @mixin \Eloquent
 */
class AutomationSubmitFormTrigger extends Model
{
    protected $fillable = [
        'automation_trigger_id',
        'landing_page_form_id'
    ];

    protected $appends = ['description'];

    public function landingPageForm(): BelongsTo
    {
        return $this->belongsTo(LandingPageForm::class);
    }

    /**
     * @return string
     */
    public function getDescriptionAttribute(): string
    {
        $landingPageForm = $this->landingPageForm;

        $formName = !$landingPageForm
            ? 'Any'
            : (optional($landingPageForm)->title ?? '[empty name]');

        return "Submit \"$formName\" form";
    }
}
