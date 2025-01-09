<?php

namespace App\Http\Controllers\API;

use App\EmailBounce;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AdminEmailReportController extends Controller
{
    public function getBouncesAndComplaints(Request $request)
    {
        $bounces = EmailBounce::orderByDesc('created_at')->get();

        return response()->json($bounces->map(function ($bounce) {
            $eventMessage = json_decode($bounce->event_message, true, 512);

            $type = $eventMessage['eventType'] ?? null;
            $bounceDiagnosticCode = $type === 'Bounce'
                ? ($eventMessage['bounce']['bouncedRecipients'][0]['diagnosticCode'] ?? '-')
                : '-';
            $bounceType = $type === 'Bounce'
                ? ($eventMessage['bounce']['bounceType'] ?? '-')
                : '-';
            $complaintFeedback = $type === 'Complaint'
                ? ($eventMessage['complaint']['complaintFeedbackType'] ?? '-')
                : '-';

            return [
                'id' => $bounce->id,
                'destination' => $bounce->email_address,
                'source' => $bounce->source_address,
                'type' => $type,
                'bounce_diagnostic_code' => $bounceDiagnosticCode,
                'bounce_type' => $bounceType,
                'complaint_feedback' => $complaintFeedback,
                'created_at' => $bounce->created_at->toDateTimeString(),
            ];
        }));
    }
}