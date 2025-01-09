<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * Class AffiliateMemberReferral
 * @package App
 *
 * @mixin \Eloquent
 */
class AffiliateMemberReferral extends Model
{
    protected $fillable = [
        'affiliate_member_participant_id',
        'ecommerce_visitor_id',
    ];
}
