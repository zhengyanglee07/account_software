<?php

namespace App\Http\Controllers;

use App\Services\ContactCurrencyService;
use App\Services\RefKeyService;
use App\Services\SegmentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\ProcessedAddress;
use App\ProcessedContact;
use Auth;
use App\peopleCustomField;
use App\Services\ProcessedContactService;
use App\Traits\AuthAccountTrait;
use Illuminate\Http\Response;
use DB;
use Illuminate\Validation\Rule;

class ProcessedContactController extends Controller
{
    use AuthAccountTrait;

    private $contactCurrencyService;

    public function __construct(ContactCurrencyService $contactCurrencyService)
    {
        $this->contactCurrencyService = $contactCurrencyService;
    }

    /**
     * Simply get all current account's contacts
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function allContacts(): JsonResponse
    {
        $currentAccountId = Auth::user()->currentAccountId;
        $allCurrentAccountContacts = ProcessedContact::where('account_id', $currentAccountId)->get();

        return response()->json([
            'contacts' => $this->contactCurrencyService->mapContactsValuesBasedOnCurrency($allCurrentAccountContacts)
        ]);
    }

    /**
     * @param $peopleId
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Exception
     */
    public function deletePeople($peopleId): Response
    {
         $processedContact = ProcessedContact::where('id',$peopleId)->firstOrFail();

         $this->authorize('delete', $processedContact);

         $processedContact->delete();
         return response()->noContent();
	}

	public function addContact(Request $request, RefKeyService $refKeyService)
    {
        $currentAccountId = Auth::user()->currentAccountId;
        $request->validate([
            'name' => 'nullable|max:255',
            'fname' => 'nullable|max:255',
            'lname' => 'nullable|max:255',
            'phone_number' => [
                'nullable',
                Rule::requiredIf(!$request->email),
                'unique:processed_contacts,phone_number,NULL,id,account_id,' . $currentAccountId . ',deleted_at,NULL'
            ],
            'email' => [
                'nullable',
                Rule::requiredIf(!$request->phone_number),
                'unique:processed_contacts,email,NULL,id,account_id,' . $currentAccountId . ',deleted_at,NULL'
            ],
            'gender' => 'nullable',
            'birthday' => 'nullable|date',
            //address
            'address1' => 'nullable|max:255',
            'address2' => 'nullable|max:255',
            'zip' => 'nullable|digits_between:5,10',
            'city' => 'nullable:max:255',
            'state' => 'nullable:max:255',
            'country' => 'nullable:max:255',
        ]);

        DB::beginTransaction();

        try {
            $newProcessedContact=new ProcessedContact();
            $newProcessedContact->contactRandomId = $refKeyService->getRefKey(new ProcessedContact, 'contactRandomId');
            $newProcessedContact->fname=$request->fname;
            $newProcessedContact->lname=$request->lname;
            $newProcessedContact->phone_number=$request->phone_number;
            $newProcessedContact->email=$request->email;
            $newProcessedContact->gender=$request->gender;
            $newProcessedContact->birthday=$request->birthday;
            $newProcessedContact->credit_balance = 0;
            $newProcessedContact->account_id=$currentAccountId;
            $newProcessedContact->save();

            $newProcessedAddress = new ProcessedAddress();
            $newProcessedAddress->processed_contact_id=$newProcessedContact->id;
            $newProcessedAddress->address1=$request->address1;
            $newProcessedAddress->address2=$request->address2;
            $newProcessedAddress->zip=$request->zip;
            $newProcessedAddress->city=$request->city;
            $newProcessedAddress->state=$request->state;
            $newProcessedAddress->country=$request->country;
            $newProcessedAddress->save();

            foreach($request->newCustomField as $data){
                if (!($content = $data["value"] ?? '')) continue;

                $newCustomField = new PeopleCustomField();
                $newCustomField->processed_contact_id = $newProcessedContact->id;
                $newCustomField->account_id = $data["account_id"];
                $newCustomField->people_custom_field_name_id = $data["id"];
                $newCustomField->custom_field_content = $content;
                $newCustomField->save();
            }

            DB::commit();

            return response()->json($newProcessedContact);
        } catch (\Throwable $th) {
            DB::rollBack();
            abort(500, $th->getMessage());
        }
	}

    /**
     * Update processed contact
     *
     * @param \App\ProcessedContact $processedContact
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function update(ProcessedContact $processedContact, Request $request): JsonResponse
    {
        $this->authorize('update', $processedContact);

        $currentAccountId = Auth::user()->currentAccountId;

        $validatedRequest = $request->validate([
            'name' => 'nullable|max:255',
            'fname' => 'nullable|max:255',
            'lname' => 'nullable|max:255',

            // FK this ignore self
            // my rule: unique:table,unique_column_to_check,ID_TO_IGNORE,ID_TO_IGNORE_COLUMN_NAME,...rest (composite unique conditions)
            // If you typed "email" for ID_TO_IGNORE_COLUMN_NAME, then ID_TO_IGNORE you should fill in email instead of id,
            // laravel docs didn't mention it very well
            // Well, if you're using simpler unique rule then no need to care this
            'phone_number' => [
                'nullable',
                Rule::requiredIf(!$request->email),
                'unique:processed_contacts,phone_number,' . $processedContact->id . ',id,account_id,' . $currentAccountId . ',deleted_at,NULL'
            ],

            'email' => [
                'nullable',
                Rule::requiredIf(!$request->phone_number),
                'unique:processed_contacts,email, ' . $processedContact->id . ',id,account_id,' . $currentAccountId . ',deleted_at,NULL'
            ],

            'gender' => 'nullable',
            'birthday' => 'nullable|date',
        ]);

        $processedContact->update($validatedRequest);

        return response()->json([
            'message' => 'Successfully updated contact',
            'contact' => $processedContact
        ]);
    }

    /**
     * Wrapper around SegmentService's filterContacts, to provide
     * a simple interface for http request
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Services\SegmentService $segmentService
     * @return \Illuminate\Http\JsonResponse
     */
    public function filterContacts(Request $request, SegmentService $segmentService): JsonResponse
    {
        $validatedData = $request->validate([
            'conditionFilters' => 'required|array'
        ]);

        $contacts = $segmentService->filterContacts($validatedData['conditionFilters']);

        return response()->json([
            'data' => $this->contactCurrencyService->mapContactsValuesBasedOnCurrency($contacts)
        ]);
    }

    public function bulkDelete(Request $request)
    {
        $request->validate([
            'contactIds' => 'required|array'
        ]);

        ProcessedContact::destroy($request->contactIds);
        return response()->noContent();
    }

    public function getPaginatedContacts()
    {
        return (new ProcessedContactService())->getPaginatedContacts();
    }
}
