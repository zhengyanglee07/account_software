<?php

namespace App;

use App\Traits\RefKeyRouteKeyNameTrait;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;

/**
 * App\EmailDesign
 *
 * @property int $id
 * @property string $reference_key
 * @property int $account_id
 * @property int|null $email_design_type_id
 * @property string $name
 * @property string $preview
 * @property string $html
 * @property string $mjml
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Email $email
 * @method static Builder|EmailDesign newModelQuery()
 * @method static Builder|EmailDesign newQuery()
 * @method static Builder|EmailDesign query()
 * @method static Builder|EmailDesign whereReferenceKey($value)
 * @method static Builder|EmailDesign whereAccountId($value)
 * @method static Builder|EmailDesign whereCreatedAt($value)
 * @method static Builder|EmailDesign whereId($value)
 * @method static Builder|EmailDesign whereName($value)
 * @method static Builder|EmailDesign wherePreview($value)
 * @method static Builder|EmailDesign whereHtml($value)*
 * @method static Builder|EmailDesign whereUpdatedAt($value)
 * @mixin Eloquent
 */
class EmailDesign extends Model
{
    use RefKeyRouteKeyNameTrait;

    protected $table = 'email_designs';

    protected $fillable = [
        'reference_key',
        'account_id',
        'email_design_type_id',
		'template_status_id',
        'name',
        'preview',
        'html',
        'mjml',
        'body_bg_color'
    ];

    /**
     * Obtain email that has this email design
     *
     * @return HasOne
     */
    public function email(): HasOne
    {
        return $this->hasOne(Email::class);
    }

	public function status()
	{
		return $this->belongsTo('App\TemplateStatus', 'template_status_id');
	}
}

