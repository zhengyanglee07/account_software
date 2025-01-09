<?php

namespace App\Http\Controllers;

use App\Account;
use App\ProcessedContact;
use App\Email;
use App\Services\EmailReportService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class EmailSubscriptionController extends Controller
{
    /**
     * To resubscribe contact back to email, just delete
     * the corresponding entry matched processed_contact_id
     * in email_subscribe table, no need to do any extra things
     *
     * @param $contactRandomId
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function subscribe($contactRandomId,$emailId)
    {
        $processedContact = ProcessedContact::where('contactRandomId', $contactRandomId)->firstOrFail();
        $unsubscribedContact = $processedContact->emailUnsub()->first();

        $email = Email::find($emailId);

        $email->processedContacts()->updateExistingPivot($processedContact->id , [
            'status' => 'sent',
        ]);

        if ($unsubscribedContact) {
            try {
                $unsubscribedContact->delete();
            } catch (\Exception $e) {
                \Log::debug('Error when deleting unsub contact');
            }
        }

        return Inertia::render('email/pages/Subscribe');
    
    }

    /**
     * A simple unsubscribe page
     *
     * @param $contactRandomId
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function showUnsubscribe($contactRandomId, $emailId)
    {
        $processedContact = ProcessedContact::where('contactRandomId', $contactRandomId)->firstOrFail();

        $appUrl = rtrim(config('app.url'), '/');
        // $port = config('app.env') === 'local' ? ':8000' : '';
        $unsubLink = $appUrl . '/emails/unsubscribe/' . $processedContact->id . '/' . $emailId;
        // $unsubLink = $appUrl . '/emails/unsubscribe/' . $processedContact->id;
        $subscribeLink = $appUrl . '/emails/subscribe/' . $processedContact->contactRandomId . '/' . $emailId;;

        $isUnsubcibed = $processedContact->emailUnsub()->exists();

        $companyLogo = Account::find($processedContact->account_id)->company_logo ?? null;

        return Inertia::render('email/pages/Unsubscribe', compact([
            'unsubLink',
            'subscribeLink',
            'contactRandomId',
            'isUnsubcibed',
            'companyLogo',
        ]));
    }

    /**
     * Unsubscribe a contact from email sending (marketing email)
     *
     * @param \App\ProcessedContact $processedContact
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function unsubscribeContact(ProcessedContact $processedContact, $emailId)
    {
        $processedContactId = $processedContact->id;

        $processedContact->emailUnsub()->updateOrCreate([
            'account_id' => $processedContact->account_id,
            'processed_contact_id' => $processedContactId
        ]);

        $email = Email::find($emailId);
        $email->processedContacts()->updateExistingPivot($processedContactId, [
            'status' => 'unsubscribe',
        ]);

        (new EmailReportService($email))->updateTotalEmailUnsubscribed();

        return response('/emails/unsubscribe/' . $processedContact->contactRandomId . '/success');
    }

    /**
     * View to show after a success unsub
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function showUnsubscribeSuccess()
    {
        return Inertia::render('email/pages/UnsubscribeSuccess');
    }
}
