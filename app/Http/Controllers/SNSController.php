<?php

namespace App\Http\Controllers;

use App\EmailBounce;
use App\Email;
use App\EmailSuppressionList;
use App\ProcessedContact;
use App\Services\EmailReportService;
use Aws\Sns\Message;
use Aws\Sns\MessageValidator;
use Aws\Sns\Exception\InvalidSnsMessageException;
use Log;

/**
 * Controller to manage SES event publishing with SNS destination.
 * For the sample data obtained, refer link below:
 *
 * https://docs.aws.amazon.com/ses/latest/DeveloperGuide/event-publishing-retrieving-sns-contents.html
 *
 * Class SNSController
 * @package App\Http\Controllers
 */
class SNSController extends Controller
{
    public function handleSNSMessage(): void
    {
        // Instantiate the Message and Validator
        $message = Message::fromRawPostData();
        $validator = new MessageValidator();

        // Validate the message and log errors if invalid.
        try {
            $validator->validate($message);
        } catch (InvalidSnsMessageException $e) {
            // Pretend we're not here if the message is invalid.
            http_response_code(404);
            Log::error('SNS Message Validation Error: ', ['msg' => $e->getMessage()]);
            return;
        }

        // Check the type of the message and handle the subscription.
        if ($message['Type'] === 'SubscriptionConfirmation') {
            // Confirm the subscription by sending a GET request to the SubscribeURL
            file_get_contents($message['SubscribeURL']);
        }

        // TODO: process complaint
        if ($message['Type'] === 'Notification') {
            // different with SNS message, this message data contains SES specific data
            $eventMessageData = json_decode($message['Message'], true);
            $eventType = $eventMessageData['eventType'] ?? null;

            // don't process any event other than SES
            // can be changed if more events other than SES added in the future
            if (!$eventType) {
                return;
            }

            if ($eventType === 'Bounce') {
                $this->processBounce($eventMessageData);
            }

            if ($eventType === 'Complaint') {
                $this->processComplaint($eventMessageData);
            }
        }
    }

    /**
     * Process bounce event received from SES sending
     *
     * @param $eventMessage array Event message
     */
    public function processBounce(array $eventMessage): void
    {
        $mail = $eventMessage['mail'];
        $bounceAddresses = $mail['destination'];
        $sourceAddress = $mail['source'];

        $accountIdArr = array_filter($eventMessage['mail']['headers'], function ($e) {
            return $e['name'] === 'X-Account-ID';
        });
        $accountId = $accountIdArr[array_key_first($accountIdArr)]['value'];

        $emailIdArr = array_filter($eventMessage['mail']['headers'], function ($e) {
            return $e['name'] === 'X-Email-ID';
        });

        $emailId = $emailIdArr[array_key_first($emailIdArr)]['value'];

        if (!isset($accountId)) {
            return;
        }
        // $accountId = $accountIdHeader[array_keys($accountIdHeader)[0]];

        foreach ($bounceAddresses as $bounceAddress) {
            $emailBounce = EmailBounce::firstOrCreate(
                [
                    'account_id' => $accountId,
                    'email_address' => $bounceAddress,
                ],
                [
                    'source_address' => $sourceAddress,
                    'type' => 'bounce',
                    'event_message' => json_encode($eventMessage)
                ]
            );
            EmailSuppressionList::updateOrCreate(
                [
                    'email_address' => $bounceAddress
                ],
                [
                    'reason' => 'BOUNCE',
                    'updated_at' => now(),
                ]
            );

            if ($emailId) {
                $email = Email::find($emailId);
                (new EmailReportService($email))->updateTotalEmailBounced();

                $processedContact = ProcessedContact::where([
                    'account_id' => $accountId,
                    'email' => $bounceAddress
                ])->first();

                if (!$processedContact) continue; //return if cannot find processed contact

                $emailProcessedContact = $email->processedContacts()->updateExistingPivot($processedContact->id, [
                    'status' => $emailBounce->type,
                ]);
            }
        }
    }

    /**
     * Process complaint event received from SES sending
     *
     * @param array $eventMessage
     */
    public function processComplaint(array $eventMessage): void
    {
        $complainedRecipients = $eventMessage['complaint']['complainedRecipients'];
        $sourceAddress = $eventMessage['mail']['source'];

        $accountIdArr = array_filter($eventMessage['mail']['headers'], function ($e) {
            return $e['name'] === 'X-Account-ID';
        });
        $accountId = $accountIdArr[array_key_first($accountIdArr)]['value'];

        $emailIdArr = array_filter($eventMessage['mail']['headers'], function ($e) {
            return $e['name'] === 'X-Email-ID';
        });

        $emailId = $emailIdArr[array_key_first($emailIdArr)]['value'];
        $email = Email::find($emailId);

        if (!isset($accountId)) {
            return;
        }
        // $accountId = $accountIdHeader[array_keys($accountIdHeader)[0]];

        foreach ($complainedRecipients as $complainedRecipient) {
            // Note: I put this together with email bounce since both
            //       of them are equally excluded from marketing email sending.
            //       Separate complaint from bounce if you want later
            $emailBounce = EmailBounce::firstOrCreate(
                [
                    'account_id' => $accountId,
                    'email_address' => $complainedRecipient['emailAddress'],
                ],
                [
                    'source_address' => $sourceAddress,
                    'type' => 'complaint',
                    'event_message' => json_encode($eventMessage)
                ]
            );
            EmailSuppressionList::updateOrCreate(
                [
                    'email_address' => $complainedRecipient['emailAddress']
                ],
                [
                    'reason' => 'COMPLAINT',
                    'updated_at' => now(),
                ]
            );

            if (isset($email)) {
                (new EmailReportService($email))->updateTotalEmailBounced();

                $processedContact = ProcessedContact::where([
                    'account_id' => $accountId,
                    'email' => $complainedRecipient['emailAddress']
                ])->first();

                if (!$processedContact) continue; //return if cannot find processed contact

                $emailProcessedContact = $email->processedContacts()->updateExistingPivot($processedContact->id, [
                    'status' => $emailBounce->type,
                ]);
            }
        }
    }
}
