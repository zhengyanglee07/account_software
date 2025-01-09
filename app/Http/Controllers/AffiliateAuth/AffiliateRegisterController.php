<?php

namespace App\Http\Controllers\AffiliateAuth;

use App\AffiliateDetail;
use App\AffiliateUser;
use App\Http\Controllers\AffiliateMailsController;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\RedirectsUsers;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Password;
use Auth;
use Inertia\Inertia;

class AffiliateRegisterController extends Controller
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

    // use RegistersUsers;
    use RedirectsUsers;

    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
    // protected $redirectTo = '/affiliate/dashboard';


    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    public function showRegisterForm()
    {
        return Inertia::render('hypershapes-affiliate/pages/Register');
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'fName' => 'string|max:255',
            'lName' => 'required|string|max:255',
            'email' => 'required|string|max:255|unique:affiliate_users,email',
            'password' => ['required', 'string', Password::min(8)->letters()->numbers()],
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
        $condition = true;
        do {
            $randomId = random_int(100000000001, 999999999999);
            $condition = AffiliateUser::where('affiliate_unique_id', $randomId)->exists();
        } while ($condition);
        return AffiliateUser::create([
            'affiliate_unique_id' => $randomId,
            'first_name' => $data['fName'],
            'last_name' => $data['lName'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'verification_code' => sha1(time()),
        ]);
    }

    public function register(Request $request)
    {
        $data = $request->all();
        $validator = $this->validator($data)->validate();
        $user = $this->create($data);
        $newUserDetail = new AffiliateDetail();
        $newUserDetail->affiliate_userid = $user->id;
        $newUserDetail->affiliate_unique_link = $user->affiliate_unique_id;
        $newUserDetail->save();

        if ($user !== null) {
            Auth::guard('affiliateusers')->login($user);
            //Send Email
            AffiliateMailsController::sendSignupEmail($user->first_name, $user->email, $user->verification_code);
            return redirect()->route('affiliate.verify');
        }
    }

    public function verifyUser()
    {
        $verification_code = \Illuminate\Support\Facades\Request::get('code');
        $user = AffiliateUser::where('verification_code', $verification_code)->first();
        if ($user != null) {
            $user->is_verified = 1;
            $user->email_verified_at = now();
            $user->save();
            return redirect()->route('affiliate.dashboard');
        }
        return redirect()->route('affiliate.login')->with(session()->flash('alert-danger', 'Invalid verification code'));
    }

    public function verify()
    {
        return Inertia::render('hypershapes-affiliate/pages/VerifyEmail')->with(['status', session('satus')]);
    }
}
