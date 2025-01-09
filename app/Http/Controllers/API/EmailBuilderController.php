<?php

namespace App\Http\Controllers\API;

use App\Email;
use App\EmailDesign;
use App\EmailDesignType;
use App\Http\Controllers\Controller;
use App\Services\RefKeyService;
use App\Traits\AuthAccountTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class EmailBuilderController extends Controller
{
    use AuthAccountTrait;

    public function getEmailTemplate()
    {
        $accountId = $this->getCurrentAccountId();
        $userTemplates = EmailDesign::whereAccountId($accountId)->where('email_design_type_id', 2)->get();
        $buildinTemplates = EmailDesign::whereNull('account_id')->where('email_design_type_id', 1)->where('template_status_id', 2)->get();

        return response()->json(compact('userTemplates', 'buildinTemplates'));
    }

    public function builderBaseData(Request $request)
    {
        $email = Email::where(['account_id' => $this->getCurrentAccountId(), 'reference_key' => $request->emailRef])->firstOrFail();
        $emailDesign = EmailDesign::where(['account_id' => $this->getCurrentAccountId(), 'reference_key' => $request->designRef])->firstOrFail();
        return response()->json([
            'email' => [
                'id' => $email->id,
                'reference_key' => $email->reference_key,
                'design_reference_key' => $emailDesign->reference_key,
                'preview' => $emailDesign->preview,
                'name' => $email->name,
            ]
        ]);
    }
    public function preview(Request $request)
    {
        $emailDesign = EmailDesign::where(function ($query) {
            $query->where('account_id', $this->getCurrentAccountId())->orWhereNull('account_id');
        })
            ->where('reference_key', $request->designRef)->firstOrFail();
        return response()->json(['html' => $emailDesign?->html]);
    }

    public function saveDesign(Request $request)
    {
        $emailDesign = EmailDesign::firstWhere('reference_key', $request->designRef);
        $emailDesign->update([
            'preview' => $request->design,
            'html' => $request->html,
        ]);
        return response()->noContent();
    }

    public function saveDesignAsTemplate(Request $request)
    {
        $accountId = $this->getCurrentAccountId();
        $emailDesignTypeId = EmailDesignType::where('name', 'user-template')->first()->id;

        $validatedInput = $request->validate([
            'name' => 'required|unique:email_designs,name,NULL,name,account_id,' . $accountId,
            'preview' => 'required',
            'html' => 'required'
        ]);
        $name = $validatedInput['name'];
        $preview = $validatedInput['preview'];
        $html = $validatedInput['html'];
        EmailDesign::create([
            'reference_key' => (new RefKeyService)->getRefKey(new EmailDesign()),
            'account_id' => $accountId,
            'email_design_type_id' => $emailDesignTypeId,
            'name' => $name,
            'preview' => json_encode($preview),
            'html' => $html,
        ]);

        return response()->json([
            'message' => 'Successfully created template'
        ]);
    }
}
