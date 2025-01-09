<?php

/** @noinspection StaticInvocationViaThisInspection */

namespace App\Listeners;

use App\Events\CartAbandoned;
use App\Events\ContactEnteredSegment;
use App\Events\ContactExitedSegment;
use App\Events\LandingFormSubmitted;
use App\Events\OrderMade;
use App\Events\OrderPlaced;
use App\Events\ProductPurchased;
use App\Events\TagAddedToContact;
use App\Events\TagRemovedFromContact;
use App\Events\TriggerDateReached;
use App\Jobs\ProcessTriggeredSteps;
use App\peopleCustomField;
use App\ProcessedContact;
use App\Segment;
use App\Services\AutomationService;
use App\Services\RefKeyService;
use App\Services\SegmentService;
use App\Traits\Automations\TriggerableLandingForm;
use App\Traits\Automations\TriggerableProduct;
use App\Traits\Automations\TriggerableTag;
use App\Traits\CurrencyConversionTraits;
use App\Traits\OrderTrait;
use App\Traits\SegmentTrait;
use Carbon\Carbon;
use Carbon\Exceptions\InvalidFormatException;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Events\Dispatcher;

class AutomationTriggerEventSubscriber implements ShouldQueue
{
    use TriggerableLandingForm, TriggerableProduct, TriggerableTag, CurrencyConversionTraits, OrderTrait, SegmentTrait;

    private $automationService;
    private $refKeyService;
    private $segmentService;

    private $accountId;

    public function __construct(
        AutomationService $automationService,
        RefKeyService $refKeyService,
        SegmentService $segmentService
    ) {
        $this->automationService = $automationService;
        $this->refKeyService = $refKeyService;
        $this->segmentService = $segmentService;
    }

    /**
     * @param LandingFormSubmitted $event
     */
    public function handleLandingFormSubmitted(LandingFormSubmitted $event): void
    {
        $landingPageForm = $event->landingPageForm;
        $processedContact = $event->processedContact;

        $automations = $this->automationService->getTriggeredAutomations(
            'submit_form',
            $landingPageForm->account_id
        );

        foreach ($automations as $automation) {
            if (
                !$this->automationService->isAutomationTriggerable($automation, $processedContact) ||
                !$this->matchLandingPageFormId($automation, $landingPageForm->id)
            ) {
                continue;
            }

            $this->automationService->createTriggeredSteps($automation, $automation->steps, [
                'processed_contact_id' => $processedContact->id
            ]);
        }

        ProcessTriggeredSteps::dispatch();
    }

    /**
     * @param ProductPurchased $event
     * @deprecated
     *
     * Don't use this method anymore, keep for reference only
     *
     */
    public function handleProductPurchased(ProductPurchased $event): void
    {
        $order = $event->order;
        $processedContact = $event->processedContact;

        $automations = $this->automationService->getTriggeredAutomations(
            'purchase_product',
            $order->account_id
        );

        foreach ($automations as $automation) {
            if (
                !$this->automationService->isAutomationTriggerable($automation, $processedContact) ||
                !$this->matchUsersProductId($automation, $order)
            ) {
                continue;
            }

            $this->automationService->createTriggeredSteps($automation, $automation->steps, [
                'processed_contact_id' => $processedContact->id
            ]);
        }

        ProcessTriggeredSteps::dispatch();
    }

    /**
     * @param TriggerDateReached $event
     * @throws \Exception
     */
    public function handleTriggerDateReached(TriggerDateReached $event): void
    {
        $eventDate = $event->date;
        $automations = $this->automationService->getEveryTriggeredAutomations('date_based');

        foreach ($automations as $automation) {
            if (
                !$this->automationService->isAutomationActivated($automation) ||
                !$this->automationService->isAutomationExecutable($automation)
            ) {
                continue;
            }

            // here I only pick the first trigger to settle this quicker. If in the future multiple
            // triggers is implemented you can change this
            $automationTrigger = $automation->automationTriggers()->first();
            $dateBasedTriggerKind = $automationTrigger->automationDateBasedTrigger ?? null;

            if (!$dateBasedTriggerKind) continue;

            $direction = $dateBasedTriggerKind->execution_time_direction;
            $accountContacts = ProcessedContact::where('account_id', $automation->account_id)->get();
            $matchedContactIds = collect();

            foreach ($accountContacts as $contact) {
                switch ($dateBasedTriggerKind->target) {
                    case 'people_profile_birthday':
                        $birthday = $contact->birthday;
                        if (!$birthday) continue 2;
                        $dateToCompare = Carbon::parse($birthday);
                        break;

                    case 'people_profile_acquisition_date':
                        $dateToCompare = Carbon::parse($contact->created_at);
                        break;

                    case 'people_profile_date_type_custom_field':
                        $customField = peopleCustomField
                            ::with('peopleCustomFieldName')
                            ->where('processed_contact_id', $contact->id)
                            ->whereHas('peopleCustomFieldName', function ($query) {
                                $query->where('type', 'date');
                            })
                            ->first();

                        if (is_null($customField)) {
                            continue 2;
                        }

                        try {
                            $dateToCompare = Carbon::parse($customField->custom_field_content);
                        } catch (InvalidFormatException $ex) {
                            \Log::error('Invalid format in date type custom field', [
                                'msg' => $ex->getMessage(),
                                'people_custom_field_id' => $customField->id
                            ]);
                            continue 2;
                        }

                        break;

                    case 'specific_date':
                        $dateToCompare = Carbon::parse($dateBasedTriggerKind->target_specific_date);
                        break;

                    default:
                        throw new \Exception('Unknown target');
                }

                // adjust $dateToCompare based on period & unit if direction isn't 'on'
                if ($direction !== 'on') {
                    $period = $dateBasedTriggerKind->execution_time_period;
                    $unit = $dateBasedTriggerKind->execution_time_unit;

                    $method = ($direction === 'before' ? 'sub' : 'add') . ucfirst($unit);
                    $dateToCompare = $dateToCompare->$method($period);
                }

                // check whether date is the same day first
                if ($dateToCompare->format('md') !== $eventDate->format('md')) {
                    continue;
                }

                // then check for yearly repetition
                // skip if not repeat yearly and is triggered
                if (
                    !$dateBasedTriggerKind->repeat_yearly &&
                    !is_null(
                        $dateBasedTriggerKind
                            ->processedContacts()
                            ->find($contact->id)
                            ->pivot
                            ->triggered_at ?? null
                    )
                ) {
                    continue;
                }

                $matchedContactIds->push($contact->id);
                $dateBasedTriggerKind->processedContacts()->syncWithoutDetaching(
                    [$contact->id => ['triggered_at' => $eventDate]]
                );
            }

            $segment = $automationTrigger->segment;

            $segmentContactIds = !$segment
                ? $accountContacts->pluck('id')
                : $segment->contacts(true);

            $resultContactIds = $matchedContactIds->intersect($segmentContactIds);

            foreach ($resultContactIds as $contactId) {
                $this->automationService->createTriggeredSteps($automation, $automation->steps, [
                    'processed_contact_id' => $contactId
                ]);
            }
        }

        ProcessTriggeredSteps::dispatch();
    }

    /**
     * @param TagAddedToContact $event
     */
    public function handleTagAddedToContact(TagAddedToContact $event): void
    {
        $processedTag = $event->processedTag;
        $processedContactIds = $event->processedContactIds;

        $automations = $this->automationService->getTriggeredAutomations(
            'add_tag',
            $processedTag->account_id
        );

        foreach ($automations as $automation) {
            foreach ($processedContactIds as $processedContactId) {
                $processedContact = ProcessedContact::find($processedContactId);

                if (
                    !$this->automationService->isAutomationTriggerable($automation, $processedContact) ||
                    !$this->matchProcessedTagId($automation, $processedTag, 'automationAddTagTrigger')
                ) {
                    continue;
                }

                $this->automationService->createTriggeredSteps($automation, $automation->steps, [
                    'processed_contact_id' => $processedContact->id
                ]);
            }
        }

        ProcessTriggeredSteps::dispatch();
    }

    public function handleTagRemovedFromContact(TagRemovedFromContact $event): void
    {
        $processedTag = $event->processedTag;
        $processedContactIds = $event->processedContactIds;

        $automations = $this->automationService->getTriggeredAutomations(
            'remove_tag',
            $processedTag->account_id
        );

        foreach ($automations as $automation) {
            foreach ($processedContactIds as $processedContactId) {
                $processedContact = ProcessedContact::find($processedContactId);

                if (
                    !$this->automationService->isAutomationTriggerable($automation, $processedContact) ||
                    !$this->matchProcessedTagId($automation, $processedTag, 'automationRemoveTagTrigger')
                ) {
                    continue;
                }

                $this->automationService->createTriggeredSteps($automation, $automation->steps, [
                    'processed_contact_id' => $processedContact->id
                ]);
            }
        }

        ProcessTriggeredSteps::dispatch();
    }

    /**
     * @param OrderMade $event
     * @deprecated
     *
     * Don't use this method anymore, keep for reference only
     *
     */
    public function handleOrderSpent(OrderMade $event): void
    {
        $order = $event->order;
        $processedContact = $order->processedContact;
        $this->setAccountId($processedContact->account_id);

        // to deal with some weird situations where no contact is
        // specified upon order creation
        if (!$processedContact) {
            return;
        }

        // Note: convertCurrency here will use getAccountId() from this class, hence prevent
        //       some errors that will happen in queue
        $orderSpent = $this->convertCurrency($order->total, $order->currency);

        $automations = $this->automationService->getTriggeredAutomations(
            'order_spent',
            $order->account_id
        );

        foreach ($automations as $automation) {
            if (!$this->automationService->isAutomationTriggerable($automation, $processedContact)) {
                continue;
            }

            $automationTrigger = $automation->automationTriggers()->first();
            $orderSpentKind = $automationTrigger->automationOrderSpentTrigger ?? null;

            if (!$orderSpentKind) {
                continue;
            }

            $operator = $orderSpentKind->operator;
            $expectedSpent = $orderSpentKind->spent; // this order spent is always in account default currency

            if ($operator === 'greater than or equal to') {
                $orderSpentMatched = $orderSpent >= $expectedSpent;
            } else if ($operator === 'less than or equal to') {
                $orderSpentMatched = $orderSpent <= $expectedSpent;
            } else {
                $orderSpentMatched = $orderSpent == $expectedSpent;
            }

            if (!$orderSpentMatched) {
                continue;
            }

            $this->automationService->createTriggeredSteps($automation, $automation->steps, [
                'processed_contact_id' => $processedContact->id
            ]);
        }

        // reset accountId, just in case
        $this->setAccountId(null);
        ProcessTriggeredSteps::dispatch();
    }

    /**
     * @param \App\Events\OrderPlaced $event
     */
    public function handleOrderPlaced(OrderPlaced $event): void
    {
        $order = $event->order;
        $processedContact = $order->processedContact;
        $this->setAccountId($processedContact->account_id);

        // to deal with some weird situations where no contact
        if (!$processedContact) {
            return;
        }

        // Note: convertCurrency here will use getAccountId() from this class, hence prevent
        //       some errors that will happen in queue
        $amountSpent = $this->convertCurrency($order->total, $order->currency, false, false, $processedContact->account_id);

        $automations = $this->automationService->getTriggeredAutomations(
            'place_order',
            $order->account_id
        );

        foreach ($automations as $automation) {
            if (!$this->automationService->isAutomationTriggerable($automation, $processedContact)) {
                continue;
            }

            $automationTrigger = $automation->automationTriggers()->first();
            $placeOrderKind = $automationTrigger->automationPlaceOrderTrigger ?? null;

            if (!$this->isContactInSegment($automation,$order) || !$placeOrderKind) {
                continue;
            }

            $filters = $placeOrderKind->filters;
            $orderMatched = true;
            $availableTypes = [
                'product_name',
                'product_category',
                'total_sales',
                'sales_channel',
                'payment_status',
                'fulfillment_status'
            ];

            // note that if $filters is empty array, $orderMatched will be automatically true
            // without running this loop
            foreach ($filters as $filter) {
                $type = $filter['type'];
                $condition = $filter['condition'];

                /**
                 * Note: these values are case-sensitive (for string)
                 *
                 * Equivalent of $value of all types:
                 * - product_name: users_product_id
                 * - product_category: category_id
                 * - total_sales: user-defined number
                 * - sales_channel: 'Online Store', 'Mini Store', 'Funnel'
                 * - payment_status: 'Paid', 'Unpaid'
                 * - fulfillment_status: 'Fulfilled', 'Unfulfilled'
                 */
                $value = $filter['value'];

                // just in case type is wrong and cause logic error
                if (!in_array($type, $availableTypes, true)) {
                    \Log::warning('Place an order trigger type not found in available types', [
                        'type' => $type,
                        'available_types' => $availableTypes,
                        'order_id' => $order->id
                    ]);
                    continue;
                }

                if ($type === 'product_name') {
                    $containProduct = $order->orderDetails->pluck('users_product_id')->contains($value);
                    $orderMatched = $condition === 'is' ? $containProduct : !$containProduct;
                }

                if ($type === 'product_category') {
                    $containCategory = $order
                        ->orderDetails
                        ->pluck('usersProduct.categories.*.id')
                        ->flatten(1)
                        ->contains($value);
                    $orderMatched = $condition === 'is' ? $containCategory : !$containCategory;
                }

                if ($type === 'total_sales') {
                    $totalSales = $this->calculateOrdersTotalSales((collect([$order])));
                    if ($condition === 'greater than or equal to') {
                        $orderMatched = $totalSales >= $value;
                    } else if ($condition === 'less than or equal to') {
                        $orderMatched = $totalSales <= $value;
                    } else if ($condition === 'between') {
                        $orderMatched = $totalSales > $value && $totalSales < $value;
                    } else {
                        $orderMatched = $totalSales == $value;
                    }
                }

                if ($type === 'sales_channel') {
                    $matchedChannel = $value === $order->acquisition_channel;
                    $orderMatched = $condition === 'is' ? $matchedChannel : !$matchedChannel;
                }

                if ($type === 'payment_status') {
                    $matchedPaymentStatus = $value === $order->payment_status;
                    $orderMatched = $condition === 'is' ? $matchedPaymentStatus : !$matchedPaymentStatus;
                }

                if ($type === 'fulfillment_status') {
                    $matchedFulfillmentStatus = $value === $order->fulfillment_status;
                    $orderMatched = $condition === 'is'
                        ? $matchedFulfillmentStatus
                        : !$matchedFulfillmentStatus;
                }

                // one false is sufficient to conclude that order not matched
                // since filters conditions are AND
                if (!$orderMatched) {
                    break;
                }
            }

            if ($orderMatched) {
                $this->automationService->createTriggeredSteps($automation, $automation->steps, [
                    'processed_contact_id' => $processedContact->id
                ]);
            }
        }

        // reset accountId, just in case
        $this->setAccountId(null);
        ProcessTriggeredSteps::dispatch();
    }

    /**
     * @param CartAbandoned $event
     * @throws \Exception
     */
    public function handleCartAbandoned(CartAbandoned $event): void
    {
        $automations = $this->automationService->getEveryTriggeredAutomations('abandon_cart');

        foreach ($automations as $automation) {
            foreach ($event->visitors as $visitor) {

                if ($visitor->account_id !== $automation->account_id) {
                    continue;
                }

                $processedContact = ProcessedContact::firstOrCreate(
                    [
                        'account_id' => $visitor->account_id,
                        'id' => $visitor->processed_contact_id,
                    ],
                    [
                        'contactRandomId' => $this->refKeyService->getRefKey(new ProcessedContact, 'contactRandomId'),
                        'acquisition_channel' => ucwords(str_replace('-', ' ', $visitor['sales_channel'])),
                    ]
                );

                if (!$this->automationService->isAutomationTriggerable($automation, $processedContact)) {
                    continue;
                }

                // skip any triggered abandoned cart regardless of execution
                // (primarily to avoid email spam on frequently updated abandoned cart)
                // Might add a feature to allow user to remove triggered contacts
                if ($automation->triggeredContacts()->find($processedContact->id)) {
                    continue;
                }
                $this->automationService->createTriggeredSteps($automation, $automation->steps, [
                    'processed_contact_id' => $processedContact->id
                ]);
            }
        }

        ProcessTriggeredSteps::dispatch();
    }

    public function handleContactEnteredSegment(ContactEnteredSegment $event)
    {
        $segment = $event->segment;
        $contactIds = $event->contactIds;
        $automations = $this->automationService->getTriggeredAutomations('enter_segment', $segment->account_id);

        $processedContacts = ProcessedContact::find($contactIds);
        foreach ($automations as $automation) {
            foreach ($processedContacts as $processedContact) {
                if (
                    !$this->automationService->isAutomationTriggerable($automation, $processedContact) ||
                    !$this->matchSegmentId($automation, $segment->id, 'enter')
                ) {
                    continue;
                }

                $this->automationService->createTriggeredSteps($automation, $automation->steps, [
                    'processed_contact_id' => $processedContact->id
                ]);
            }
        }

        ProcessTriggeredSteps::dispatch();
    }

    public function handleContactExitedSegment(ContactExitedSegment $event)
    {
        $segment = $event->segment;
        $contactIds = $event->contactIds;

        $automations = $this->automationService->getTriggeredAutomations('exit_segment', $segment->account_id);

        $processedContacts = ProcessedContact::find($contactIds);
        foreach ($automations as $automation) {
            foreach ($processedContacts as $processedContact) {
                if (
                    !$this->automationService->isAutomationTriggerable($automation, $processedContact) ||
                    !$this->matchSegmentId($automation, $segment->id, 'exit')
                ) {
                    continue;
                }

                $this->automationService->createTriggeredSteps($automation, $automation->steps, [
                    'processed_contact_id' => $processedContact->id
                ]);
            }
        }

        ProcessTriggeredSteps::dispatch();
    }

    /**
     * @param $accountId
     */
    private function setAccountId($accountId): void
    {
        $this->accountId = $accountId;
    }

    /**
     * Just to override getAccountId in CurrencyConversionTraits trait
     *
     * @return mixed
     */
    private function getAccountId()
    {
        return $this->accountId;
    }

    /**
     * Register the listeners for the subscriber.
     *
     * Note: both OrderSpent and ProductPurchased are deprecated in favor of
     *       OrderPlaced
     *
     * @param Dispatcher $events
     */
    public function subscribe(Dispatcher $events): void
    {
        $prefix = __CLASS__;

        $events->listen(
            LandingFormSubmitted::class,
            "$prefix@handleLandingFormSubmitted"
        );

        //        $events->listen(
        //            ProductPurchased::class,
        //            "$prefix@handleProductPurchased"
        //        );

        $events->listen(
            TriggerDateReached::class,
            "$prefix@handleTriggerDateReached"
        );

        $events->listen(
            TagAddedToContact::class,
            "$prefix@handleTagAddedToContact"
        );

        $events->listen(
            TagRemovedFromContact::class,
            "$prefix@handleTagRemovedFromContact"
        );

        //        $events->listen(
        //            OrderMade::class,
        //            "$prefix@handleOrderSpent"
        //        );

        $events->listen(
            OrderPlaced::class,
            "$prefix@handleOrderPlaced"
        );

        $events->listen(
            CartAbandoned::class,
            "$prefix@handleCartAbandoned"
        );

        $events->listen(
            ContactEnteredSegment::class,
            "$prefix@handleContactEnteredSegment"
        );

        $events->listen(
            ContactExitedSegment::class,
            "$prefix@handleContactExitedSegment"
        );
    }
}
