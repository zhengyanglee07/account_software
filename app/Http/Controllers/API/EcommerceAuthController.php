<?php

namespace App\Http\Controllers\API;

use App\Account;
use App\AffiliateMemberAccount;
use App\AffiliateMemberParticipant;
use App\AffiliateMemberSetting;
use App\EcommerceAccount;
use App\EcommerceAddressBook;
use App\Http\Controllers\Controller;
use App\Http\Controllers\EcommerceMailsController;
use App\ProcessedContact;
use App\Repository\Checkout\FormDetail;
use App\Services\AffiliateCookieService;
use App\Traits\AffiliateMemberAccountTrait;
use App\Traits\AuthAccountTrait;
use Auth;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\Hash;
use DB;
use Illuminate\Validation\ValidationException;

class EcommerceAuthController extends Controller
{
    use AuthAccountTrait, AffiliateMemberAccountTrait;

    public function register(Request $request)
    {
        $accountId = $this->getCurrentAccountId();

        $request->validate([
            'email' => ['required', 'email:rfc,dns', 'max:255', 'unique:ecommerce_accounts,email,NULL,id,account_id,' . $accountId],
            'password' => ['required', 'string', Password::min(8)->letters()->numbers()],
            'phoneNumber' => [
                'nullable', 'unique:processed_contacts,phone_number,NULL,id,account_id,' . $accountId . ',deleted_at,NULL'
            ],
        ]);

        $processedContact = ProcessedContact::firstOrCreate(['account_id' => $accountId, 'email' => $request->email]);

        $processedContact->contactRandomId = $processedContact->contactRandomId ?? $this->getRandomId('processed_contacts', 'contactRandomId');
        if (!empty($request->fullName)) $processedContact->fname = $request->fullName;
        if (!empty($request->phoneNumber)) $processedContact->phone_number = $request->phoneNumber;
        $processedContact->save();

        $ecommerceAccount = EcommerceAccount::create([
            'account_id' => $accountId,
            'processed_contact_id' => $processedContact->id,
            'email' => $request->email,
            'password' =>  Hash::make($request->password),
            'verification_code' => $this->getRandomId('ecommerce_accounts', 'verification_code'),
        ]);

        EcommerceAddressBook::create(['ecommerce_account_id' => $ecommerceAccount->id]);

        $affiliateAcctCreation = $this->createAffiliateMemberAccount($request, '');
        if (!$affiliateAcctCreation) {
            \Log::error('Auto create affiliate failed. Please check logs');
        }

        if (!$request->isSkipEmail) $this->sendEmailVerification($ecommerceAccount);
        return $this->login($request);
    }

    public function isCustomerAccountExists($email)
    {
        return EcommerceAccount::where(
            ['account_id' => $this->getCurrentAccountId(), 'email' => $email]
        )->exists();
    }

    public function attempLogin(Request $request)
    {
        $ecommerceAccount = EcommerceAccount::firstWhere(['account_id' => $this->getCurrentAccountId(), 'email' => $request->email]);
        return $ecommerceAccount && Hash::check($request->password, $ecommerceAccount->password);
    }

    public function login(Request $request)
    {
        $ecommerceAccount = EcommerceAccount::with('processedContact:email,id,fname,lname,phone_number')->firstWhere(['account_id' => $this->getCurrentAccountId(), 'email' => $request->email]);

        if (!$this->attempLogin($request)) {
            throw ValidationException::withMessages([
                'email' => ['The email or password you entered is incorrect.'],
            ]);
        }

        if (!empty($request->phoneNumber)) {
            $ecommerceAccount->processedContact->update(['phone_number' => $request->phoneNumber]);
        }

        return response()->json([
            'token' => $ecommerceAccount->createToken('hypershapes_ecommerce_users')->plainTextToken,
            'processedContact' => $ecommerceAccount->processedContact,
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();
        $redirectUrl = $request->query('redirectUrl');
        return response($redirectUrl ?: '/login');
    }

    private function sendEmailVerification($ecommerceAccount)
    {
        $domain = $_SERVER['HTTP_HOST'];
        $urlHeader = $domain === 'hypershapes.test' ? 'http://' : 'https://';

        $sellerInfo = $ecommerceAccount->sellerInfo;

        $sellerInfo->url = $urlHeader . $domain . '/customer-account/email/verify/?code=' . $ecommerceAccount->verification_code;

        if (!empty($_SERVER['HTTP_ORIGIN'])) {
            $sellerInfo->url = $_SERVER['HTTP_ORIGIN'] . '/customer-account/email/verify/?code=' . $ecommerceAccount->verification_code;
        }

        $ecommerceAccount->full_name = $ecommerceAccount->processedContact->fname;
        EcommerceMailsController::sendSignupEmail($ecommerceAccount, $sellerInfo);
    }

    public function getEmailNotice(Request $request)
    {
        $companyLogo = Account::find($this->getCurrentAccountId())->company_logo;
        return response()->json(
            [
                'email' =>  $request->user()->email,
                'companyLogo' => $companyLogo,
                'pageName' => 'Email Verification Page',
            ]
        );
    }

    public function verifyEmail(Request $request)
    {
        $ecommerceAccount = EcommerceAccount::firstWhere('verification_code', $request->get('code'));

        if (!isset($ecommerceAccount))
            return abort(302, '/customer-account/email/verification');

        $ecommerceAccount->email_verified_at = now();
        $ecommerceAccount->is_verify = true;
        $ecommerceAccount->save();
        return abort(302, '/customer-account/orders/dashboard');
    }

    public function resendEmail(Request $request)
    {
        $this->sendEmailVerification($request->user());
        return response('Your verification email has been resend.');
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
    private function createAffiliateMemberAccount(Request $request)
    {
        $affiliateInfo = AffiliateCookieService::getReferToken();

        $accountId = $this->getCurrentAccountId();
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
