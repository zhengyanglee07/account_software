<?php

namespace App\Http\Controllers;

use App\Email;
use App\EmailDesign;
use App\EmailDesignType;
use App\Services\MjmlComponentService;
use App\TemplateStatus;
use App\Services\MjmlRendererService;
use App\Services\RefKeyService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Auth;
use Illuminate\Http\Response;
use Inertia\Inertia;

class EmailDesignController extends Controller
{
    private $mjmlRendererService;
    private $mjmlComponentService;

    public function __construct(
        MjmlRendererService $mjmlRendererService,
        MjmlComponentService $mjmlComponentService
    )
    {
        $this->mjmlRendererService = $mjmlRendererService;
        $this->mjmlComponentService = $mjmlComponentService;
    }

    /**
     * Simply throw all email designs to frontend
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(): JsonResponse
    {
        $currentAccountId = Auth::user()->currentAccountId;

        return response()->json([
            'userTemplates' => EmailDesign::whereAccountId($currentAccountId)->where('email_design_type_id', 2)->get(),
            // built-in templates that aren't belongs to any account
            'buildinTemplates' => EmailDesign::whereNull('account_id')->where('email_design_type_id', 1)->where('template_status_id', 2)->get()
        ]);
    }

    /**
     * View email design. Used in View Email btn (/emails)
     *
     * Edit 16/06/20: add functionality to return email design model
     *
     * @param \App\Email $email Email
     * @param \App\EmailDesign $emailDesign Email design
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Http\JsonResponse|\Illuminate\View\View
     */
    public function show(Email $email, EmailDesign $emailDesign, Request $request)
    {
        $queryParams = $request->all();

        if (count($queryParams) === 0) {
            return view('email.viewEmail', compact('email', 'emailDesign'));
        }

        // move to api in the future
        $fields = explode(',', $queryParams['field']);
        return response()->json([
            'emailDesign' => EmailDesign::select($fields)->where('id', $emailDesign->id)->first()
        ]);
    }

    /**
     * Create new email design. Note about email design type, there are 3
     * types as of June 2020:
     * - builtin-template
     * - user-template
     * - default (created by user)
     *
     * @param \App\Email $email
     * @param \Illuminate\Http\Request $request
     * @param \App\Services\RefKeyService $refKeyService
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function create(Email $email, Request $request, RefKeyService $refKeyService): JsonResponse
    {
        $emailDesignType = $request->input('type');
        $isBuildinTemplate = $emailDesignType == "builtin-template";
        $accountId = $isBuildinTemplate ? null : Auth::user()->currentAccountId;
        $name = $request->input('name'); // optional, used when creating template, only
        $preview = $request->input('preview');
        $emailDesignTypeId = EmailDesignType::where('name', $emailDesignType)->first()->id;
        $templateStatusId = TemplateStatus::firstWhere('name', 'Draft')->id;

        $emailDesign = EmailDesign::create([
            'account_id' => $accountId,
            'reference_key' => $refKeyService->getRefKey(new EmailDesign),
            'name' => $name,
            'email_design_type_id' => $emailDesignTypeId,
            'preview' => $preview,
            'template_status_id' => $isBuildinTemplate ? $templateStatusId : null
        ]);

        // email_design_reference_key will be automatically determined from this
        $email->email_design_id = $emailDesign->id;
        $email->save();

        return response()->json([
            'message' => 'Successfully saved email preview',
            'email_design_reference_key' => $emailDesign->reference_key
        ]);
    }

    /**
     * Create user template for email design
     *
     * @param \App\Email $email
     * @param \Illuminate\Http\Request $request
     * @param \App\Services\RefKeyService $refKeyService
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function createTemplate(Email $email, Request $request, RefKeyService $refKeyService): JsonResponse
    {
        $accountId = Auth::user()->currentAccountId;
        $emailDesignTypeId = EmailDesignType::where('name', 'user-template')->first()->id;

        $validatedInput = $request->validate([
            'name' => 'required|unique:email_designs,name,NULL,name,account_id,' . $accountId,
            'preview' => 'required',
            'mjml' => 'required'
        ]);
        $name = $validatedInput['name'];
        $preview = $validatedInput['preview'];
        $mjml = $validatedInput['mjml'];

        // template doesn't need html (might change in the future, depends)
        // Edit 25/07/2020: html is required now for preview purpose
        $emailtemplate = EmailDesign::create([
            'reference_key' => $refKeyService->getRefKey(new EmailDesign),
            'account_id' => $accountId,
            'email_design_type_id' => $emailDesignTypeId,
            'name' => $name,
            'preview' => $preview,
            'html' => $this->mjmlRendererService->render($mjml),
            'mjml' => $mjml
        ]);

        return response()->json([
            'message' => 'Successfully created template'
        ]);
    }

    /**
     * Delete email template
     *
     * @param \App\EmailDesign $emailDesign
     * @return Response
     * @throws \Exception
     */
    public function deleteTemplate(EmailDesign $emailDesign): Response
    {
        $emailDesign->delete();
        return response()->noContent();
    }

    /**
     * Edit email design (using Vue email builder)
     *
     * @param \App\Email $email
     * @param \App\EmailDesign $emailDesign
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function edit(Email $email, EmailDesign $emailDesign, Request $request)
    {
        $source = strtolower($request->query('source'));
        $refKey = $request->query('key');

        switch ($source) {
            case 'automation' :
                // back to automation builder page
                $exitUrl = '/automations/' . $refKey;
                break;
            case 'standard' :
                // back to email setup page
                $exitUrl = '/emails/standard/' . $refKey . '/edit';
                break;
            case 'template' :
                // back to template index page
                $exitUrl = '/templates';
                break;
            default :
                // fallback to window history back if no valid source is found
                $exitUrl = '';
        }

        return Inertia::render('email-builder/pages/BaseEmailBuilder', compact(
            'email',
            'emailDesign',
            'exitUrl'
        ));
    }

    /**
     * Update email design. Mostly used to update preview
     *
     * @param \App\Email $email Email
     * @param \App\EmailDesign $emailDesign Email design
     * @param \Illuminate\Http\Request $request
     * @return Response
     */
    public function update(Email $email, EmailDesign $emailDesign, Request $request): Response
    {
        $emailDesign->update($request->all());

        $parsedMjml = $this->mjmlComponentService->parse($request->mjml);
        $html = $this->mjmlRendererService->render($parsedMjml);

        $emailDesign->html = $html;
        $emailDesign->save();

        session()->put('mjml-html', $html);

        return response()->noContent();
    }

    /**
     * Update email buildin template details
     *
     * @param \App\Email $email Email
     * @param \App\EmailDesign $emailDesign Email design
     * @param \Illuminate\Http\Request $request
     * @return JsonResponse
     */
    public function updateTemplateDetails(Email $email, EmailDesign $emailDesign, Request $request): JsonResponse
    {
        $templateStatusId = TemplateStatus::firstWhere('name', $request->status)->id;

        $emailDesign->update([
            'name' => $request->name,
            'template_status_id' => $templateStatusId
        ]);

        return response()->json([
            'status' => 'Success',
            'message' => 'Details updated successfully'
        ]);
    }

    /**
     * Flash session to be used in preview() method below
     *
     * @param \Illuminate\Http\Request $request
     * @return Response
     * @throws \Exception
     */
    public function putSessionForPreview(Request $request): Response
    {
        if ($request->exists('mjml')) {
            $mjml = $this->mjmlComponentService->parse($request->mjml);
            $html = $this->mjmlRendererService->render($mjml);
        } else {
            $html = $request->html;
        }

        session()->put('mjml-html', $html);

        return response()->noContent();
    }

    /**
     * Preview MJML generated email HTML
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function preview()
    {
        $html = session()->has('mjml-html') ? session('mjml-html') : '';

        return view('email.preview', compact('html'));
    }

    /**
     * Used to preview email template instead of normal email as in
     * preview method above, which doesn't require interference of
     * MJML
     *
     * @param \App\EmailDesign $emailDesign
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function previewTemplate(EmailDesign $emailDesign)
    {
        return view('email.preview', [
            'html' => $emailDesign->html
        ]);
    }
}
