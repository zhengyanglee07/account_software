<?php

namespace App\Http\Controllers;

use App\peopleCustomField;
use App\peopleCustomFieldName;
use App\ProcessedContact;
use App\Traits\AuthAccountTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Account;
use Log;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class PeopleCustomFieldController extends Controller
{
    use AuthAccountTrait;

	/**
     *
	 * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Http\JsonResponse|\Illuminate\View\View
     */
	public function customField(){
		$currentAccountId = Auth::user()->currentAccountId;
		$allCustomFields = peopleCustomFieldName::where('account_id',$currentAccountId)->get();
        return Inertia::render('people/pages/CustomFields', compact('allCustomFields'));
    }

    public function rename(Request $request){
        $currentAccountId = Auth::user()->currentAccountId;
        $id = $request->oldCustomFieldId;

        $request->validate([
            'customFieldName' => 'required|unique:people_custom_field_names,custom_field_name,' . $id . ',id,account_id,' . $currentAccountId,
            'oldCustomFieldId' => 'required|int'
        ]);

         $newCustomFieldName = $request->customFieldName;
         $savedCustomField = peopleCustomFieldName::find($id);
         $savedCustomField->custom_field_name = $newCustomFieldName;
         $savedCustomField->save();

         return response()->noContent();
    }

    public function deleteCustomField(Request $request){
        $customFieldId = peopleCustomFieldName::find($request->customFieldId);
        $customFieldId->delete();

        return response()->noContent();
	}

	public function create(ProcessedContact $processedContact):JsonResponse{
		$currentAccountId = Auth::user()->currentAccountId;
        $peopleCustomField = peopleCustomField::create([
            'account_id' => $currentAccountId,
            'custom_field_name' => '',
		]);

        return response()->json([
            'status' => 'success',
            'customField' => $peopleCustomField
        ]);
	}

    public function store(ProcessedContact $processedContact): JsonResponse
    {
        $currentAccountId = Auth::user()->currentAccountId;
        $peopleCustomField = peopleCustomField::create([
            'account_id' => $currentAccountId,
            'processed_contact_id' => $processedContact->id,
            'people_custom_field_name_id' => null,
            'custom_field_content' => '',
        ]);

        return response()->json([
            'customField' => $peopleCustomField
        ]);
	}


    /**
     * Sync custom fields in a contact based on people custom field name
     *
     * @param \App\ProcessedContact $processedContact
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function syncContactCustomFields(ProcessedContact $processedContact, Request $request): JsonResponse
    {
        $customFields = $request->input('customFields');
        $currentAccountId = Auth::user()->currentAccountId;
        $peopleCustomFieldNameIds = [];

        // Note: update custom field based on people custom name's id
        //       to ensure only one entry per people custom name is allowed
        foreach ($customFields as $customField) {
            $peopleCustomFieldNameId = peopleCustomFieldName
                ::where('account_id', $currentAccountId)
                ->where('custom_field_name', $customField['customFieldName'])
                ->firstOrFail()
                ->id;

            // save for deletion later
            $peopleCustomFieldNameIds[] = $peopleCustomFieldNameId;

            $processedContact->peopleCustomFields()->updateOrCreate([
                'people_custom_field_name_id' => $peopleCustomFieldNameId
            ], [
                'processed_contact_id' => $processedContact->id,
                'account_id' => $currentAccountId,
                'custom_field_content' => $customField['customFieldContent']
            ]);
        }

       // delete not updated/created custom field
       $processedContact
           ->peopleCustomFields()
           ->whereNotIn('people_custom_field_name_id', $peopleCustomFieldNameIds)
           ->delete();

        return response()->json([
            'message' => 'Successfully update custom fields'
        ]);
    }

    public function destroy(peopleCustomField $customField)
    {
        $customField->delete();
        return response()->noContent();
	}

	public function addCustomFieldName(Request $request){
		// dd($request);
	// 	$currentAccountId = Auth::user()->currentAccountId;
	// 	if($request!==null){
	// 		$newCustomFieldName = new peopleCustomFieldName();
	// 		$newCustomFieldName->account_id = $currentAccountId;
	// 		$newCustomFieldName->custom_field_name = $request->customFieldInput;
	// 		$newCustomFieldName->save();
	// }
	$currentAccountId = Auth::user()->currentAccountId;

		$customFieldName = $request->customFieldInput;

        $peopleCustomField = new peopleCustomFieldName();
        $peopleCustomField->account_id = $currentAccountId;
        $peopleCustomField->type = $request->customFieldType;
        $peopleCustomField->custom_field_name = $customFieldName;
        $peopleCustomField->save();

        return response()->json([
            'customField' => $peopleCustomField
        ]);
    }
}
