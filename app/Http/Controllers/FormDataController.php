<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\Services\DataFormatValidationService;

use App\Account;
use App\LandingPageForm;
use App\LandingPageFormContent;
use App\LandingPageFormLabel;
use App\ProcessedAddress;
use App\ProcessedContact;
use App\ProcessedTag;
use App\peopleCustomField;
use App\peopleCustomFieldName;
use App\EcommerceVisitor;
use App\Traits\ReferralCampaignTrait;

use Mail;
use App\Mail\LandingPageFormSubmissionEmail;

use App\Events\LandingFormSubmitted;
use App\Events\TagAddedToContact;

use Inertia\Inertia;
use App\Exports\FormContentsExport;
use App\Traits\AuthAccountTrait;
use Maatwebsite\Excel\Facades\Excel;

use App\Traits\DatatableTrait;

class FormDataController extends Controller
{
	use ReferralCampaignTrait, AuthAccountTrait, DatatableTrait;

    public function accountId()
    {
        return Auth::user()->currentAccountId;
    }

    public function generateRefKey()
	{
		do {
			$randomId = random_int(100000000001, 999999999999);
			$condition = LandingPageForm::where('reference_key', $randomId)->exists();
		} while ($condition);
		return $randomId;
	}

    /**
     * Display all the forms in the user's account
     */
    public function index(Request $request)
    {
        $paginatedForms = $this->paginateRecords(
            LandingPageForm::with([
                'page:id,name,is_landing,reference_key,deleted_at',
                'formContents',
            ])->where('account_id', $this->accountId()),
            $request->query(),
            ['title', 'submit_count'],
            function($form) {
                $form['submission'] = $form['formContents']?->groupBy('reference_key')?->count() ?? 0;
                $form['pageName'] = $form?->page?->name ?? "N/A";
                $form['editorUrl'] = $form->page?->is_landing !== null 
                    ? "/builder/" . ($form->page?->is_landing ? "landing" : "page") . "/" . $form->page?->reference_key . "/editor" 
                    : null;
                $form['isDeleted'] = $form->page?->deleted_at !== null;
                return $form;
            },
        );

        return Inertia::render('people/pages/Forms', compact('paginatedForms'));
    }

	public function show(LandingPageForm $form)
	{
        $formName = $form->title;
        $referenceKey = $form->reference_key;
        $conditions = [
            'account_id' => Auth::user()->currentAccountId,
            'landing_page_form_id' => $form->id
        ];
        $labelNames = LandingPageFormLabel::where($conditions)->pluck(
            'landing_page_form_label', 'id'
        );
        $dbFormContents = LandingPageFormContent::where($conditions)->get()->groupBy('reference_key');

        return Inertia::render('people/pages/FormDetails', compact(
			'labelNames',
            'formName',
            'dbFormContents',
            'referenceKey',
		));
	}

    public function rename(Request $request, LandingPageForm $form)
    {
        $form->update([
            'title' => $request->title,
        ]);
    }

	public function create(Request $request)
	{
		$conditions = [
			'account_id' => $request->accountId ?? $this->getCurrentAccountId(),
			'funnel_id' => $request->funnelId,
			'landing_id' => $request->landingId,
			'element_id' => $request->elementId
		];

		$form = LandingPageForm::where($conditions)->first();

		// TODO(darren) refactor here
		if($form === null) {
			$form = LandingPageForm::where(array_merge(
				$conditions,
				[
					'title' => $request->formTitle,
					'element_id' => null,
				]
			))->first();

			if($form === null) {
				$form = LandingPageForm::create(array_merge(
					$conditions,
					[
						'title' => $request->formTitle,
						'element_id' => $request->elementId,
						'reference_key' => $this->generateRefKey()
					]
				));
			} else {
				$form->update([
					'element_id' => $request->elementId,
					'reference_key' => $this->generateRefKey()
				]);
			}
		} else {
            $form->update([
                'title' => $request->formTitle
            ]);
        }

		return response()->json([
			'referenceKey' => $form->reference_key
		]);
	}

	public function submit(Request $request, DataFormatValidationService $dataFormatValidationServ)
	{
        $form = LandingPageForm::whereReferenceKey($request->refKey)->first();
		$accountId = $form->account_id;
		$referenceKey = bin2hex(random_bytes(6));
        $cookie = null;
        $tracking = false;

        $tags = $request->input('actionAfterSubmit.tags');
		$emailSettings = $request->input('actionAfterSubmit.email');
		$updatePeopleProfile = $request->input('actionAfterSubmit.updatePeopleProfile');

		$peopleAddressFields = [
			'address1', 'address2', 'city', 'state', 'country', 'zip'
		];
        $peopleProfileFields = [
			'fname', 'lname', 'email', 'phone_number', 'birthday', 'gender'
		];

        $accountPlanTotal = Account::find($accountId)->accountPlanTotal;
        $accountPlanTotal->total_form +=1;
		$accountPlanTotal->save();

		$form->title = $request->formName;
		$form->submit_count++;
		$form->save();

		if($updatePeopleProfile) {
            $emailAddress = collect($updatePeopleProfile)->first(function ($value) {
                    return $value['updatePeopleProfile'] == "email";
                })['value'] ?? null;
            $phoneNumber = collect($updatePeopleProfile)->first(function ($value) {
                    return $value['updatePeopleProfile'] == "phone_number";
                })['value'] ?? null;
            // if both email & phone present, select based on identifier
            if (!is_null($emailAddress) && !is_null($phoneNumber)) {
                $people = $request->identifier === 'email'
                    ? ProcessedContact::firstOrCreate([
                        'account_id' => $accountId,
                        'email' => $emailAddress,
                    ])
                    : ProcessedContact::firstOrCreate([
                        'account_id' => $accountId,
                        'phone_number' => $phoneNumber,
                    ]);
            } else {
                $people = $emailAddress
                    ? ProcessedContact::firstOrCreate([
                        'account_id' => $accountId,
                        'email' => $emailAddress,
                    ])
                    : ProcessedContact::firstOrCreate([
                        'account_id' => $accountId,
                        'phone_number' => $phoneNumber,
                    ]);
            }

            if ($people->contactRandomId == null) {
                $people->update([
                    'acquisition_channel' => $request->funnelId ? "Funnel" : "Online Store",
                    'contactRandomId' => random_int(1000000001, 9999999999)
                ]);
            }

            if($tags) {
                $this->attachTags($tags, array($people->id));
            }

            $peopleAddress = ProcessedAddress::updateOrCreate([
                'processed_contact_id' => $people->id
            ]);

            if (isset($request->visitorId)) {
                $conversion = new Request([
                    'visitorId' => $request->visitorId,
                    'type' => 'form',
                    'value' => $referenceKey,
                    'processed_contact_id' => $people->id,
                ]);
                $visitor = app('App\Http\Controllers\OnlineStoreTrackingController')->recordConversion($conversion, false);
            }
        }

        foreach($request->fields as $input) {

            $profileFieldName = $input['updatePeopleProfile'];

            $formLabel = LandingPageFormLabel::updateOrCreate([
                'account_id' => $accountId,
                'landing_page_form_id' => $form->id,
                'landing_page_form_label' => $input['label']
            ]);

			LandingPageFormContent::create([
				'account_id' => $accountId,
                'visitor_id' => $visitor['id'] ?? null,
				'reference_key' => $referenceKey,
				'landing_page_form_id' => $form->id,
				'landing_page_form_label_id' => $formLabel->id,
				'landing_page_form_content' => $input['value'],
				'processed_contact_id' => $people->id ?? null,
			]);

			if($profileFieldName == "none") continue;

			switch (true) {
			    // don't update current $people email/phone if it is duplicated
                case $profileFieldName === 'email' && $this->isPropDuplicated($accountId, $profileFieldName, $input['value']):
                case $profileFieldName === 'phone_number' && $this->isPropDuplicated($accountId, $profileFieldName, $input['value']):
                    break;

                case in_array($profileFieldName, $peopleProfileFields) :
                    $people->update([
                        $profileFieldName => $input['value']
                    ]);
                    break;
                case in_array($profileFieldName, $peopleAddressFields) :
                    $peopleAddress->update([
                        $profileFieldName => $input['value']
                    ]);
					break;
				default :
					$customFieldName = peopleCustomFieldName::firstOrCreate(
					    [
                            'account_id' => $accountId,
                            'custom_field_name' => $profileFieldName
                        ],
                        [
                            'type' => 'text' // default
                        ]
                    );

					$value = $input['value'];
					if ($dataFormatValidationServ->validateCustomField($customFieldName->type, $value)) {
                        $customFieldValue = peopleCustomField::updateOrCreate([
                            'account_id' => $accountId,
                            'processed_contact_id' => $people->id,
                            'people_custom_field_name_id' => $customFieldName->id
                        ], [
                            'custom_field_content' => $value
                        ]);
                    }
			}
		}

		if (isset($people)) {
			event(new LandingFormSubmitted($form, $people));
            /**
             * @deprecated referral-campaign
             */
            // $newSignUp = $this->newSignUp($people, $request->funnelId);
            // $user = $this->referralUser($people, $request->funnelId);
            // $cookie = $user ? cookie('funnel#user#'.$user['campaign'], $user['referralCode'], (3*30*24*60) ) : null;// 90 days
            // if ($request->hasCookie('referral') && $newSignUp && $request->funnelId) {
            //     $this->checkReferralCampaignAction($request->getHost(), 'sign-up', $request->funnelId, $people);
            // }
		}

        if ($emailSettings && is_string($request->input('actionAfterSubmit.email.receiverEmail'))) {
            $emailSettings['subject'] = 'Someone has opted into your form ' . $form->title . '!';
            $this->sendEmailNotification($emailSettings, $request->fields, $accountId);
        }

        $compactData = [
			'status' => 'Success',
			'visitorRefKey' => ($tracking && ($tracking->cart ?? null)) ? $tracking->visitor->reference_key : null,
			'cartRefKey' => ($tracking && ($tracking->cart ?? null)) ? $tracking->cart->reference_key : null,
            'user' => $user ?? null,
            'isInFunnel' => $visitor['isInFunnel'] ?? false,
        ];

		return $cookie ? response()->json($compactData)->cookie($cookie) : response()->json($compactData);
	}


    private function attachTags($tagIds, $contactId)
    {
        foreach($tagIds as $tagId) {
            $processedTag = ProcessedTag::find($tagId);
            $processedTag->processedContacts()->syncWithoutDetaching($contactId);
            event(new TagAddedToContact($processedTag, $contactId));
        }
    }

	public function sendEmailNotification($emailSettings, $inputFields, $accountId)
	{
		$receiverEmails = explode(",", $emailSettings['receiverEmail'] ?? '');
		$inputValuesArray = [];
		foreach($inputFields as $input) {
			$inputValuesArray[$input['label']] = $input['value'];
		}
        foreach ($receiverEmails as $email) {
            Mail::to(trim($email))->send(
                new LandingPageFormSubmissionEmail(
                    $emailSettings,
                    $inputValuesArray,
                    $accountId
                )
            );
        }
    }

    private function isPropDuplicated($accountId, $prop, $val)
    {
        return ProcessedContact
            ::where([
                'account_id' => $accountId,
                $prop => $val
            ])
            ->exists();
    }

    public function download(LandingPageForm $form)
    {
        return Excel::download(new FormContentsExport($form->id), "{$form->title}.csv");
    }

    public function delete(LandingPageForm $form)
    {
        $form->delete();

        return response()->json([
            'status' => 'Success',
            'message' => "The form {$form->title} was successfully deleted",
        ]);
    }

    public function deleteEmptyForm(LandingPageForm $form)
    {
        if($this->accountId() === $form->account_id && $form->submit_count === 0) {
            $form->delete();
        }
    }

    public function deleteFormContent($refKey)
    {
        LandingPageFormContent::where('reference_key', $refKey)->delete();

        return response()->json([
            'status' => 'Success',
            'message' => "The form contents was successfully deleted",
        ]);
    }
}
