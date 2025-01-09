<?php

namespace App\Http\Controllers\API\Auth;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class LoginController
{
    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse|\Illuminate\Http\Response
     * @throws ValidationException
     */
    public function masterAdminLogin(Request $request)
    {
        $credentials = $request->only(['email', 'password']);
        $email = $credentials['email'];

        if (!in_array($email, config('master-admin.allowedEmails'), true)) {
            throw ValidationException::withMessages([
                'email' => ["$email has no permission right to login."],
            ]);
        }

        if (Auth::attempt($credentials, $request->filled('remember'))) {
            $token = optional(Auth::user())->createToken('master-admin');

            return response()->json([
                'status' => 'success',
                'token' => $token->plainTextToken
            ]);
        }

        throw ValidationException::withMessages([
            'email' => [trans('auth.failed')],
        ]);
    }

    /**
     * Logout user by revoking token
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     */
    public function logout(Request $request)
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
}
