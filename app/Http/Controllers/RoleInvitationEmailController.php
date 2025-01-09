<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\RoleInvitationEmail;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use App\Mail\AdminInvitationEmail;
use Illuminate\Support\Facades\Crypt;
use App\User;
use App\Account;
use App\AccountUser;
use App\AccountRole;
use Auth;
use Inertia\Inertia;

class RoleInvitationEmailController extends Controller
{
    public function getRoleInvitationPage()
    {
        $accountId = Auth::user()->currentAccountId;
        $accountRandomId = Account::find($accountId)->accountRandomId;
        $ownerEmails = User::where('currentAccountId', $accountId)->first()->email;
        $existingEmails[] =  $ownerEmails;
        $invitedEmails = RoleInvitationEmail::where('account_random_id', $accountRandomId)->get();
        foreach($invitedEmails as $invitedEmail){
            $existingEmails[] =  $invitedEmail['email'];
        }
        // $existingEmails = Account::find($accountId)->user->pluck('email');
        return Inertia::render('setting/pages/InvitationEmailForm', compact('accountRandomId', 'existingEmails'));
}

    public function sendInvitation(Request $request)
    {
        do {
            $token = Str::random();
        } while (RoleInvitationEmail::where('token', $token)->first());

        $invite = RoleInvitationEmail::create([
            'token' => $token,
            'email' => $request->email,
            'account_random_id' => $request->randomId,
            'role' => $request->role,
            'status' => 'unverified'
        ]);

        $companyDetails = Account::where('accountRandomId', $request->randomId)->first();

        Mail::to($request->email)->send(new AdminInvitationEmail($invite, $companyDetails));

        return response()->json(['status' => 'Email Sent']);
    }

    public function assignRoleAssignment(Request $request)
    {
        $email = Crypt::decryptString($request->encryptedEmail);
        $accountId = Account::where('accountRandomId', $request->randomId)->first()->id;
       // Check if user already deletd before/after accepted the invitation
        $isUserDeleted = RoleInvitationEmail::where('token', $request->token)->where('status', 'unverified')->first();
        // Check if user already accepted the invitation
        $isUserAccepted = RoleInvitationEmail::where('token', $request->token)->where('status', 'verified')->first();

        if ($isUserAccepted !== null || $isUserDeleted === null) {
            return redirect('/login')->withErrors(['token-not-applicable'=> 'Token expired']);
        }

        // Check if user exist
        $isUserExist = User::where('email', $email)->count();
        if ($isUserExist === 0) {
            return redirect('/register?token='.$request->token);
        }

        // Check if the token exist in database
        $isEmailSent = RoleInvitationEmail::where('token', $request->token)->where('email', $email)->count();
        if ($isEmailSent === 0) {
            return redirect('/login')->withErrors(['token-mismatched'=> 'Your are not invited by the user']);
        }

        // Assign the role
        $user = User::where('email', $email)->first();
        $adminRole = AccountRole::where('name', 'admin')->first();
        AccountUser::firstOrCreate([
            'user_id' => $user->id,
            'account_role_id' => $adminRole->id,
            'account_id' => $accountId,
            'role' => $adminRole->name,
        ]);

        // Set verified status to email
        $selectedInvitationEmail = RoleInvitationEmail::where('email', $user->email)->where('token', $request->token)->first();
        $selectedInvitationEmail->status = 'verified';
        $selectedInvitationEmail->save();

        return redirect('/login');
    }
}
