<?php

namespace App\Observers;

use App\Services\BossAcctOpsService;
use App\User;

class UserObserver
{
    private $bossAcctOpsService;

    public function __construct(BossAcctOpsService $bossAcctOpsService)
    {
        $this->bossAcctOpsService = $bossAcctOpsService;
    }

    /**
     * Handle the user "created" event.
     *
     * @param  \App\User  $user
     * @return void
     */
    public function created(User $user): void
    {
        $this->bossAcctOpsService->addUserToContacts($user);
    }
}
