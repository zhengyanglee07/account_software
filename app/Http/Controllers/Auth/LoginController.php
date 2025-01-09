<?php

namespace App\Http\Controllers\Auth;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse|\Illuminate\Http\Response
     * @throws ValidationException
     */
    public function apiLogin(Request $request)
    {
        $credentials = $request->only(['email', 'password']);

        if (Auth::attempt($credentials, $request->filled('remember'))) {
            $token = optional(Auth::user())->createToken('hypershapes-user');
            return response()->json([
                'status' => 'success',
                'token' => $token->plainTextToken,
            ])->withCookie(
                cookie(
                    'hs_id',
                    $token->plainTextToken,
                    (3 * 30 * 24 * 60), // 90 days
                    null,
                    '.hypershapes.com',
                    null,
                    false,
                )
            );
        }

        return $this->sendFailedApiLoginResponse($request);
    }

      /**
     * Logout user by revoking token
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     */
    public function apiLogout(Request $request)
    {
        if (Auth::check()) {
            $request->user()->currentAccessToken()->delete();
            return response()->noContent();
        }

        return response()->json([
            'status' => 'failed',
            'message' => 'You are not logged in.'
        ], 422);
    }


    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    // protected $redirectTo = '/api';
    //carmen
    protected $redirectTo = '/dashboard';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
	}

	public function logout(){
		Auth::logout();
		return redirect('/');
	}

    /**
     * Get the failed login response instance.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    protected function sendFailedApiLoginResponse(Request $request)
    {
        throw ValidationException::withMessages([
            'message' => [trans('auth.failed')],
        ]);
    }
}
