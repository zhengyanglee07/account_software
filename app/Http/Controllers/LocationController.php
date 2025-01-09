<?php

namespace App\Http\Controllers;

use App\Location;
use App\Traits\AuthAccountTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class LocationController extends Controller
{
    use AuthAccountTrait;

    public function index()
    {
        $location = Location::where('account_id', $this->getCurrentAccountId())->first();
        return Inertia::render('setting/pages/LocationSettings', compact('location'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'locationName' => 'required',
            'address1' => 'required',
            'address2' => 'nullable',
            'city' => 'required',
            'zip' => 'required|numeric',
            'country' => 'required',
            'state' => 'required',
            'phoneNumber' => 'required',
            'email' => 'required|email'
        ]);

        Location::updateOrCreate([
            'account_id' => $this->getCurrentAccountId()
        ], [
            'name' => $request->locationName,
            'address1' => $request->address1,
            'address2' => $request->address2,
            'city' => $request->city,
            'zip' => $request->zip,
            'country' => $request->country,
            'state' => $request->state,
            'phone_number' => $request->phoneNumber,
            'email' => $request->email
        ]);

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
