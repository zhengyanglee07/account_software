<?php

namespace App\Http\Controllers\API;

use App\Account;
use App\EcommerceAccount;
use App\EcommerceAddressBook;
use App\EcommercePreferences;
use App\Http\Controllers\Controller;
use App\ProcessedContact;
use App\Traits\PublishedPageTrait;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\Hash;
use Auth;

class EcommerceRegisterController extends Controller
{
    use PublishedPageTrait;

    public function register(Request $request)
    {
        $accountId = $request->user()->currentAccountId;
        $preference = EcommercePreferences::firstWhere('account_id', $accountId);

        $isEcommerceAccountExists = EcommerceAccount::where('account_id', $accountId)
            ->where('email', $request->email)
            ->exists();

        if ($isEcommerceAccountExists) {
            if (isset($request->sendEmail)) {
                $validate = app('App\Http\Controllers\EcommerceLoginController')->authenticate($request);
                // return response()->json($validate);
                return $validate
                    ? response(['status' => 'Success', 'message' => 'Account Login Successful', 'customerInfo' => Auth::guard('ecommerceUsers')->user()->processedContact ?? null])
                    : response(['status' => 'Fail', 'message' => 'Email or Password Incorrect']);
            }
            // return response()->json('Account already exists');
            return redirect()->back()->with(session()->flash('status', 'The email has already been taken.'));
        }

        $request->validate([
            'email' => 'required|string|email|max:255',
            'password' => ['required', 'string', Password::min(8)->letters()->numbers()],
        ]);

        $isProcessedContactExists = ProcessedContact::where(
            [
                'account_id' => $accountId,
                'email' => $request->email
            ]
        )->exists();

        if ($isProcessedContactExists) {
            $ProcessedContact = ProcessedContact::where(
                [
                    'account_id' => $accountId,
                    'email' => $request->email
                ]
            )->first();
        } else {
            if (isset($request->phoneNumber)) {
                $request->validate([
                    'phoneNumber' => [
                        'nullable',
                        'required',
                        'unique:processed_contacts,phone_number,NULL,id,account_id,' . $accountId . ',deleted_at,NULL'
                    ],
                ]);
            }
            $ProcessedContact = new ProcessedContact();
            $ProcessedContact->account_id =  $accountId;
            $ProcessedContact->email =  $request->email;
        }

        $ProcessedContact->contactRandomId = $ProcessedContact->contactRandomId ?? $this->getRandomId('processed_contacts', 'contactRandomId');
        if (!empty($request->fullName)) $ProcessedContact->fname = $request->fullName;
        if (!empty($request->phoneNumber)) $ProcessedContact->phone_number = $request->phoneNumber;
        $ProcessedContact->save();

        $ecommerceAccount = new EcommerceAccount();
        $ecommerceAccount->account_id = $accountId;
        $ecommerceAccount->processed_contact_id =  $ProcessedContact->id ?? null;
        $ecommerceAccount->email = $request->email;
        $ecommerceAccount->password = Hash::make($request->password);
        $ecommerceAccount->verification_code = $this->getRandomId('ecommerce_accounts', 'verification_code');
        $ecommerceAccount->save();

        $addressBook = new EcommerceAddressBook();
        $addressBook->ecommerce_account_id = $ecommerceAccount->id;
        $addressBook->save();

        $affiliateAcctCreation = $this->createAffiliateMemberAccount($request, '');
        if (!$affiliateAcctCreation) {
            \Log::error('Auto create affiliate failed. Please check logs');
        }

        if ($ecommerceAccount !== null) {
            Auth::guard('ecommerceUsers')->login($ecommerceAccount);
            $this->sendEmailVerification();
            if (isset($request->sendEmail)) return response(['customerInfo' => $ProcessedContact]);
            // return response()->json('register_successful');
            return redirect('/email/verification');
        }
        if (isset($request->sendEmail)) return response(['status' => 'fail', 'message' => 'Fail on create account']);
        // return response()->json('Something went wrong');
        return redirect()->back()->with(session()->flash('alert-danger', 'Something went wrong'));
    }

    public function getEmailNotice()
    {
        $publishPageBaseData = $this->getPublishedPageBaseData();

        if (!Auth::guard('ecommerceUsers')->user()) abort(404);

        $email =  Auth::guard('ecommerceUsers')->user()->email;
        $companyLogo = Account::find($this->getAccountId($_SERVER['SERVER_NAME']) ?? $_SERVER['HTTP_HOST'])->company_logo;

        return response()->json(array_merge(
            $publishPageBaseData,
            [
                'email' => $email,
                'companyLogo' => $companyLogo,
                'pageName' => 'Email Verification Page',
            ]
        ));
    }
}
