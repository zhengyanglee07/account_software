<?php

namespace App\Http\Controllers;

use App\Sender;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class SesMailsVerificationController extends Controller
{
    /**
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     * @throws AuthorizationException
     */
    public function verificationSuccess()
    {
        $accountId = session('lastEmailVerifiedAccountId');
        $email = session('lastEmailVerified') ?? '';

        if (!hash_equals((string)$accountId, (string)Auth::user()->currentAccountId)) {
            throw new AuthorizationException;
        }

        Sender::updateOrCreate([
            'account_id' => $accountId,
            'email_address' => $email,
        ], [
            'status' => 'verified',
        ]);

        return Inertia::render('email/pages/VerificationSuccess', compact('email'));
    }

    public function verificationFailed()
    {
        $accountRandomId = Auth::user()->currentAccountId;

        return Inertia::render('email/pages/VerificationFailed', compact('accountRandomId'));
    }
}
