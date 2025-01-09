<?php

namespace App\Http\Controllers;

use App\Email;
use App\Services\EmailService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SendEmailController extends Controller
{
    private $emailService;

    public function __construct(EmailService $emailService)
    {
        $this->emailService = $emailService;
    }

    /**
     * Send standard email from messages/emails accordingly to segment.
     * Recipients' email address is obtained from segment's contact.
     *
     * @param \App\Email $email
     * @param \App\Services\EmailService $emailService
     * @return \Illuminate\Http\JsonResponse
     */
    public function sendStandardEmail(Email $email): JsonResponse
    {
        $success = $this->emailService->sendStandardEmail($email);

        if (!$success) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Some info is missing'
            ], 422);
        }

        return response()->json(['status' => 'success']);
    }

    public function sendTestStandardEmail(Email $email, Request $request)
    {
        $this->emailService->sendStandardTestEmail($email, $request->emailAddresses);
        return response()->noContent();
    }
}
