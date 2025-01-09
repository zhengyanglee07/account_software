<?php

namespace App\Http\Controllers\Auth;

use App\User;
use App\AccountUser;
use App\AccountRole;
use App\RoleInvitationEmail;
use App\Account;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Http\Request;
use App\AffiliateDetail;
use App\AffiliateReferralLog;
use App\AffiliateUser;
use App\AffiliateUserReferral;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB;
class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers;
    // use RedirectsUsers;

    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
    protected $redirectTo = '/confirm/email';

    /**
     * Create a new controller instance.
     *
     * @return void
	 *
	 *
     */

	public function showRegistrationForm()
    {
        $affiliate_id = \Illuminate\Support\Facades\Request::get('affiliate_id');

        if($affiliate_id!=null){
            Cookie::queue(cookie('refer_by', $affiliate_id, 86400));
        }

        return view('auth.register',compact('affiliate_id'));
    }


    public function __construct()
    {
        $this->middleware('guest');
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        // dd($data);
        return Validator::make($data, [
            'firstName' => ['string', 'max:255','nullable'],
            'lastName' => ['required','string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => 'required|string|min:8|bail',
            // 'confirm_password' => 'same:password|bail',
        ],[
            'password.same' =>'Password does not match',
            // 'confirm_password.same' => 'Password does not match',
            'lastName.required'=>'Field is required'
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return \App\User
     */
    protected function create(array $data)
    {
        $user = User::create([
            'firstName' => $data['firstName'],
            'lastName' => $data['lastName'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);


        return $user;
    }

    public function check(Request $request){
        // $validator = Validator::make($request->all(),[
        //     'email' => 'email|unique:users'
        // ]);
        // if ($validator->fails()){
        //     return response()->json($validator->errors(),403);
        // }

        $user = User::where('email',$request->email)->count();
        $isEmailExisted = $user > 0;
        $message = ($isEmailExisted) ? 'This email is taken. Try another' : 'This email is available';
        return response()->json([
            'isExisted' => $isEmailExisted,
            'message' => $message]);
    }

    public function generateRandomId($tableName,$columnName){

        $condition = true;
        do {
            $randomId = random_int(100000000001, 999999999999);
            $condition = DB::table($tableName)->where($columnName, $randomId)->exists();

        } while ($condition);

        return $randomId;

    }


	public function register(Request $request, $user)
    {

        // $this->validator($request->all())->validate();

		// event(new Registered($user = $this->create($request->all())));

        if ($request->token !== null) {
            $this->assignRole($user, $request->token);
        }
        $isExisted = AffiliateUser::where('email',$request->email)->exists();

        if(!$isExisted){
            $newAffiliate = new AffiliateUser();
            $newAffiliate->first_name = $request->fName;
            $newAffiliate->last_name=$request->lName;
            $newAffiliate->email = $request->email;
            $newAffiliate->password = Hash::make($request->password);
            $newAffiliate->affiliate_unique_id = $this->generateRandomId('affiliate_users','affiliate_unique_id');
            $newAffiliate->verification_code = sha1(time());
            $newAffiliate->save();
            AffiliateDetail::create([
                'affiliate_userid' => $newAffiliate->id,
                'affiliate_unique_link' => $newAffiliate->affiliate_unique_id
            ]);
        }

        $referBy = Cookie::get('refer_by');
        if(isset($request->affiliate_id) || $referBy != null){
            $newAffiliate =  AffiliateUser::where('email',$request->email)->first();
            $affiliateId = (isset($request->affiliate_id) ? $request->affiliate_id : $referBy);
            $affiliateUser = AffiliateUser::where('affiliate_unique_id',$affiliateId)->first();
            $hypershapesUser = User::where('email',$affiliateUser->email)->first();

            if($affiliateUser){
                $subscription = ($hypershapesUser) ? $hypershapesUser->subscription : null;
                if($hypershapesUser){
                    $parentAffiliate = AffiliateReferralLog::firstOrCreate([
                        'user_id' => $hypershapesUser->id
                    ],[
                        'referral_name' => 'root',
                        'affiliate_id' => $affiliateUser->id,
                        'subscription_id' => ($subscription) ? $subscription->id : null,
                        'referral_unique_id' =>$this->generateRandomId('affiliate_referral_logs','referral_unique_id'),
                    ]);
                }else{
                    $parentAffiliate = AffiliateReferralLog::firstOrCreate([
                        'user_id' => null,
                        'affiliate_id' => $affiliateUser->id,
                    ],[
                        'referral_name' => 'root',
                        'subscription_id' => ($subscription) ? $subscription->id : null,
                        'referral_unique_id' =>$this->generateRandomId('affiliate_referral_logs','referral_unique_id'),
                    ]);
                }
                $affiliateReferral = AffiliateReferralLog::create([
                    'referral_unique_id' => $this->generateRandomId('affiliate_referral_logs','referral_unique_id'),
                    'user_id' => $user->id,
                    'affiliate_id' => $affiliateUser->id,
                    'referral_name' => $request->firstName." ".$request->lastName,
                    'referral_status' => 'pending',
                    'refer_from_affiliate_id' => $affiliateId,
                ]);
                $affiliateDetail = AffiliateDetail::where('affiliate_userid', $affiliateUser->id)->first();
                $affiliateDetail->total_referrals = AffiliateReferralLog::where('affiliate_id',$affiliateUser->id)->where('user_id','!=',NULL)->where('parent_id','!=',NULL)->count();
                $affiliateDetail->save();
                if($parentAffiliate){
                    $parentAffiliate->appendNode($affiliateReferral);
                }
            }
        }else{
            $newAffiliate =  AffiliateUser::where('email',$request->email)->first();
            AffiliateReferralLog::create([
                'referral_unique_id' => $this->generateRandomId('affiliate_referral_logs','referral_unique_id'),
                'user_id' => $user->id,
                'affiliate_id' => $newAffiliate->id,
                'referral_name' => $request->firstName." ".$request->lastName,
                'referral_status' => 'pending',
            ]);
        }
        // $this->guard()->login($user);
        // return $this->registered($request, $user)
        //                 ?: redirect($this->redirectPath());
    }

    public function assignRole($user, $token)
    {
        $emailDetails = RoleInvitationEmail::where('token', $token)->where('email', $user->email)->first();
        if ($emailDetails === null) {
            return;
        }
        $accountId = Account::where('accountRandomId', $emailDetails->account_random_id)->first()->id;
        $role = AccountRole::where('name', 'admin')->first();

        AccountUser::firstOrCreate([
            'user_id' => $user->id,
            'account_role_id' => $role->id,
            'account_id' => $accountId,
            'role' => $role->name,
        ]);
        $emailDetails->status = 'verified';
        $emailDetails->save();
        return;
    }
}
