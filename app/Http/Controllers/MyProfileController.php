<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateProfile;
use App\Account;
use Illuminate\Http\Request;
use App\User;
use Auth;
use Illuminate\Validation\Validator;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;

class MyProfileController extends Controller
{
    private function getAuthUserAccountWithRandomId()
    {
        $user = Auth::user();
        $currentAccountId = $user->currentAccountId;
        $account          = Account::where('id', $currentAccountId)->first();
        $accountRandomId  = $account->accountRandomId;

        return [$currentAccountId, $accountRandomId];
    }

    public function  UserInfo()
    {
        return Inertia::render('setting/pages/ProfileSettings', ['user' => Auth::user()]);
    }

    public function saveData(Request $request)
    {
        $firstName = $request->input('firstName');
        $lastName = $request->input('lastName');
        $user = Auth::user();

        $user->firstName = $firstName ?? $user->firstName;
        $user->lastName = $lastName ?? $user->lastName;
        $user->save();

        return response()->json(['message' => 'Successfully updated profile']);
    }

    public function changePassword(UpdateProfile $request)
    {
        $password = Hash::make($request->password);
        $user = Auth::user();
        $user->password = $password;
        $user->save();
        return response()->json(['message' => 'Successfully updated password']);
    }
}
