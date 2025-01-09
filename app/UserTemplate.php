<?php

namespace App;

use Auth;
use Illuminate\Database\Eloquent\Model;

class UserTemplate extends Model
{
    protected $fillable =
    [
        'account_id',
        'name',
        'type',
        'element',
        'design',
	];

    protected $casts = [
        'design' => 'array',
    ];

	public static function insertDatetimeData()
	{
		$currentAccountId = Auth::user()->currentAccountId;
		$accountTimeZone = Account::findOrFail($currentAccountId)->timeZone;
		$allUserTemplate = UserTemplate::whereAccountId($currentAccountId)->get();
		foreach($allUserTemplate as $template) {
			$template['creation_datetime'] = 
				$template->created_at->timezone($accountTimeZone)->isoFormat('MMMM D, YYYY \\at h:mm a');
		}
		return $allUserTemplate;
	}
	
}
