<?php

namespace App\Http\Controllers;

use App\Account;
use App\Services\SegmentService;
use Illuminate\Http\Request;
use App\Email;
use App\EmailBounce;
use App\Models\EmailGroup;
use App\EmailUnsubscribe;
use App\Models\EmailReport;
use App\ProcessedContact;
use App\Segment;
use App\Services\TimezoneService;
use Auth;
use jdavidbakr\MailTracker\Model\SentEmailUrlClicked;
use App\SentEmailOpened;
use App\Services\EmailService;
use App\Traits\AuthAccountTrait;
use Inertia\Inertia;
use Carbon\Carbon;
use App\ProcessedTag;

class EmailReportController extends Controller
{
    use AuthAccountTrait;

    protected $timezoneService;
    protected $segmentService;

    public function __construct(
        TimezoneService $timezoneService,
        SegmentService $segmentService
    ) {
        $this->timezoneService = $timezoneService;
        $this->segmentService = $segmentService;
    }

    /**
     * Get unique opens from multiple sent emails
     *
     * @param $sentEmails
     * @return int
     */
    private function getUniqueOpens($sentEmails): int
    {
        return $sentEmails
            ->where('opens', '!==', 0)
            ->count();
    }

    /**
     * Get unique clicks from multiple sent emails
     *
     * @param $sentEmails
     * @return int
     */
    private function getUniqueClicks($sentEmails): int
    {
        return $sentEmails
            ->where('clicks', '!==', 0)
            ->count();
    }

    /**
     * Get all bounced addresses count in provided email
     *
     * @param $sentEmail
     * @return int
     */
    private function getBouncedCount($sentEmail): int
    {
        $emailAddresses = $sentEmail->pluck('recipient_email')->toArray();
        if (count($emailAddresses) === 0) return 0;
        return EmailBounce::where('account_id', $this->getCurrentAccountId())
            ->whereIn('email_address', $emailAddresses)
            ->count();
    }

    /**
     * Get all unsubscribed addresses count in provided email
     *
     * @param \App\Email $email
     * @return int
     */
    private function getUnsubscribeCount(Email $email): int
    {
        return $email->processedContacts()->wherePivot('status', 'unsubscribe')->count();
    }

    /**
     * Email report datatable. Show the details of all emails sent by current account
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index()
    {
        $currentAccountId = Auth::user()->currentAccountId;
        $emails = Email::withCount('sentEmails')
            ->where('account_id', $currentAccountId)
            ->orderBy('id', 'desc')->get();

        foreach ($emails as $email) {
            $emailReport = EmailReport::firstWhere('email_id', $email->id);
            if (!isset($emailReport)) continue;
            $emailReport->update(['total_sent' =>  $email->sent_emails_count]);
            $totalEmailsSent = $emailReport->total_sent;
            $totalUniqueEmailOpens = $emailReport->total_opened;
            $totalUniqueEmailClicks = $emailReport->total_clicked;
            $openRate = $totalEmailsSent !== 0
                ? number_format(
                    ($totalUniqueEmailOpens / $totalEmailsSent) * 100,
                    2,
                    '.',
                    ','
                ) : 0;
            $clickRate = $totalEmailsSent !== 0
                ? number_format(
                    ($totalUniqueEmailClicks / $totalEmailsSent) * 100,
                    2,
                    '.',
                    ','
                ) : 0;
            $bouncedRate = $totalEmailsSent !== 0
                ? number_format(
                    ($emailReport->total_bounced / $totalEmailsSent) * 100,
                    2,
                    '.',
                    ','
                ) : 0;

            // $unsubscribeRate = $totalEmailsSent !== 0
            //     ? number_format(
            //         ($this->getUnsubscribeCount($email) / $totalEmailsSent) * 100,
            //         2,
            //         '.',
            //         ','
            //     ) : 0;

            $schedule = $email->schedule
                ? $this->timezoneService->convertDatetimeBasedOnTimezone(
                    $email->schedule,
                    $email->timezone
                )->format('D, d M Y, g:i A')
                : 'Not scheduled';

            $reports[] = [
                'emailRefKey' => $email->reference_key,
                'name' => $email->name,
                'schedule' => $schedule,
                'sent' => $totalEmailsSent,
                'openRate' => $openRate,
                'clickRate' => $clickRate,
                'viewEmailUrl' => '/emails/' . $email->reference_key . '/design/' . $email->email_design_reference_key,
                'emailDesign' => $email->emailDesign,
                'bouncedRate' => $bouncedRate,
                'group' => $email->group,
            ];
        }
        $emailGroups =  EmailGroup::where('account_id', $currentAccountId)->pluck('name')->toArray();

        return Inertia::render('report/pages/EmailReport', [
            'reports' => $reports ?? [],
            'emailGroups' => $emailGroups ?? [],
        ]);
    }

    /**
     * Show one email report (per email definitely). All stats from sent_emails of each email will
     * be sum up, such as $sent, $opened and $clicked data.
     *
     *
     * @param \App\Email $email
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function show(Email $email, Request $request, EmailService $emailService)
    {
        $sentEmails = $email->sentEmails();
        $emailReport = EmailReport::firstWhere('email_id', $email->id);
        if (isset($emailReport))
            $emailReport->update(['total_sent' =>  $sentEmails->count()]);

        $account = Account::find($email->account_id);

        $sent =  $emailReport->total_sent ?? 0;
        $opened = $emailReport->total_opened ?? 0;
        $clicked = $emailReport->total_clicked ?? 0;
        $bounced = $emailReport->total_bounced ?? 0;
        $unsubscribed = $emailReport->total_unsubscribed ?? 0;

        $emailName = $email->name;
        switch ($email->target) {
            case 'specific-tag':
                $tag = ProcessedTag::find($email->tag_id);
                $recipientDesc = 'People in a ' . $tag->tagName . ' tag';
                break;
            case 'specific-segment':
                $segment = Segment::find($email->segment_id);
                $recipientDesc = 'People in a ' . $segment->segmentName . ' segment';
                break;
            default:
                $recipientDesc = 'Everyone';
                break;
        }
        $subject = $email->subject;
        $sentDate = $sentEmails->latest()->first()->created_at ?? null;

        $navigateFrom = $request->input('fm');

        $recipients = $email->processedContacts()->get();

        $tableData = [];

        $recipients->each(function ($recipient) use (&$tableData, $email, $account) {
            switch ($recipient->pivot->status) {
                case 'pending':
                    $status =  'Pending';
                    break;
                case 'sent':
                    $status = 'Sent';
                    break;
                case 'complaint':
                    $status = 'Mark as spam';
                    break;
                default:
                    $status = $recipient->pivot->status === 'bounce' ? 'Bounced' : 'Unsubscribed';
                    break;
            }

            $procesedEmail = array_map(function ($row) {
                return $row['emailAddress'];
            }, $tableData);
            $email->sentEmails()
                ->where('recipient_email', $recipient->email)
                ->whereNotIn('recipient_email', $procesedEmail)
                ->each(function ($row) use ($recipient, $status, &$tableData) {
                    $sentEmailOpened = SentEmailOpened::firstWhere('sent_email_id', optional($row)->id);
                    $clickedAt = $row ? optional($row->urlClicks()->first())->created_at : null;
                    $tableData[] = [
                        'emailAddress' => $recipient->email,
                        'name' => ($recipient->fname ?? '') . ($recipient->lname ? (' ' . $recipient->lname) : ''),
                        'status' => $status,
                        'openedAt' => $sentEmailOpened->created_at ?? null,
                        'clickedAt' =>  $clickedAt,
                    ];
                });
        });

        return Inertia::render('email/pages/Report', compact(
            'sent',
            'opened',
            'clicked',
            'bounced',
            'unsubscribed',
            'tableData',
            'emailName',
            'subject',
            'sentDate',
            'navigateFrom',
            'recipientDesc',
        ));
    }
}
