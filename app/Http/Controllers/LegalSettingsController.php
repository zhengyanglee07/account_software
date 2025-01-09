<?php

namespace App\Http\Controllers;

use App\Account;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Auth;
use App\Traits\AuthAccountTrait;
use App\LegalPolicy;
use App\LegalPolicyType;
use App\Sender;
use App\Traits\PublishedPageTrait;
use Inertia\Inertia;

class LegalSettingsController extends Controller
{
    use AuthAccountTrait, PublishedPageTrait;

    public function getLegalSetting()
    {
        $accountId = $this->getCurrentAccountId();
        $legalPolicy = LegalPolicy::with('legalPolicyType')->where('account_id', $accountId)->orderBy('type_id', 'asc')->get();
        $legalPolicyType = LegalPolicyType::all();
        $account = Account::with('user')->find($accountId);
        $account['sender'] = Sender::firstWhere('account_id', $accountId);

        return Inertia::render('setting/pages/LegalSettings', compact('legalPolicy', 'legalPolicyType', 'account'));
    }

    public function saveLegalSetting(Request $request)
    {
        $legalPolicyType = LegalPolicyType::firstWhere('type', $request->type);
        $legalPolicy = LegalPolicy::updateOrCreate(
            [
                'account_id' => $this->getCurrentAccountId(),
                'type_id' => $legalPolicyType->id,
            ],
            [
                'template' => $request->template,
            ],
        );
        $legalPolicy->save();
    }

    public function showLegalPolicy(Request $request)
    {
        $domainInfo = $this->getCurrentAccountId(true);
        $publishPageBaseData = $this->getPublishedPageBaseData();
        $legalPolicy = LegalPolicy::with('legalPolicyType')
            ->where('account_id', $domainInfo['accountId'])
            ->whereRelation('legalPolicyType', 'type', $request->type)
            ->whereNotNull('template')
            ->first();
        $pageType = $domainInfo['domain']->type;

        return Inertia::render('online-store/pages/LegalPolicy', array_merge($publishPageBaseData, [
            'legalPolicy' => $legalPolicy,
            'pageType' => $pageType,
        ]));
    }

    public function getLegalPolicy(Request $request)
    {
        $legalPolicy = LegalPolicy::with('legalPolicyType')
            ->where('account_id', $this->getCurrentAccountId())
            ->whereRelation('legalPolicyType', 'type', $request->type)
            ->whereNotNull('template')
            ->first();

        return response()->json([
            'legalPolicy' => $legalPolicy,

        ]);
    }
}
