<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Kalnoy\Nestedset\NodeTrait;

class AffiliateReferralLog extends Model
{
	use SoftDeletes;
    use NodeTrait;
    protected $guarded = [];
    protected $with = ['referFromAffiliate','subscription'];


    public function subscription(){
        return $this->belongsTo(Subscription::class);
    }

    public function referFromAffiliate(){
        return $this->belongsTo(AffiliateUser::class,'affiliate_id','id');
    }

    public function user(){
        return $this->belongsTo(User::class,'user_id','id');
    }

    public function getSublines(){
        $depth = 2;
        $levels = [];
        $traverse = function ($nodes, $prefix = '-', $i = -1,) use (&$levels, &$traverse) {
            $i = $i + 1;
            if (!($levels[$i] ?? null)) {
                $levels[$i] = [];
            }
            foreach ($nodes as $node) {
                $content = $prefix . ' ' . $node->id;
                $traverse($node->children, $prefix . '-', $i,);
                array_push($levels[$i], $content);
            }
        };
        $nodes = $this->descendants?->toTree();
        if($nodes){
            $traverse($nodes);
            $levels = array_filter(
                $levels,
                function ($key ) use(&$depth){
                    return $key < $depth;
                },
                ARRAY_FILTER_USE_KEY
            );
        }
        return $levels;

    }
}
