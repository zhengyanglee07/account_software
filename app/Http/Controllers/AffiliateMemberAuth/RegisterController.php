<?php

namespace App\Http\Controllers\AffiliateMemberAuth;

use App\Account;
use App\AccountDomain;
use App\AffiliateMemberAccount;
use App\AffiliateMemberParticipant;
use App\AffiliateMemberSetting;
use App\Http\Controllers\Controller;
use App\ProcessedContact;
use App\Services\RefKeyService;
use App\Traits\AffiliateMemberAccountTrait;
use App\Traits\AuthAccountTrait;
use Illuminate\Auth\Events\Registered;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use DB;
use Inertia\Inertia;
use App\Traits\PublishedPageTrait;
use App\Traits\ReferralCampaignTrait;
use Illuminate\Validation\Rules\Password;

class RegisterController extends Controller
{
    use AuthAccountTrait, RegistersUsers, AffiliateMemberAccountTrait, ReferralCampaignTrait;

    private const PARENT_PARTICIPANT_NOT_FOUND = '01';
    private const SETTINGS_NOT_FOUND = '02';

    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
    protected $redirectTo = '/affiliates/confirm/email';

    private $refKeyService;

    public function __construct(RefKeyService $refKeyService)
    {
        $this->middleware('guest');
        $this->refKeyService = $refKeyService;
    }

    /**
     * Get the guard to be used during registration.
     *
     * @return \Illuminate\Contracts\Auth\StatefulGuard
     */
    protected function guard(): \Illuminate\Contracts\Auth\StatefulGuard
    {
        return Auth::guard('affiliateMember');
    }

    /**
     * Get data form registration form
     *
     * @return \Illuminate\View\View
     */
    public function getSignupData()
    {
        $account = Account::findOrFail($this->getCurrentAccountId());
        return response()->json([
            'account' => $account,
        ]);
    }

    /**
     * Handle a registration request for the application.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     * @throws \Throwable
     */
    public function register(Request $request)
    {

        $account = Account::findOrFail($this->getCurrentAccountId());
        // $planTotal = $account->accountPlanTotal;
        // if($planTotal-> total_affiliate_member >= $account->permissionMaxValue('add-affiliate-member')){
        //     return response()->json([
        //         'exceed_limit' => true,
        //         'modal_title' => "Affiliate Member Reach The Limit",
        //         'custom_context' => 'The affiliate member for this campaign is full and currently close. Thanks for the participation',
        //         'upgradeButton' => false
        //     ], 422);
        // }

        $this->validator($request->all())->validate();

        DB::beginTransaction();
        try {
            event(new Registered($user = $this->create($request->all())));

            $memberAccount = AffiliateMemberAccount::firstWhere(['account_id' => $this->getCurrentAccountId(), 'email' => $request->email]);
            $isLoggedIn = Hash::check($request->password, $memberAccount?->password);

            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }

        return $request->wantsJson()
            ? new JsonResponse([
                'token' => $isLoggedIn ? $memberAccount->createToken('hypershapes_affiliate_member')->plainTextToken : '',
            ], 201)
            : redirect($this->redirectPath());
    }


    /**
     * Get a validator for an incoming registration request.
     *
     * @param array $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data): \Illuminate\Contracts\Validation\Validator
    {
        return Validator::make($data, [
            'first_name' => ['string', 'max:255', 'nullable'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['bail', 'required', 'email:rfc,dns', 'max:255'],
            'password' => ['required', 'string', Password::min(8)->letters()->numbers()],

        ], [
            'password.same' => 'Password does not match',
            'first_name.required' => 'Field is required',
            'last_name.required' => 'Field is required',
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param array $data
     * @return AffiliateMemberAccount|\Illuminate\Database\Eloquent\Model
     * @throws \Exception
     * @throws \Throwable
     */
    protected function create(array $data)
    {
        $viaRefKey = $data['via'] ?? null;
        $id = null;
        $accountId = $this->getCurrentAccountId();
        if ($viaRefKey) {
            $id = $this->decodeReferralCode($viaRefKey, $accountId);
        }

        // member acct doesn't exist for root affiliate that creates the program
        $parentAffiliateMemberAcct = $id
            ? AffiliateMemberAccount::find($id)
            : null;

        // there's only one affiliate_member_account_id=NULL per account: root
        $parentParticipant = AffiliateMemberParticipant
            ::where([
                'account_id' => $accountId,
                'affiliate_member_account_id' => $parentAffiliateMemberAcct->id ?? null // null means root parent
            ])
            ->first();

        if (!$parentParticipant) {
            $this->fatalErrorAbort(self::PARENT_PARTICIPANT_NOT_FOUND);
        }

        $firstName = $data['first_name'];
        $lastName = $data['last_name'];
        $email = $data['email'];

        // member account email is unique per account_id. Create if not exist
        $affiliateMember = AffiliateMemberAccount::firstOrCreate(
            [
                'account_id' => $accountId,
                'email' => $email,
            ],
            [
                'reference_key' => $this->refKeyService->getRefKey(new AffiliateMemberAccount),
                'first_name' => $firstName,
                'last_name' => $lastName,
                'referral_identifier' => $this->getReferralIdentifier($firstName, $lastName),
                'password' => Hash::make($data['password']),
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
            abort(409, "$email has already joined");
        }

        // add child to parent
        $settings = AffiliateMemberSetting::where('account_id', $accountId)->first();

        if (!$settings) {
            $this->fatalErrorAbort(self::SETTINGS_NOT_FOUND);
        }

        $childParticipant = AffiliateMemberParticipant::create([
            'account_id' => $accountId,
            'affiliate_member_account_id' => $affiliateMember->id,
            'status' => $settings->auto_approve_affiliate
                ? 'approved'
                : 'pending'
        ]);
        $parentParticipant->appendNode($childParticipant);
        $childParticipant->refresh();
        if ($childParticipant->status === 'approved') {
            $parentParticipant->sendReferEmail();
        }

        // add mem to root parent's processed_contact
        ProcessedContact::firstOrCreate(
            [
                'email' => $affiliateMember->email,
                'account_id' => $accountId
            ],
            [
                'contactRandomId' => $this->refKeyService->getRefKey(new ProcessedContact, 'contactRandomId'),
                'fname' => $affiliateMember->first_name,
                'lname' => $affiliateMember->last_name,
                'acquisition_channel' => 'affiliate_member'
            ]
        );

        return $affiliateMember;
    }

    /**
     * @param $code
     */
    private function fatalErrorAbort($code): void
    {
        abort(409, "Fatal error: {$code}. Kindly contact our support");
    }
}
