<?php

namespace App\Http\Controllers\API;

use App\EcommerceAddressBook;
use Illuminate\Http\Request;

use App\Http\Controllers\Controller;
use App\Traits\CurrencyConversionTraits;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;

class EcommerceController extends Controller
{
    use CurrencyConversionTraits;

    public function checkAuth()
    {
        $ecommerceAccount = Auth::guard('ecommerceUsers');
        return response()->json([
            'isAuthenticated' => $ecommerceAccount->check(),
            'isVerified' => $ecommerceAccount->user()?->is_verify ?? false
        ]);
    }

    public function getBaseData(Request $request)
    {
        $user = $request->user();
        $processedContact = $user->processedContact;
        return response()->json([
            'totalVirtualAsset' => $processedContact->accessLists->load('products')->whereNotNull('products.asset_url')->where('is_active', 1)->count(),
            'totalPurchasedCourse' => $processedContact->courseStudents->where('is_active', 1)->count(),
            'address' => $user->addressBook()->first(),
            'processedContact' => $processedContact,
        ]);
    }

    public function updateAddress(Request $request)
    {
        $address = EcommerceAddressBook::find($request->addressRef);
        $address->update($request->address);
        return response()->json(['address' => $address]);
    }

    public function updateProfile(Request $request)
    {
        $request->user()->processedContact->update([
            'fname' => $request->name,
            'phone_number' => $request->phoneNumber
        ]);
    }

    public function updatePassword(Request $request)
    {
        if (!Hash::check($request->oldPassword, $request->user()->password)) {
            throw ValidationException::withMessages([
                'current_password' => ['The old password you entered is incorrect.'],
            ]);
        }

        $request->validate([
            'password' => ['required', 'string', Password::min(8)->letters()->numbers()],
        ]);

        $request->user()->update(['password' => Hash::make($request->newPassword)]);
    }

    public function getStoreCreditData(Request $request)
    {
        $processedContact = $request->user()->processedContact;
        $storeCredits = $processedContact->storeCredits;
        $currency = $this->getDefaultCurrency()->prefix;
        $storeCredits->map(function ($storeCredit) use ($currency) {
            // convert value based on currency
            $storeCredit->balance = $currency . ' ' . $this->convertCurrency($storeCredit->balance / 100, $storeCredit->currency, false, true);
            $storeCredit->displayCreditAmount = $currency . ' ' . $this->convertCurrency($storeCredit->credit_amount / 100, $storeCredit->currency, false, true);
            return $storeCredit;
        });

        function roundTo2dp($value)
        {
            return number_format((float)$value, 2, '.', '');
        }

        $storeCreditSummary = [
            'totalSpent' => $currency . ' ' . roundTo2dp($storeCredits->where('source', 'Credit Used')->sum('credit_amount') / 100),
            'totalBalance' => $currency . ' ' . roundTo2dp($processedContact->credit_balance / 100),
        ];
        return response()->json(compact('storeCreditSummary', 'storeCredits'));
    }
}
