<?php

namespace App\Http\Controllers;

use App\Automation;
use App\AutomationStatus;
use App\Email;
use App\LandingPageForm;
use App\Models\EmailReport;
use App\ProcessedTag;
use App\peopleCustomFieldName;
use App\Segment;
use App\Sender;
use App\Services\Automations\AutomationStatistics;
use App\Services\AutomationStepService;
use App\Services\EmailMergeTagService;
use App\Services\RefKeyService;
use App\Traits\AuthAccountTrait;
use App\Trigger;
use App\UsersProduct;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class AutomationController extends Controller
{
    use AuthAccountTrait;

    private $automationStepService;
    private $refKeyService;
    private $descGeneratorServ;

    public function __construct(
        AutomationStepService $automationStepService,
        RefKeyService $refKeyService
    ) {
        $this->automationStepService = $automationStepService;
        $this->refKeyService = $refKeyService;
    }


    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|Response|\Illuminate\View\View|null
     */
    public function index()
    {
        $dbAutomations = Automation::with('automationTriggers')->where('account_id', $this->getCurrentAccountId())->get();
        // return view('automations.index', compact('automations'));//AutomationHome

        foreach ($dbAutomations as &$automation) {
            $automationStatistics = new AutomationStatistics($automation->id);
            $automation->total_entered = $automationStatistics->getTotalEntered();
            $automation->total_pending = $automationStatistics->getTotalPending();
            $automation->total_completed = $automationStatistics->getTotalCompleted();
        }

        return Inertia::render('automation/pages/AutomationHome', compact('dbAutomations'));
    }

    /**
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function create(Request $request): JsonResponse
    {
        $validatedRequest = $request->validate([
            'name' => 'required|unique:automations,name,NULL,id,account_id,' . $this->getCurrentAccountId()
        ]);

        $automation = Automation::create([
            'name' => $validatedRequest['name'],
            'account_id' => $this->getCurrentAccountId(),
            'reference_key' => $this->refKeyService->getRefKey(new Automation),
            'steps_order' => [],
            'steps' => [],
        ]);

        return response()->json([
            'reference_key' => $automation->reference_key,
        ]);
    }

    /**
     * Generic update func for automation
     *
     * @param $reference_key
     * @param \Illuminate\Http\Request $request
     * @return Response
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function update($reference_key, Request $request): Response
    {
        $currentAccountId = $this->getCurrentAccountId();
        $automation = Automation::findByRefKey($reference_key);

        $this->authorize('update', $automation);

        $request->validate([
            'name' => 'required|unique:automations,name,' . $automation->id . ',id,account_id,' . $currentAccountId,
        ]);

        $automation->update($request->all());

        return response()->noContent();
    }

    /**
     * Update automation status, which is automation_status_id in
     * automations
     *
     * @param $reference_key
     * @param \Illuminate\Http\Request $request
     * @return Response
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function updateStatus($reference_key, Request $request): Response
    {
        $request->validate([
            'status' => 'required|string'
        ]);

        $automation = Automation::findByRefKey($reference_key);

        $this->authorize('update', $automation);

        $statusId = AutomationStatus
            ::where('name', $request->input('status'))
            ->firstOrFail()
            ->id;

        $automation->automation_status_id = $statusId;
        $automation->save();

        return response()->noContent();
    }

    /**
     * Display the specified resource.
     *
     * @param $referenceKey
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|Response|\Illuminate\View\View
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function show($referenceKey)
    {
        $currentAccountId = $this->getCurrentAccountId();

        $dbAutomation = Automation
            ::with(['automationTriggers'])
            ->where('reference_key', $referenceKey)
            ->firstOrFail();

        $this->authorize('view', $dbAutomation);

        $dbLandingPageForms = LandingPageForm
            ::where('account_id', $currentAccountId)
            ->select(['id', 'title'])
            ->get();
        $dbSegments = Segment
            ::where('account_id', $currentAccountId)
            ->select(['id', 'segmentName'])
            ->get();
        $dbTags = ProcessedTag
            ::where('account_id', $currentAccountId)
            ->contactTag()
            ->select(['id', 'tagName'])
            ->get();
        $dbTriggers = Trigger
            ::select(['id', 'type', 'name'])
            ->get()
            ->filter(function ($t) {
                return !in_array($t->type, ['purchase_product', 'order_spent']);
            })
            ->values();
        $dbUsersProducts = UsersProduct
            ::where('account_id', $currentAccountId)
            ->select(['id', 'title', 'description'])
            ->get()
            ->unique('title')
            ->toArray();
        $dbUsersProducts = array_values($dbUsersProducts);
        $dbSendEmailActionsEntities = $this->automationStepService->getSendEmailActionsEntities($dbAutomation->steps);
        $dbSenders = Sender::where('account_id', $currentAccountId)
            ->select(['id', 'name', 'email_address', 'status'])
            ->get();
        $dbCustomFieldNames = peopleCustomFieldName
            ::where('account_id', $currentAccountId)
            ->select(['id', 'custom_field_name'])
            ->get();

        $emailMergeTagService = new EmailMergeTagService();
        foreach ($dbSendEmailActionsEntities as &$sendEmail) {
            $email = Email::firstWhere('reference_key', $sendEmail['email_reference_key']);
            $sendEmail['emailId'] = $email->id;
            $sendEmail['emailDesign'] = $email->emailDesign;
            $sendEmail['hasRequiredMergeTags'] = !$emailMergeTagService->checkIfRequiredMergeTagsAbsent($email);
        }

        Email::withCount('sentEmails')
            ->where('account_id', $currentAccountId)
            ->orderBy('id', 'desc')->each(function ($email) {
                optional(EmailReport::firstWhere('email_id', $email->id))
                    ->update(['total_sent' =>  $email->sent_emails_count]);
            });

        $automationStatistics = new AutomationStatistics($dbAutomation->id);
        $overallStatistics = [
            'total_entered' => $automationStatistics->getTotalEntered(),
            'total_pending' => $automationStatistics->getTotalPending(),
            'total_completed' => $automationStatistics->getTotalCompleted(),
        ];
        $stepBasedStatistics = $automationStatistics->triggeredStepProgressGroupByStep;

        return Inertia::render('automation/pages/AutomationBuilder', compact(
            'dbAutomation',
            'dbLandingPageForms',
            'dbSegments',
            'dbTags',
            'dbTriggers',
            'dbUsersProducts',
            'dbSendEmailActionsEntities',
            'dbSenders',
            'dbCustomFieldNames',
            'overallStatistics',
            'stepBasedStatistics',
        ));

        // return view('automations.builder', compact(
        //     'automation',
        //     'landingPageForms',
        //     'segments',
        //     'tags',
        //     'triggers',
        //     'usersProducts',
        //     'sendEmailActionsEntities',
        //     'senders',
        //     'customFieldNames'
        // ));
    }

    /**
     * Remove automation from storage.
     *
     * @param int $id
     * @return Response
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Exception
     */
    public function destroy(int $id): Response
    {
        $automation = Automation::findOrFail($id);

        $this->authorize('delete', $automation);

        // remember to delete residual one-off email
        foreach ($automation->steps as $step) {
            if ($step['kind'] !== 'automationSendEmailAction') {
                continue;
            }

            $this->automationStepService->destroySendEmailEmail($step);
        }

        $automation->delete();
        return response()->noContent();
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
}
