<?php

namespace App\Http\Controllers;

use App\ProcessedAddress;
use Illuminate\Http\Request;
use App\ProcessedContact;
use Auth;
use Illuminate\Validation\ValidationException;

class ProcessedAddressController extends Controller
{
	// public function addAddress(ProcessedAddress $processedAddress, Request $request){
	// 	$validatedRequest = $request->validate([
	// 		'address1' => 'nullable|max:255',
	// 		'address2' => 'nullable|max:255',
	// 		'zip' => 'nullable|digits_between:5,10',
	// 		'city' => 'nullable:max:255',
	// 		'state' => 'nullable:max:255',
	// 		'country' => 'nullable:max:255',
	// 	]);

	// 	// dd($request);
		
	// 	// $currentAccountId = Auth::user()->currentAccountId;
	// 	// $processedContact = ProcessedContact::where('account_id',$cusrrentAccountId)->first();
	// 	// $processedAddress->store($validatedRequest);

	// 	// $processedContactIDfound = ProcessedContact::where('contactRandomId', $request->contactRandomId)->first()->id;

	// 	// $processedContactId = ProcessedContact::where('email',$request->email)->first()->id;
	// 	// dd($processedContact->id);
	// 	// dd($processedContactIDfound->id);
	// 	// $processedContactIDfound = $processedContactIDfound->id;

	// 	// $rows = DB::table("players")->where('id_player', $playerId)->first(); //example

	// 	// $processedContactIDfound = DB::table("processed_contacts")->where('id', $request->id)->first();

	// 	$processedContactIDfound = ProcessedContact::where('email', $request->email)->first()->id;


	// 	if (ProcessedAddress::where('processed_contact_id',$processedContactIDfound)->exists()) {
	// 		throw ValidationException::withMessages(['error' => 'Contact Already Exists!']);
			
	// }else{
	// 	if($validatedRequest!=null){
	// 		// dd($request);
	// 		// dd($processedContact);
	// 		// $newprocessedAddress->id
	// 		// $newprocessedAddress = ProcessedAddress::where('processed_contact_id', $processedContact->id)->first();
	// 		// $newprocessedAddress = new ProcessedAddress();
	// 		// $newprocessedAddress->processed_contact_id=$processedContactIDfound;
	// 		// $newprocessedAddress->address1=$request->address1;
	// 		// $newprocessedAddress->address2=$request->address2;
	// 		// $newprocessedAddress->zip=$request->zip;
	// 		// $newprocessedAddress->city=$request->city;
	// 		// $newprocessedAddress->state=$request->state;
	// 		// $newprocessedAddress->country=$request->country;
	// 		// $newprocessedAddress->save();
	// 	}
	// }
		
	// 	// dd($processedContactId);

	// }

    public function update(Request $request)
    {
        $validatedRequest = $request->validate([
            'id' => 'nullable|int',
            'processed_contact_id' => 'required',
            'address1' => 'nullable|max:255',
            'address2' => 'nullable|max:255',
            'zip' => 'nullable|digits_between:5,10', // might need to change in the future
            'city' => 'nullable:max:255',
            'state' => 'nullable:max:255',
            'country' => 'nullable:max:255',
        ]);

        $processedAddress = ProcessedAddress::updateOrCreate(
            [
                'id' => $validatedRequest['id'],
                'processed_contact_id' => $validatedRequest['processed_contact_id']
            ],
            $validatedRequest
        );

        return response()->json([
            'message' => 'Successfully updated address',
            'address' => $processedAddress
        ]);
    }
}
