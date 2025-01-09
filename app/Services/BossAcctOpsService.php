<?php

namespace App\Services;

use App\Account;
use App\ProcessedContact;
use App\ProcessedTag;
use App\Subscription;
use App\SubscriptionPlan;
use App\User;
use Log;

class BossAcctOpsService
{
    private $refKeyService;

    /**
     * Emails excluded from syncing. Add more to this
     * array if boss requests more emails to be excluded
     */
    private const EXCLUDED_EMAILS = [
        'steve@rocketlaunch.my',

        // all employee accounts in UsersTableSeeder (as of Feb 2021)
        'gabriel@gmail.com',
        'steve@gmail.com',
        'tommy@gmail.com',
        'zhihong@gmail.com',
        'darren@gmail.com',
        'andy@gmail.com',
        'yeekang@gmail.com',
        'jaylyn@gmail.com',
        'cheejunwong@hotmail.com',
        'junwei@hotmail.com',
        'lewzhenyao00@gmail.com',
        'txuen006@gmail.com'
    ];

    public function __construct(RefKeyService $refKeyService)
    {
        $this->refKeyService = $refKeyService;
    }

    /**
     * Sync all users (excluding EXCLUDED_EMAILS) to boss account processed_contacts.
     *
     * @throws \Exception
     */
    public function syncUsersWithProcessedContacts(): void
    {
        $users = User
            ::with('subscription.subscriptionPlan')
            ->whereNotIn('email', self::EXCLUDED_EMAILS)
            ->get();

        if (!($bossAcct = $this->getBossAccount())) {
            return;
        }

        foreach ($users as $user) {
            if ($bossAcct->processedContacts->firstWhere('email', $user->email)) {
                continue;
            }

            $this->addUserToContacts($user, $bossAcct);
            $this->addOrUpdateSubscriptionTagInContact($user->subscription, $bossAcct->refresh());
        }
    }

    /**
     * Add user created to boss account processed_contacts
     *
     * @param User $user
     * @param Account|null $bossAccount
     * @throws \Exception
     */
    public function addUserToContacts(User $user, ?Account $bossAccount = null): void
    {
        if (!($bossAcct = $bossAccount ?? $this->getBossAccount())) {
            return;
        }

        $userEmail = $user->email;

        if (in_array($userEmail, self::EXCLUDED_EMAILS)) {
            return;
        }

        $bossAcct->processedContacts()->firstOrCreate(
            ['email' => $userEmail],
            [
                'contactRandomId' => $this->refKeyService->getRefKey(new ProcessedContact, 'contactRandomId'),
                'account_id' => $bossAcct->id,
                'customer_id' => $user->customer_id,
                'contactId' => null,
                'name' => "{$user->firstName} {$user->lastName}",
                'fname' => $user->firstName,
                'lname' => $user->lastName,
                'birthday' => null,
                'phone_number' => $user->phone,
                'address' => null,
                'gender' => null,
                'dateCreated' => now(),
                'orderCount' => 0,
                'type' => 'tt',
                'totalSpent' => 0.00, // maybe later set it to subscription payment amount
                'acquisition_channel' => 'Sign up',
            ]
        );
    }

    /**
     * Add/Update tag based on subscription to user contact in boss account
     *
     * @param Subscription $subscription
     * @param Account|null $bossAccount
     */
    public function addOrUpdateSubscriptionTagInContact(Subscription $subscription, ?Account $bossAccount = null): void
    {
        if (!($bossAcct = $bossAccount ?? $this->getBossAccount())) {
            return;
        }

        $newTag = $this->getSubscriptionTag(
            $bossAcct->id,
            $subscription->subscriptionPlan->plan ?? null
        );
        $userContact = $bossAcct
            ->processedContacts
            ->firstWhere('email', $subscription->user->email);

        if (!$newTag || !$userContact) {
            return;
        }

        $oldTag = $userContact
            ->processed_tags
            ->whereIn(
                'tagName',
                SubscriptionPlan::all()->pluck('plan')
            )
            ->first();

        // don't perform any action if old subscription tag equals to new tag
        // since this method might be called on every subscription update
        // even on subscription updates where plan change is not involved
        if (optional($oldTag)->tagName === $newTag->tagName) {
            return;
        }

        // operation below has two main functions:
        // - add a new subscription tag if the contact has no subscription tag attached
        // - delete old tag and add new tag if the contact has subscription tag attached
        $userContact
            ->processed_tags()
            ->toggle([
                $newTag->id,
                $oldTag->id ?? $newTag->id // if old tag doesn't exist, don't delete it
            ]);
    }

    /**
     * Get an existing subscription tag, or create it
     *
     * @param $accountId
     * @param $tagName
     * @return ProcessedTag|\Illuminate\Database\Eloquent\Model
     */
    private function getSubscriptionTag($accountId, $tagName)
    {
        if (!$tagName) {
            return null;
        }

        return ProcessedTag::firstOrCreate(
            [
                'account_id' => $accountId,
                'tagName' => $tagName
            ],
            ['typeOfTag' => 'contact']
        );
    }

    /**
     * @return \App\Account|\Illuminate\Database\Eloquent\Collection|\Illuminate\Database\Eloquent\Model
     */
    private function getBossAccount()
    {
        $bossUser = User::where('email', 'steve@rocketlaunch.my')->first();
        $bossAcct = Account::with('processedContacts.processed_tags')->find(optional($bossUser)->currentAccountId);

        if (!$bossAcct) {
            Log::error("Steve user doesn't have a current account. Please settle this problem ASAP.");
        }

        return $bossAcct;
    }
}