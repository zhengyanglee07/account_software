<?php

namespace App\Console\Commands;

use App\EmailSuppressionList;
use Aws\Laravel\AwsFacade;
use Aws\Result;
use Carbon\Carbon;
use Illuminate\Console\Command;

class UpdateSuppressionList extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update:suppression-list 
                            {--start= : get the list after a specific date}
                            {--end= : get the list before a specific date}
                            {--page-size= : The number of results to get in a single call}
                            {--reason=BOUNCE : The suppression reason of email to get}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Set email addresses that are on the suppression list for your account.';

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

        $ses = AwsFacade::createClient('sesv2');
        $result = new Result();
        $suppressionLists = [];
        $option = $this->options();

        $params = ['Reasons' => [$option['reason']]];
        if ($option['start']) $params['StartDate'] = $option['start'];
        if ($option['end']) $params['EndDate'] = $option['end'];
        if ($option['page-size']) $params['PageSize'] = $option['page-size'];

        do {
            $params['NextToken'] = $result['NextToken'];
            $result = $ses->listSuppressedDestinations($params);
            $suppressionLists = array_merge($suppressionLists, $result['SuppressedDestinationSummaries']);
        } while (!empty($result['NextToken']));

        foreach ($suppressionLists as $suppressionList) {
            EmailSuppressionList::updateOrCreate(
                [
                    'email_address' => $suppressionList['EmailAddress']
                ],
                [
                    'reason' => $suppressionList['Reason'],
                    'updated_at' => Carbon::parse($suppressionList['LastUpdateTime']->__toString())
                ]
            );
        }
        return 0;
    }
}
