<?php

namespace App\Traits;

use App\User;

trait TestAccountTrait
{
	/**
     * @return \Illuminate\Support\Collection
     */
	public function getTestAccountIds(): \Illuminate\Support\Collection
    {
        return $this->getDisposableEmailAccountIds()->merge(User
            ::whereIn('email', [
                'gabriel@gmail.com',
                'steve@gmail.com',
                'tommy@gmail.com',
                'zhihong@gmail.com',
                'darren@gmail.com',
                'andy@gmail.com',
                'yeekang@gmail.com',
                'jaylyn@gmail.com',
                'cheejunwong@hotmail.com',
                'junwei@hotmail.com',
                'lewzhenyao00@gmail.com',
                'txuen006@gmail.com',
                'hypertesting007@gmail.com',
                'finnnelson006@gmail.com',
                'khawsora@gmail.com',
                'peilingkhoo@gmail.com',
                'loong414@hotmail.com',
                'sehwan1997@hotmail.com',
                'wongmeien98@gmail.com',
                'crystallai.0412@gmail.com',
                'charis@rocketlaunch.my',
                'steve@rocketlaunch.my',
				'brandeasy123@gmail.com',
				'iamstevelow369@gmail.com',
				'iamstevelow123@gmail.com',
				'hypershapes.tommy@gmail.com',
				'steve@hypershapes.com',
				'eztrade.my@gmail.com',
				'tommyliew59@gmail.com',
				'chanjunwei2000@gmail.com',
				'stjw0099@gmail.com',
				'jisintan27@gmail.com',
				'darrentesting007@gmail.com',
				'cheyektan@gmail.com',
				'jaylynyjx@gmail.com',
				'hw921861@gmail.com',
                'hypertesting007@hotmail.com',
                'brenda_1109@outlook.com',
                'darrenter123@gmail.com',
                'tzyong990707@gmail.com',
                'tzyong990808@gmail.com',
                'sehwan1997@gmail.com',
                'zhengyangz1007@gmail.com',
                'jiapengkhew@gmail.com',
            ])
            ->pluck('currentAccountId')
            ->filter())->filter(function ($e) {
				return $e;
			});
    }


	/**
     * @return \Illuminate\Support\Collection
     */
    private function getDisposableEmailAccountIds(): \Illuminate\Support\Collection
    {
        $domainsPath = storage_path() . "/json/domains.json";
        $disposableEmails = collect(json_decode(file_get_contents($domainsPath), true));
        return User::get()->filter(function ($value) use ($disposableEmails) {
            return $disposableEmails->contains(substr($value->email, strpos($value->email, "@") + 1));
        })->pluck('currentAccountId');
    }
}
