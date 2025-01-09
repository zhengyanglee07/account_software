<?php

namespace App\Http\Controllers;

use App\Account;
use App\Mail\HyperSenderDomainVerificationEmail;
use App\Sender;
use App\Traits\AuthAccountTrait;
use App\Traits\SenderTrait;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Mail;
use Inertia\Inertia;

class SenderController extends Controller
{
    use AuthAccountTrait, SenderTrait;

    public function __construct()
    {
        $this->middleware('signed')->only('verifySenderDomain');
    }

    /**
     * Get current account senders. Default to obtain all senders regardless
     * of their status in current account
     *
     * Query params:
     * - status: if verified is provided, it will obtain only verified sender
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function currentAccountSenders(Request $request): JsonResponse
    {
        $status = $request->query('status');

        $currentAccountSenders = Sender::where('account_id', $request->user()->currentAccountId)->get();
        $senders = $status === 'verified'
            ? $currentAccountSenders->filter(static function ($sender) {
                return $sender->status === 'verified';
            })->values()
            : $currentAccountSenders;

        return response()->json(compact('senders'));
    }

    /**
     * Check whether email exists on DB
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkSenderDomain(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $sender = Sender
            ::where('account_id', $request->user()->currentAccountId)
            ->where('email_address', $request->query('email'))
            ->first();

        return response()->json([
            // check db sender status first before query AWS
            // Edit 2021: check status on DB ONLY, SES check is separated
            'verified' => $this->senderStatusVerified($sender),
            'sender' => $sender // only send verification email if sender is null
        ]);
    }

    /**
     * Check whether email exists on SES
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function checkSenderDomainOnSes(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        return response()->json([
            'verified' => $this->sesCheckEmailVerified($request->query('email')),
        ]);
    }

    /**
     * Send hypershapes sender domain verification email.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function sendHyperVerificationEmail(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email'
        ]);

        $emailAddr = $request->email;
        $currentAccountId = $request->user()->currentAccountId;

        $newSender = Sender::firstOrCreate([
            'account_id' => $currentAccountId,
            'email_address' => $emailAddr,
        ], [
            'name' => null,
            'status' => 'pending',
        ]);

        Mail::to($emailAddr)->send(new HyperSenderDomainVerificationEmail(
            $currentAccountId,
            $emailAddr
        ));

        return response()->json([
            'message' => 'A verification email is sent to ' . $emailAddr . '. Please check your email inbox.',
            'sender' => $newSender
        ]);
    }

    /**
     * Verify sender domain that is verified previously on SES.
     *
     * @param Account $account
     * @param $crypt
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|RedirectResponse|\Illuminate\Routing\Redirector
     * @throws AuthorizationException
     */
    public function verifySenderDomain(Account $account, $crypt, Request $request)
    {
        if (!hash_equals((string)$account->id, (string)$request->user()->currentAccountId)) {
            throw new AuthorizationException;
        }

        $email = Crypt::decryptString($crypt);

        // to prevent the situation where user deleted the
        // sender before verifying it
        Sender::updateOrCreate([
            'account_id' => $account->id,
            'email_address' => $email,
        ], [
            'status' => 'verified',
            'email_verified_at' => now()
        ]);

        return Inertia::render('email/pages/VerificationSuccess', compact('email'));
    }

    /**
     * A page to indicate success sender domain verification. This view is the same
     * with SES one.
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function verificationSuccess(Request $request)
    {
        $request->validate([
            'c' => 'required' // c is encrypted email
        ]);

        return view('email.ses.verificationSuccess', [
            'email' => Crypt::decryptString($request->query('c'))
        ]);
    }

    public function refreshSenderStatus(Sender $sender)
    {
        $email = $sender->email_address;
        $emailVerified = $this->sesCheckEmailVerified($email);

        $sender->update([
            'status' => $emailVerified ? 'verified' : 'pending'
        ]);

        return response()->json([
            'status' => 'success',
            'senderStatus' => $sender->status
        ]);
    }

    public function getVerifiedIdentity()
    {
        $this->getSenderByStatus('pending')->each(function ($sender) {
            $emailVerified = $this->sesCheckEmailVerified($sender->email_address);
            $sender->update([
                'status' => $emailVerified ? 'verified' : 'pending'
            ]);
        });
        return response()->json(['senders' => $this->getSenderByStatus('verified')->get()]);
    }

    private function getSenderByStatus($status)
    {
        return Sender::where([
            'account_id' => $this->getCurrentAccountId(),
            'status' => $status,
        ]);
    }

    /**
     * Delete a sender
     *
     * Note: since now we can send our own verification email, so
     *       once user verified an email on SES, it will stay
     *       permanently there until further action is taken by
     *       internal guys.
     *
     *       If this isn't desired behavior, you might want to
     *       create a custom delete method in the future to delete
     *       SES email.
     *
     * @param $id
     * @return \Illuminate\Http\Response
     * @throws \Exception
     */
    public function destroy($id): \Illuminate\Http\Response
    {
        Sender::findOrFail($id)->delete();
        return response()->noContent();
    }
}
