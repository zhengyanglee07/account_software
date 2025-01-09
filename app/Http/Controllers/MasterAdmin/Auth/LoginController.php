<?php

namespace App\Http\Controllers\MasterAdmin\Auth;

use Auth;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\ThrottlesLogins;
use Inertia\Inertia;

class LoginController extends Controller
{

	use ThrottlesLogins;
	public $maxAttempts = 3;
	public $decayMinutes = 5;

	
    /**
     * Show the login form.
     * 
     * @return \Illuminate\Http\Response
     */
    public function showLoginForm()
    {
		return Inertia::render('master-admin/pages/Login');
    }

    /**
     * Login the admin.
     * 
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function login(Request $request)
    {
        //Validation...
        //Login the admin...
		//Redirect the admin...
		
		$this->validator($request);
	
		//check if the user has too many login attempts.
		if ($this->hasTooManyLoginAttempts($request)){
			//Fire the lockout event.
			$this->fireLockoutEvent($request);
	
			//redirect the user back after lockout.
			return $this->sendLockoutResponse($request);
		}

		if(Auth::guard('masterAdmin')->attempt($request->only('username','password'),$request->filled('remember'))){
			//Authentication passed...
			return redirect()
				->intended(route('masterAdmin.dashboard'));
		}

		//keep track of login attempts from the user.
		$this->incrementLoginAttempts($request);

		//Authentication failed...
		return $this->loginFailed();
    }

    /**
     * Logout the admin.
     * 
     * @return \Illuminate\Http\RedirectResponse
     */
    public function logout()
    {
	  //logout the admin...
	  Auth::guard('masterAdmin')->logout();
		return redirect()
			->route('masterAdmin.login');
			// ->with('status','Admin has been logged out!');
    }

    /**
     * Validate the form data.
     * 
     * @param \Illuminate\Http\Request $request
     * @return 
     */
    private function validator(Request $request)
	{
		//validation rules.
		$rules = [
			'username'    => 'required|exists:master_admins|min:10|max:191|starts_with:hypershapes@',
			'password' => 'required|string|min:4|max:255',
			
		];

		//custom validation error messages.
		$messages = [
			'username.exists' => 'These credentials do not match our records.',
		];

		//validate the request.
		$request->validate($rules,$messages);
	}

    /**
     * Redirect back after a failed login.
     * 
     * @return \Illuminate\Http\RedirectResponse
     */
    private function loginFailed()
    {
	  //Login failed...
	  return redirect()
        ->back()
        ->withInput()
        ->withErrors([
			'password' => 'These credentials do not match our records.',
		]);
	}
	

	/**
	 * Only guests for "admin" guard are allowed except
	 * for logout.
	 * 
	 * @return void
	 */
	public function __construct()
	{
		$this->middleware('guest:masterAdmin')->except('logout');
	}

	/**
     * Username used in ThrottlesLogins trait
     * 
     * @return string
     */
	public function username(){
        return 'username';
    }
}
