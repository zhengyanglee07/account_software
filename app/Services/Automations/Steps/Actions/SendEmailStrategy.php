<?php

namespace App\Services\Automations\Steps\Actions;

use App\Services\EmailService;
use App\TriggeredStep;
use App\Email;

class SendEmailStrategy implements IActionStrategy
{
    public function perform(TriggeredStep $triggeredStep): void
    {
        $step = $triggeredStep->step;
        $email = Email::find($step['properties']['email_id'] ?? null);
        $processedContact = $triggeredStep->processedContact;

        if (!$email) {
            \Log::error('Send email strategy', [
                'step' => $step,
            ]);
            throw new RuntimeException('Email must present in SendEmail action step');
        }

        if (!$processedContact) {
            throw new RuntimeException('ProcessedContact not present in TriggeredStep');
        }

        app(EmailService::class)->sendAutomationEmail($email, $processedContact, $triggeredStep->automation_id);
    }
}
