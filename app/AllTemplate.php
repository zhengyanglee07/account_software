<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AllTemplate extends Model
{
    use SoftDeletes;

    protected $fillable = [
		'name',
		'type',
		'is_published',
        'element',
        'design',
        'image_path',
		'tags',
    ];

    protected $casts = [
        'design' => 'array',
    ];
}
