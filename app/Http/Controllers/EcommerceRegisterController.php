<?php

namespace App\Http\Controllers;

use App\AffiliateMemberAccount;
use App\AffiliateMemberParticipant;
use App\AffiliateMemberSetting;
use App\Traits\PublishedPageTrait;
use App\Traits\AffiliateMemberAccountTrait;
use Auth;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Foundation\Auth\RedirectsUsers;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Auth\Events\Registered;
use App\Http\Controllers\EcommerceMailsController;
use App\ProcessedContact;
use App\AccountDomain;
use App\EcommerceAccount;
use App\EcommerceAddressBook;
use App\Account;
use App\EcommercePreferences;
use Carbon\Carbon;
use DB;
use Illuminate\Validation\Rule;
use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Validation\Rules\Password;
use App\Traits\AuthAccountTrait;

use App\EcommerceVisitor;
use App\EcommerceTrackingLog;
use App\EcommerceAbandonedCart;
use App\EcommercePage;
use App\Http\Requests\CheckoutFormRequest;
use App\Services\AffiliateCookieService;
use App\Traits\ReferralCampaignTrait;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class EcommerceRegisterController extends Controller
{
    use RedirectsUsers, AffiliateMemberAccountTrait, PublishedPageTrait, AuthAccountTrait, ReferralCampaignTrait;

    public function __construct()
    {
        $this->middleware('guest');
    }


    public function domainInfo($domainName)
    {
        return AccountDomain::whereDomain($domainName)->first();
    }

    public function getAccountId($domainName = null)
    {
        return $this->getCurrentAccountId();
        // dd($this->domainInfo($domainName)!= null);
        return $this->domainInfo($domainName) != null
            ? $this->domainInfo($domainName)->account_id
            : Auth::user()->currentAccountId;
    }

    public function show()
    {
        if (Auth::guard('ecommerceUsers')->check() && Auth::guard('ecommerceUsers')->user()->hasVerifiedEmail()) {
            return redirect()->intended('/orders/dashboard');
        }

        $publishPageBaseData = $this->getPublishedPageBaseData();
        $account_id = $publishPageBaseData['domain']->account_id;
        $companyLogo = Account::find($account_id)->company_logo;
        $preference = EcommercePreferences::firstWhere('account_id', $account_id);

        return Inertia::render('customer-account/pages/Register', array_merge(
            $publishPageBaseData,
            [
                'companyLogo' => $companyLogo,
                'pageName' => "Sign Up Page",
                'preference' => $preference,
                'status' => session('status'),
            ]
        ));
    }

    public function getRandomId($tableName, $columnName)
    {
        $condition = true;
        do {
            return $randomId = random_int(100000000001, 999999999999);
            $condition = DB::table($tableName)->where($columnName, $randomId)->exists();
        } while ($condition);
    }

    public function register(Request $request)
    {
        $accountId = $this->getCurrentAccountId();
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
            return response([
                'status' => 'failed',
                'message' => 'The email has already been taken.',
            ]);
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
            return abort(302, '/email/verification');
        }
        if (isset($request->sendEmail)) return response(['status' => 'fail', 'message' => 'Fail on create account']);
        // return response()->json('Something went wrong');

        return response([
            'status' => 'failed',
            'message' => 'Something went wrong',
        ]);
    }

    public function convertDatetimeToSelectedTimezone()
    {
        $date = Carbon::createFromFormat('Y-m-d H:i:s', date('Y-m-d H:i:s'), date_default_timezone_get());
        $date->timezone('Asia/Kuala_Lumpur');
        return $date;
    }

    public function emailNotice()
    {
        $publishPageBaseData = $this->getPublishedPageBaseData();

        if (!Auth::guard('ecommerceUsers')->user()) abort(404);

        $email =  Auth::guard('ecommerceUsers')->user()->email;
        $companyLogo = Account::find($this->getAccountId($_SERVER['SERVER_NAME']) ?? $_SERVER['HTTP_HOST'])->company_logo;

        return Inertia::render('customer-account/pages/VerifyEmail', array_merge(
            $publishPageBaseData,
            [
                'email' => $email,
                'companyLogo' => $companyLogo,
                'pageName' => 'Email Verification Page',
            ]
        ));
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

    public function verifyEmail()
    {
        $verification_code = \Illuminate\Support\Facades\Request::get('code');
        $ecommerceAccount = EcommerceAccount::where('verification_code', $verification_code)->first();
        if ($ecommerceAccount != null) {
            $ecommerceAccount->email_verified_at = now();
            $ecommerceAccount->is_verify = true;
            $ecommerceAccount->save();
            return abort(302, '/orders/dashboard');
        }
        return abort(302, '/email/verification');
    }

    public function resetEmail()
    {
        $this->sendEmailVerification();
        return redirect('/email/verification')->with(session()->flash('alert-success', 'Your verification email has been resend.'));
    }

    public function resendEmail()
    {
        $this->sendEmailVerification();
        return redirect('/email/verification')->with(session()->flash('alert-success', 'Your verification email has been resend.'));
    }

    private function sendEmailVerification()
    {
        $ecommerceAccount = Auth::guard('ecommerceUsers')->user();
        $domain = $_SERVER['HTTP_HOST'];
        $urlHeader = $domain === 'hypershapes.test' ? 'http://' : 'https://';

        $sellerInfo = $ecommerceAccount->sellerInfo;

        $sellerInfo->url = $urlHeader . $domain . '/email/verify/?code=' . $ecommerceAccount->verification_code;

        if (!empty($_SERVER['HTTP_ORIGIN'])) {
            $sellerInfo->url = $_SERVER['HTTP_ORIGIN'] . '/email/verify/?code=' . $ecommerceAccount->verification_code;
        }

        $ecommerceAccount->full_name = $ecommerceAccount->processedContact->fname;
        EcommerceMailsController::sendSignupEmail($ecommerceAccount, $sellerInfo);
    }

    /**
     * Create affiliate member account if applicable
     *
     * Note on the return value. If true means operation has no error and vise versa
     *
     * @param \Illuminate\Http\Request $request
     * @return bool|\Illuminate\Http\RedirectResponse
     * @throws \Throwable
     */
    private function createAffiliateMemberAccount(Request $request, $domainName)
    {
        $affiliateInfo = AffiliateCookieService::getReferToken();

        $accountId = $this->getAccountId($domainName);
        $affiliateMemberSettings = AffiliateMemberSetting::where('account_id', $accountId)->first();

        if (!$affiliateMemberSettings) {
            \Log::error('Affiliate member setting is missing.', [
                'account_id' => $accountId,
            ]);

            return false;
        }

        // skip if auto create account option is not enabled
        if (!$affiliateMemberSettings->auto_create_account_on_customer_sign_up) {
            return true;
        }

        $parentMember = null;
        $parentParticipant = null;
        if ($affiliateInfo) {
            parse_str($affiliateInfo, $referral);
            $ref = ($referral['ref']);
            $parentMember = AffiliateMemberAccount
                ::where([
                    'account_id' => $accountId,
                    'referral_identifier' => $ref
                ])
                ->first();

            if ($parentMember) {
                $parentParticipant = AffiliateMemberParticipant
                    ::where([
                        'account_id' => $accountId,
                        'affiliate_member_account_id' => $parentMember->id
                    ])
                    ->first();
            }

            if (!$parentMember) {
                \Log::error('Affiliate parent member account is missing.', [
                    'account_id' => $accountId,
                    'ref' => $ref
                ]);
            }
        }


        DB::beginTransaction();

        try {
            $email = $request->email;

            $affiliateMember = AffiliateMemberAccount::firstOrCreate(
                [
                    'account_id' => $accountId,
                    'email' => $email,
                ],
                [
                    'reference_key' => $this->getRandomId('affiliate_member_accounts', 'reference_key'),
                    'first_name' => $request->fullName,
                    'last_name' => null,
                    'referral_identifier' => $this->getReferralIdentifier($request->fullName, null),
                    'password' => Hash::make($request->password),
                ]
            );

            $participantExists = DB
                ::table('affiliate_member_participants')
                ->where([
                    'account_id' => $accountId,
                    'affiliate_member_account_id' => $affiliateMember->id,
                ])
                ->exists();

            if ($participantExists) {
                \Log::error('Participant joined', [
                    'account_id' => $accountId,
                    'member' => $affiliateMember,
                    'email' => $email
                ]);

                return false;
            }

            $childParticipant = AffiliateMemberParticipant::create([
                'account_id' => $accountId,
                'affiliate_member_account_id' => $affiliateMember->id,
                'status' => $affiliateMemberSettings->auto_approve_affiliate
                    ? 'approved'
                    : 'pending'
            ]);
            // add child to parent if parent is exist
            if ($parentParticipant) {
                $parentParticipant->appendNode($childParticipant);
                $childParticipant->refresh();
                if ($childParticipant->status === 'approved') {
                    $parentParticipant->sendReferEmail();
                }
            }

            DB::commit();

            return true;
        } catch (\Throwable $th) {
            \Log::error('Auto create affiliate error', [
                'msg' => $th->getMessage()
            ]);
            DB::rollBack();
            return false;
        }
    }
}
