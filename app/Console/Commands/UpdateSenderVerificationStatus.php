<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Aws\Laravel\AwsFacade;
use App\Sender;

class UpdateSenderVerificationStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update:sender-verification-status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check and update verification status of sender from AWS SES';

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
     * @return int
     */
    public function handle()
    {
        $ses = AwsFacade::createClient('ses');
        $senders = Sender::all();
        $emailList = $senders->pluck('email_address')->toArray();

        $result = $ses->getIdentityVerificationAttributes([
            'Identities' => $emailList
        ]);

        $verificationAttributes = $result['VerificationAttributes'];

        $senders->each(function ($sender) use ($verificationAttributes) {
            $email = $sender->email_address;
            // If email not in AWS SES verified identities list
            if (!isset($verificationAttributes[$email])) {
                $sender->update(['status' => 'pending']);
                return;
            }
            $verifiedStatus = $verificationAttributes[$email]['VerificationStatus'];
            if ($verifiedStatus !== 'Success')  $sender->update(['status' => 'pending']);
        });
    }
}
