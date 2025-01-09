<?php

namespace App\Console\Commands;

use App\Services\BossAcctOpsService;
use Illuminate\Console\Command;

/**
 * This command is just a backup plan if some users have not synced to boss account.
 *
 * Class SyncUsersToBossContacts
 * @package App\Console\Commands
 */
class SyncUsersToBossContacts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'boss:sync';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync users to boss processed_contacts';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @param BossAcctOpsService $bossAcctOpsService
     * @return int
     * @throws \Exception
     */
    public function handle(BossAcctOpsService $bossAcctOpsService)
    {
        $bossAcctOpsService->syncUsersWithProcessedContacts();
    }
}
