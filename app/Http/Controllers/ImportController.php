<?php

namespace App\Http\Controllers;

use App\Traits\AuthAccountTrait;
use App\Services\DataFormatValidationService;
use App\Services\RefKeyService;
use Illuminate\Http\Request;
use App\Http\Requests\CsvImportRequest;
use App\Imports\ContactImport;
use App\Account;
use App\ProcessedContact;
use App\peopleCustomField;
use App\peopleCustomFieldName;
use App\ProcessedAddress;
use App\ProcessedTag;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;
use Session;
use DB;
use Inertia\Inertia;

class ImportController extends Controller
{
    use AuthAccountTrait;

    private $dataFormatValidationServ;

    public function __construct(DataFormatValidationService $dataFormatValidationService)
    {
        $this->dataFormatValidationServ = $dataFormatValidationService;
    }

    public function parseImport(CsvImportRequest $request)
    {
        // dd(count(Excel::toArray(new ContactImport, $request->file('csv_file'))[0]));
        $user = Auth::user();
        $currentAccountId = $user->currentAccountId;

        $account = $user->accounts->first();
        $max_people = $account->permissionMaxValue('add-people');
        $accountPlanTotal = $account->accountPlanTotal;
        $leftOverPeople = $max_people - $accountPlanTotal->total_people;

        // dd($accountPlanTotal->total_people);


        $customOptions = peopleCustomFieldName::where('account_id', $currentAccountId)
            ->get()
            ->pluck('custom_field_name');

        $path = $request->file('csv_file')->getRealPath();

        if ($request->has('header')) {
            $csvData = Excel::toArray(new ContactImport, $request->file('csv_file'))[0];
        } else {
            $csvData = array_map('str_getcsv', file($path));
        }

        if (count($csvData) === 0) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Data is empty'
            ], 406);
        } else if (count($csvData) > $leftOverPeople) {
            return response()->json([
                'status' => 'exceed-limit',
                'exceed_limit' => true,
                'modal_title' => "You've Reach The Limit",
                'context' => 'people',
                'custom_context' => 'You have exceed the maximum number of people.
                Please check again with the number of people you import and make sure the people do not exceed plan limit or click Upgrade Plan to increase the limit.'
            ], 406);
        }

        if ($request->has('header')) {
            $csvHeaderFields = array_keys($csvData[0]);
        }

        $csvDisplayData = array_slice($csvData, 0, 3);

        // to be used in /import_fields (i.e. in showImportFields method below)
        session()->put('csvHeaderFields', $csvHeaderFields ?? []);
        session()->put('csvData', $csvData);
        session()->put('csvDisplayData', $csvDisplayData);
        session()->put('customOptions', $customOptions);

        // remember to redirect to /import_fields at the frontend
        return response()->json([
            'status' => 'success',
        ]);
    }

    public function showImportFields()
    {
        $keys = [
            'csvHeaderFields',
            'csvData',
            'csvDisplayData',
            'customOptions'
        ];

        $csvData = null;

        foreach ($keys as $key) {
            $$key = session()->get($key);
        }

        $account = Auth::user()->currentAccount();

        // redirect user back to people page if he/she is not redirected
        // from /import_parse POST req (means $csvData will be null)
        if (!$csvData) {
            $accountRandomId = $account->accountRandomId;
            return redirect('/people/view/' . $accountRandomId);
        }

        $emailAddresses = ProcessedContact
            ::where('account_id', $account->id)
            ->pluck('email')
            ->unique()
            ->filter()
            ->values();

        $phoneNumbers = ProcessedContact
            ::where('account_id', $account->id)
            ->pluck('phone_number')
            ->unique()
            ->filter()
            ->values();

        return Inertia::render('people/pages/ImportFields', array_merge(
            compact(...$keys),
            [
                'emailAddresses' => $emailAddresses,
                'phoneNumbers' => $phoneNumbers
            ]
        ));
    }

    public function checkDuplicateNameAndDataType(Request $request)
    {
        $user = Auth::user();
        $currentAccountId = $user->currentAccountId;
        $customFieldType = $request->dataTypeInput;
        $data = $request->csvData;
        $csvData = json_decode($data, true);
        $message = "non-duplicate";
        $regexError = "no error";
        $nameInArray = [];

        $formatedNameInput = strtolower(str_replace(' ', '', $request->name_input));

        $getNames = peopleCustomFieldName::where('account_id', $currentAccountId)
            ->get();

        foreach ($getNames as $getName) {
            $nameInArray[] = strtolower(str_replace(' ', '', $getName->custom_field_name));
        }

        if (in_array($formatedNameInput, $nameInArray)) {
            $message = "duplicated";
        }

        if ($customFieldType !== 'text') {
            foreach ($csvData as $row) {
                $rowValueArray = [];

                foreach ($row as $rowKey => $rowValue) {
                    $rowValueArray[] = $rowValue;
                }

                $value = $rowValueArray[$request->td_index];

                if (!$this->dataFormatValidationServ->validateCustomField($customFieldType, $value)) {
                    $regexError = 'error';
                    return response()->json([
                        'regexError' => $regexError,
                    ]);
                }
            }
        }

        if ($regexError == 'no error' && $message == 'non-duplicate') {
            $peopleCustomField = new peopleCustomFieldName();
            $peopleCustomField->account_id = $currentAccountId;
            $peopleCustomField->type = $customFieldType === 'emailAddress' ? 'email' : $customFieldType;
            $peopleCustomField->custom_field_name = $formatedNameInput;
            $peopleCustomField->save();
        }

        return response()->json([
            'message' => $message,
            'regexError' => $regexError,
        ]);
    }

    /**
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Services\RefKeyService $refKeyService
     * @return \Illuminate\Http\Response
     * @throws \Throwable
     */
    public function saveImportContacts(Request $request, RefKeyService $refKeyService)
    {
        $request->validate([
            'includedNameArray' => 'required|array',
            'csvData' => 'required|string',
            'selectedUniqueIdentifier' => 'required|in:email,phone_number',
            'success' => 'required|int',
            'skipped' => 'required|int'
        ]);

        $user = Auth::user();
        $currentAccountId = $user->currentAccountId;
        $data = $request->csvData;
        $csvData = json_decode($data, true);
        $uniqueIdentifier = $request->selectedUniqueIdentifier;
        
        $newContactCount = 0;
        $updatePeopleProfileCount = 0;

        // remember to check whether field below exists as column
        // in processed_addresses table
        $processed_address = [
            'address1', 'address2', 'city', 'state', 'country', 'zip'
        ];

        // remember to check whether field below exists as column
        // in processed_contacts table
        $people_profile_name = [
            'fname',
            'lname',
            'email',
            'birthday',
            'phone_number',
            'gender'
        ];

        DB::beginTransaction();
        try {
            $contactIdArray = [];
            foreach ($csvData as $row) {
                $rowValueArray = [];
                $rowKeyArray = [];
                $temp_contact_id = 0;

                foreach ($row as $rowKey => $rowValue) {
                    $rowValueArray[] = $rowValue;
                    $rowKeyArray[] = $rowKey;
                }

                $contact = null;

                // find "email"/"phone_number" field idx and initialize contact & address model
                $uniqueIdentifierIdx = array_search($uniqueIdentifier, $request->includedNameArray, true);

                if ($uniqueIdentifierIdx === false) {
                    abort(422, 'Something is wrong about the unique identifier. Please inform our support');
                }

                $newEntryAttributes = [
                    $uniqueIdentifier => $rowValueArray[$uniqueIdentifierIdx],
                    'account_id' => $currentAccountId
                ];

                $contact = ProcessedContact::where($newEntryAttributes)->first();

                if ($contact === null) {
                    $newContactCount += 1;
                    $contact = ProcessedContact::create($newEntryAttributes);
                } else {
                    $updatePeopleProfileCount += 1;
                }

                if (is_null($contact)) {
                    abort(422, 'Contact could not be found/created due to unknown reason');
                }

                $temp_contact_id = $contact->id;
                $contactIdArray[] = $contact->id;
                $processedAddress = ProcessedAddress::firstOrCreate(['processed_contact_id' => $temp_contact_id]);

                foreach ($request->includedNameArray as $key => $field) {
                    if ($field === "none") {
                        continue;
                    }

                    $columnName = $rowKeyArray[$key];
                    $columnData = $rowValueArray[$key];

                    $this->dataFormatValidationServ->columnDataValidation($field, $columnData, $columnName);

                    $contact->acquisition_channel = 'Import';
                    $contact->contactRandomId = $refKeyService->getRefKey(new ProcessedContact, 'contactRandomId');

                    // processed contact
                    if (in_array($field, $people_profile_name, true)) {
                        $contact->$field = $columnData;
                    }

                    // processed address
                    else if (in_array($field, $processed_address, true)) {
                        $processedAddress->processed_contact_id = $temp_contact_id;
                        $processedAddress->$field = $columnData;
                        $processedAddress->save();
                    }

                    // custom field
                    else {
                        $customFieldName = peopleCustomFieldName
                            ::where('account_id', $currentAccountId)
                            ->where('custom_field_name', $field)
                            ->first();

                        if (!$customFieldName) {
                            continue;
                        }

                        // extra validation for custom field
                        // Note: legacy custom field name doesn't have type, they're all default to 'text'
                        $this->dataFormatValidationServ->columnDataValidation($customFieldName->type ?? 'text', $columnData, $columnName);

                        $customFieldNameId = $customFieldName->id;
                        $customField = peopleCustomField
                            ::where('account_id', $currentAccountId)
                            ->where('processed_contact_id', $temp_contact_id)
                            ->firstOrCreate(['people_custom_field_name_id' => $customFieldNameId]);
                        $customField->processed_contact_id = $temp_contact_id;
                        $customField->account_id = $currentAccountId;
                        $customField->people_custom_field_name_id = $customFieldNameId;
                        $customField->custom_field_content = $columnData;
                        $customField->save();
                    }

                    $contact->save();
                }
            }

            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }

        session([
            'successfulImports' => $request->success,
            'newContacts' => $newContactCount,
            'updatePeopleProfile' => $updatePeopleProfileCount,
            // for now, since like all the contacts will be imported without fail
            'failedImports' => 0,
            'skippedImports' => $request->skipped
        ]);

        $request->session()->flash('success', 'Data imported successfully.');
        $request->session()->put('importedContactId', $contactIdArray);
        $request->session()->forget('csvHeaderFields');
        $request->session()->forget('csvData');
        $request->session()->forget('csvDisplayData');
        $request->session()->forget('customOptions');

        return response()->json([
            'importedContactId' => $contactIdArray,
        ]);
    }

    /**
     * Show import tag page
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function showTag(Request $request)
    {
        $tags = ProcessedTag
            ::where([
                'account_id' => $this->getCurrentAccountId(),
                'typeOfTag' => 'contact'
            ])
            ->get()
            ->unique('tagName'); // this unique just a precaution, try to avoid saving duplicated tag

        // import stats handled in saveImportContacts() method
        $successfulImports = $request->session()->pull('successfulImports', 0);
        $newContacts = $request->session()->pull('newContacts', 0);
        $updatePeopleProfile = $request->session()->pull('updatePeopleProfile', 0);
        $failedImports = $request->session()->pull('failedImports', 0);
        $skippedImports = $request->session()->pull('skippedImports', 0);

        return Inertia::render('people/pages/ImportCreateTag', compact(
            'tags',
            'successfulImports',
            'newContacts',
            'updatePeopleProfile',
            'failedImports',
            'skippedImports'
        ));
    }

    /**
     * Add tags to imported contacts
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function tagImportedContacts(Request $request): \Illuminate\Http\Response
    {
        $request->validate([
            'tagNames' => 'required|array'
        ]);

        $tagIds = collect($request->tagNames)
            ->map(function ($tagName) {
                $tag = ProcessedTag::firstOrCreate(
                    [
                        'account_id' => $this->getCurrentAccountId(),
                        'tagName' => $tagName
                    ],
                    [
                        'typeOfTag' => 'contact'
                    ]
                );

                return $tag->id;
            });

        $contacts = ProcessedContact::find(Session::get('importedContactId') ?? []);

        if (count($contacts) === 0) {
            abort(500, 'Unable to find any contacts to add tag. If this occurs after import please contact support');
        }

        foreach ($contacts as $contact) {
            $contact->processed_tags()->syncWithoutDetaching($tagIds);
        }

        $request->session()->flash('importedWithTag', true);
        return response()->noContent();
    }

    /**
     * To override the problematic getCurrentAccountId in AuthAccountTrait
     *
     * @return mixed
     */
    private function getCurrentAccountId()
    {
        return Auth::user()->currentAccountId;
    }
}
