<?php

namespace App\Http\Controllers;

use App\Account;
use App\Email;
use App\EmailBounce;
use App\EmailDesign;
use App\EmailStatus;
use App\EmailUnsubscribe;
use App\Models\EmailGroup;
use App\ProcessedContact;
use App\ProcessedTag;
use App\Segment;
use App\Sender;
use App\Subscription;
use App\Services\EmailMergeTagService;
use App\Services\EmailService;
use App\Services\RefKeyService;
use App\Traits\AuthAccountTrait;
use App\Traits\SenderTrait;
use Aws\Laravel\AwsFacade;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use DB;
use Inertia\Inertia;
use Validator;

class EmailController extends Controller
{
    use AuthAccountTrait, SenderTrait;

    private $refKeyService;

    public function __construct(RefKeyService $refKeyService)
    {
        $this->refKeyService = $refKeyService;

        // update email gate
        $this
            ->middleware('can:update,email')
            ->only([
                'update',
                'updateSchedule',
                'cancelSchedule',
                'saveSegment',
                'saveSender',
                'saveSubjectPreview',
                'saveSetup'
            ]);
    }

    /**
     * Home page of emails
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index()
    {
        $currentAccountId = Auth::user()->currentAccountId;

        // show only standard email (ignore automation emails)
        $emails = Email::where('account_id', $currentAccountId)
            ->where('type', 'Standard')
            ->get();
        $emailGroups = EmailGroup::where('account_id', $currentAccountId)->pluck('name')->toArray();
        return Inertia::render('email/pages/Index', [
            'dbEmails' => $emails,
            'emailGroups' => $emailGroups,
        ]);
    }

    /**
     * Create standard email. For automation email please use
     * another method.
     *
     * @param Request $request
     * @return JsonResponse
     * @throws \Exception
     */
    public function createStandardEmail(Request $request): JsonResponse
    {
        $currentAccountId = $this->getCurrentAccountId();
        $emailName = $request->input('email');
        $existingEmail = Email
            ::where('account_id', $currentAccountId)
            ->where('type', 'Standard')
            ->where('name', $emailName)
            ->first();

        // TODO: use validation
        if ($existingEmail) {
            return response()->json([
                'message' => 'This email name has been taken.',
            ], 422);
        }

        $email = Email::create([
            'reference_key' => $this->refKeyService->getRefKey(new Email),
            'account_id' => $currentAccountId,
            'type' => "Standard",
            'name' => $emailName,
        ]);

        return response()->json([
            'reference_key' => $email->reference_key
        ]);
    }

    /**
     * Create email template.
     *
     * @return JsonResponse
     * @throws \Exception
     */
    public function createTemplateEmail(): JsonResponse
    {
        $email = Email::create([
            'reference_key' => $this->refKeyService->getRefKey(new Email),
            'type' => "Template",
            'email_status_id' => 5
        ]);

        return response()->json([
            'reference_key' => $email->reference_key
        ]);
    }

    /**
     * Create or update automation email only.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function createOrUpdateAutomationEmail(Request $request): JsonResponse
    {
        $request->validate([
            'emailEntity.email_reference_key' => 'nullable|int',
            'emailEntity.name' => 'nullable|string',
            'emailEntity.subject' => 'nullable|string',
            'emailEntity.preview' => 'nullable|string',

            // sender
            'emailEntity.sender_name' => 'nullable|string',
            'sender.id' => 'nullable|int',
            'sender.email_address' => 'nullable|email'
        ]);

        $email = null;
        $newSender = null;

        DB::transaction(function () use ($request, &$email, &$newSender) {
            $emailEntity = $request->emailEntity;
            $sender = $request->sender;
            $currentAccountId = $this->getCurrentAccountId();

            $email = Email::updateOrCreate(
                [
                    'account_id' => $currentAccountId,
                    'reference_key' => $emailEntity['email_reference_key'] ?? $this->refKeyService->getRefKey(new Email),
                ],
                [
                    'type' => 'Automation',
                    'name' => $emailEntity['name'],
                    'email_status_id' => 4, // 4 for in use, maybe will change in the future
                    'subject' => $emailEntity['subject'],
                    'preview_text' => $emailEntity['preview'],
                    'sender_name' => $emailEntity['sender_name'],
                ]
            );

            if (!isset($sender['email_address'])) {
                return;
            }

            // update sender if matched email & account_id, else create new one
            $newSender = Sender::updateOrCreate(
                [
                    'account_id' => $currentAccountId,
                    'email_address' => $sender['email_address']
                ],
                [
                    'name' => $sender['name'],
                    'email_address' => $sender['email_address']
                ]
            );

            $email->update(['sender_id' => $newSender->id]);
            $email->emailId = $email->id;
            $email->emailDesign = $email->emailDesign;
            $email->hasRequiredMergeTags = !(new EmailMergeTagService)->checkIfRequiredMergeTagsAbsent($email);
        });

        return response()->json([
            'email' => $email,
            'sender' => $newSender
        ]);
    }

    /**
     * Straight and simple, update one email's field
     * Warning: NO VALIDATION PROVIDED, use with care
     *
     * Non-related values from $request->all() will be
     * filtered automatically by Laravel, follows attributes stated
     * in $fillable property in App\Email.
     *
     * @param \App\Email $email
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Email $email, Request $request): JsonResponse
    {
        $email->update($request->all());

        return response()->json([
            'message' => 'Successfully updated email'
        ]);
    }

    /**
     * Custom method for update email name to show duplicated name error
     *
     * @param \App\Email $email
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateName(Email $email, Request $request): JsonResponse
    {
        // duplicated name error on "Standard type" email in same account
        $request->validate([
            'name' => 'required|unique:emails,name,' . $email->id . ',id,account_id,' . $email->account_id . ',type,Standard',
        ]);

        $email->update(['name' => $request->name]);

        return response()->json([
            'message' => 'Successfully updated email name'
        ]);
    }

    /**
     * Specially created for updating schedule. Only update schedule
     * after all email setups are completed.
     *
     * @param \App\Email $email
     * @param \Illuminate\Http\Request $request
     * @param \App\Services\EmailService $emailService
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateSchedule(Email $email, Request $request, EmailService $emailService): JsonResponse
    {
        $validatedData = $request->validate([
            'schedule' => 'required|string'
        ]);
        $schedule = $validatedData['schedule'];

        if ($emailService->checkIfAnyEmptyEmailSetup($email)) {
            return response()->json([
                'message' => 'Please fill up all email setups before schedule'
            ], 406); // 406 to avoid conflict with 422 in validation error above
        }

        $scheduledEmailStatusId = EmailStatus::whereStatus('Scheduled')
            ->firstOrFail()
            ->id;

        $email->update([
            'schedule' => $schedule,
            'email_status_id' => $scheduledEmailStatusId
        ]);

        return response()->json([
            'message' => 'Successfully updated email schedule'
        ]);
    }

    /**
     * Specially created for cancel schedule.
     *
     * @param \App\Email $email
     * @param \Illuminate\Http\Request $request
     * @param \App\Services\EmailService $emailService
     * @return \Illuminate\Http\JsonResponse
     */
    public function cancelSchedule(Email $email): JsonResponse
    {

        $draftEmailStatusId = EmailStatus::whereStatus('Draft')
            ->firstOrFail()
            ->id;

        $email->update([
            'schedule' => null,
            'email_status_id' => $draftEmailStatusId
        ]);

        return response()->json([
            'message' => 'Successfully cancel email schedule'
        ]);
    }

    /**
     * Display email setup page view for editing
     *
     * @param \App\Email $email
     * @param EmailMergeTagService $emailMergeTagService
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function editStandardEmail(Email $email, EmailMergeTagService $emailMergeTagService)
    {
        $this->authorize('view', $email);

        $currentAccountId = Auth::user()->currentAccountId;
        $segments = Segment::where('account_id', $currentAccountId)->get();
        $tags = ProcessedTag::whereAccountId($currentAccountId)->get();

        // Note: this also true for non-existence email design,
        //       in case user doesn't design email yet
        $requiredMergeTagsAbsent = $emailMergeTagService->checkIfRequiredMergeTagsAbsent($email);

        return Inertia::render('email/pages/Setup', [
            'dbEmail' => $email,
            'dbSender' => $email->sender ?? (object)[],
            'dbSegments' => $segments ?? array(),
            'dbTags' => $tags ?? array(),
            'requiredMergeTagsAbsent' => $requiredMergeTagsAbsent,
        ]);
    }

    /**
     * Simply delete email
     *
     * @param \App\Email $email
     * @return Response
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function deleteEmail(Email $email): Response
    {
        $this->authorize('delete', $email);

        $emailDesign = $email->emailDesign;

        if ($emailDesign) {
            $emailDesign->delete();
        }

        $email->delete();

        return response()->noContent();
    }

    /**
     * Verify sender email address by using Amazon SES
     *
     * @param Request $request Request object
     * @return mixed
     */
    public function verifySenderDomain(Request $request)
    {
        $currentAccountId = $this->getCurrentAccountId();
        $sender = Sender
            ::where('account_id', $currentAccountId)
            ->where('email_address', $request->input('senderEmail'))
            ->first();
        $emailAddress = optional($sender)->email_address ?? $request->input('senderEmail');

        // no need to verify again if the sender email is verified before
        if ($this->senderStatusVerified($sender)) {
            return response()->json(['message' => 'This email address is already verified.'], 403);
        }

        // warn user if empty email is provided
        // Note: the message is used in front-end to show alert
        if (!$emailAddress) {
            return response()->json(['message' => 'Email address is empty. Please enter a valid email address to verify.'], 403);
        }

        $ses = AwsFacade::createClient('ses');

        try {
            // TemplateName please refer to:
            // resources/view/emailTemplates/sesCustomVerificationEmail/sesCustomVerificationEmail.json
            $ses->sendCustomVerificationEmail([
                'EmailAddress' => $emailAddress,
                'TemplateName' => $this->getSesCustomVerificationTemplateName()
            ]);

            // create a new sender with pending status
            // Pending status is changeable to verified if user
            // refreshed sender
            $newSender = Sender::create([
                'account_id' => $currentAccountId,
                'name' => null,
                'email_address' => $emailAddress,
                'status' => 'pending',
            ]);

            // put email in session for the use in /emails/verification view later
            session()->put('lastEmailVerifiedAccountId', $currentAccountId);
            session()->put('lastEmailVerified', $emailAddress);
        } catch (\Exception $ex) {
            Log::error('Amazon SES email verification exception', ['message' => $ex]);
            return response()->json([
                'message' => 'Failed to send verification email. Please try again or contact support'
            ], 500);
        }

        return response()->json([
            'message' => 'A verification email is sent to ' . $emailAddress . '. Please check your email inbox.',
            'sender' => $newSender
        ]);
    }

    /**
     * Save sender data (e.g. name and VERIFIED email address)
     * Fail to save if the sender email address is not verified
     * in Amazon SES
     *
     * @param \App\Email $email
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    // public function saveSender(Email $email, Request $request): JsonResponse // expect to return JsonResponse
    public function saveSender(Email $email, Request $request): JsonResponse
    {
        $currentAccountId = $this->getCurrentAccountId();
        $senderName = $request->senderName;
        $senderEmail = $request->senderEmail;
        $isLocal = app()->environment('local');

        // ensure that user must verify sender email first
        // before he/she can save any sender
        //
        // Now this SES verification only make on staging & production level
        // to ease local testing
        if (!$isLocal && !$this->sesCheckEmailVerified($senderEmail)) {
            return response()->json([
                'message' => 'Failed to save sender data. Please verify your email address before saving.'
            ], 422);
        }

        $sender = Sender::updateOrCreate([
            'account_id' => $currentAccountId,
            'email_address' => $senderEmail,
        ], [
            'status' => 'verified'
        ]);

        $email->sender_id = $sender->id;
        $email->sender_name = $senderName;

        return response()->json([
            'message' => 'Successfully saved sender.',
            'senderId' => $sender->id
        ]);
    }

    /**
     * Save segment_id (recipients) for email. If the recipients
     * are all contacts, then the segment_id is 'Everyone'
     *
     * @param \App\Email $email
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function saveSegment(Email $email, Request $request)
    {
        $segmentIdFromReq = $request->segmentId;

        // Note: segment_id of email can be 'Everyone', which
        //       represents all contacts. Don't ask me why, go
        //       and ask the person who designed segments table
        $email->segment_id = $segmentIdFromReq === 'Everyone'
            ? $segmentIdFromReq
            : Segment::findOrFail($segmentIdFromReq)->id;
    }

    /**
     * Save email subject and preview
     *
     * @param \App\Email $email
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function saveSubjectPreview(Email $email, Request $request)
    {
        $subject = $request->subject;
        $preview = $request->preview;

        $email->update([
            'subject' => $subject,
            'preview_text' => $preview
        ]);
    }

    /**
     * Save setup values in email setup page
     *
     * @param \App\Email $email
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function saveSetup(Email $email, Request $request): JsonResponse
    {

        if (isset($request->senderEmail)) {
            $response = $this->saveSender($email, $request);
            if ($response->status() === 422)
                abort(500, $response->getData()->message);
        }

        $this->saveSubjectPreview($email, $request);

        $email->target = $request->target;
        $email->tag_id = $request->tagId;
        $email->segment_id = $request->segmentId;
        $email->save();

        return response()->json([
            'message' => 'Successfully saved.'
        ]);
    }

    /**
     * Duplicate an email, including email design, but not email sent
     *
     * @param Email $email
     * @return JsonResponse
     * @throws \Exception
     */
    public function duplicateEmail(Email $email): JsonResponse
    {
        $newEmailDesign = $email->emailDesign->replicate()->fill([
            'reference_key' => $this->refKeyService->getRefKey(new EmailDesign)
        ]);
        $newEmailDesign->save(); // must save first to get id

        $newEmail = $email->replicate()->fill([
            'reference_key' => $this->refKeyService->getRefKey(new Email),
            'name' => "{$email->name} copy",
            'email_status_id' => 1,
            'email_design_id' => $newEmailDesign->id,
            'schedule' => null,
        ]);
        $newEmail->save();

        return response()->json([
            'emails' => Email
                ::where('account_id', $email->account_id)
                ->where('type', 'Standard')
                ->get()
        ]);
    }

    /**
     * Email settings page
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function showSettings()
    {
        $matchAccountId = [
            'account_id' => $this->getCurrentAccountId()
        ];

        $allBouncedEmailAddresses = EmailBounce::where($matchAccountId)->get()->map(function ($bounced) {
            switch ($bounced->type) {
                default:
                case 'bounce':
                    $type = 'Bounced';
                    break;
                case 'complaint':
                    $type = 'Mark as spam';
                    break;
                case 'manual':
                    $type = 'Manually Added';
                    break;
            }

            return [
                'id' => $bounced->id,
                'emailAddress' => $bounced->email_address,
                'type' => $type,
                'createdAt' => $bounced->created_at,
            ];
        });
        $allUnsubEmailAddresses = EmailUnsubscribe::where($matchAccountId)->get()->map(function ($unsubPeople) {
            return [
                'id' => $unsubPeople->id,
                'emailAddress' => ProcessedContact::withTrashed()->find($unsubPeople->processed_contact_id)->email,
                'type' => 'Unsubscribed',
                'createdAt' => $unsubPeople->created_at,
            ];
        });

        $suppressionEmailAddresses = array_merge($allBouncedEmailAddresses->toArray(), $allUnsubEmailAddresses->toArray());
        $suppressionEmailAddresses = collect($suppressionEmailAddresses)->sortBy('createdAt')->unique('emailAddress')->values()->all();

        return Inertia::render('setting/pages/EmailSetting', [
            'dbSenders' => Sender::where($matchAccountId)->get(),
            'hasEmailAffiliateBadge' => (Account::find($this->getCurrentAccountId())->has_email_affiliate_badge) == '1',
            'suppressionEmailAddresses' => collect($suppressionEmailAddresses)->unique('emailAddress'),
        ]);
    }

    public function addSuppressionEmail(Request $request): JsonResponse
    {
        $emailAddresses = $request->emailAddresses;

        DB::beginTransaction();
        try {
            $emailBounceArray = [];

            foreach ($emailAddresses as $emailAddress) {
                $emailBounce = EmailBounce::firstOrCreate(
                    [
                        'account_id' => $this->getCurrentAccountId(),
                        'email_address' => $emailAddress
                    ],
                    [
                        'source_address' => Auth::user()->email,
                        'type' => 'manual'
                    ]
                );

                $emailBounceArray[] = $emailBounce;
            }

            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
            return response()->json([
                'status' => 'failed',
                'message' => 'Something went wrong.'
            ], 406);
        }

        $updatedSuppresionList = array_map(function ($bounced) {
            return [
                'id' => $bounced->id,
                'emailAddress' => $bounced->email_address,
                'type' => 'Manually Added',
                'createdAt' => $bounced->created_at,
            ];
        }, $emailBounceArray);

        return response()->json([
            'status' => 'success',
            'bouncedEmail' => $updatedSuppresionList,

        ]);
    }

    public function deleteSuppressionEmail($id, $type)
    {
        if ($type === 'Unsubscribed') {
            $email = EmailUnsubscribe::find($id);
        } else {
            $email = EmailBounce::find($id);
        }
        $email->delete();

        return response()->noContent();
    }


    public function renameEmail(Email $email, Request $request)
    {
        $request->validate([
            'name' => 'required|unique:emails,name|max:191'
        ]);

        $email->update([
            'name' => $request->name,
        ]);
    }

    public function createEmailGroup($name)
    {
        $validator = Validator::make(['name' => $name], ['name' => 'required|string|max:191']);

        if (!empty($validator->errors()->first())) {
            abort(422, $validator->errors()->first());
        }

        $accountId = $this->getCurrentAccountId();
        EmailGroup::updateOrCreate(['account_id' => $accountId, 'name' => $name]);
        return response()->json([
            'emailGroups' => $this->getEmailGroups(),
        ]);
    }

    public function deleteEmailGroup($name)
    {
        $validator = Validator::make(['name' => $name], ['name' => 'required']);

        if (!empty($validator->errors()->first())) {
            abort(422, $validator->errors()->first());
        }

        EmailGroup::where(['account_id' => $this->getCurrentAccountId(), 'name' => $name])->delete();
        return response()->json([
            'emails' => $this->getStandardEmail(),
            'emailGroups' => $this->getEmailGroups(),
        ]);
    }

    public function assignEmailGroup(Request $request)
    {
        $isDelete = $request->action === 'delete';
        $selectedGroup = EmailGroup::firstWhere('name', $request->name);
        $emailIds = $request->emailIds;
        if (!isset($selectedGroup) || !isset($emailIds) || count($emailIds) === 0) return;

        Email::with('emailGroups:id')->find($emailIds)->each(function ($email) use ($selectedGroup, $isDelete) {
            $groupIds = $email->emailGroups->pluck('id')->toArray();
            if ($isDelete) {
                $groupIds = array_filter($groupIds, function ($id) use ($selectedGroup) {
                    return $id !== $selectedGroup->id;
                });
            } else {
                $groupIds[] = $selectedGroup->id;
            }
            $email->emailGroups()->sync($groupIds);
        });

        return response()->json(['emails' => $this->getStandardEmail()]);
    }


    /**
     * To override the problematic getCurrentAccountId in AuthAccountTrait
     *
     * @return mixed
     */
    private function getCurrentAccountId()
    {
        return Auth::user()->currentAccountId;
    }

    private function getStandardEmail()
    {
        return Email::where('account_id', $this->getCurrentAccountId())
            ->where('type', 'Standard')
            ->get();
    }

    private function getEmailGroups($property = 'name')
    {
        return EmailGroup::where('account_id', $this->getCurrentAccountId())->pluck($property)->toArray();
    }
}
