<?php

namespace Database\Seeders;

use App\User;
use App\Email;
use App\EmailDesign;
use Illuminate\Database\Seeder;


class EmailsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        return; // temp disabled from 11/07/23
        $users = User::where('email', 'steve@gmail.com')->first();
        $accountId = $users->currentAccountId;
        $emails = EmailDesign::where('account_id', $accountId)->get();


        $data = [
            [
                'account_id' => $accountId,
                'type' => 'Marketing',
                'name' => 'Day 0: Welcome to Hypershapes!',
                'subject' => 'Welcome to Hypershapes!',
                'email_design_id' => $emails->firstWhere('name', 'OnboardingDay0')->id,
            ],
            [
                'account_id' => $accountId,
                'type' => 'Marketing',
                'name' => 'Day 1: Always deliver on time... (mini store)',
                'subject' => 'Always deliver on time...',
                'email_design_id' => $emails->firstWhere('name', 'OnboardingDay1')->id,
            ],
            [
                'account_id' => $accountId,
                'type' => 'Marketing',
                'name' => 'Day 2: What are your preferences? (mini store)',
                'subject' => 'What are your preferences?',
                'email_design_id' => $emails->firstWhere('name', 'OnboardingDay2')->id,
            ],
            [
                'account_id' => $accountId,
                'type' => 'Marketing',
                'name' => 'Day 3: Let your customers pay you consistently. (mini store)',
                'subject' => 'Let your customers pay you consistently',
                'email_design_id' => $emails->firstWhere('name', 'OnboardingDay3')->id,
            ],
            [
                'account_id' => $accountId,
                'type' => 'Marketing',
                'name' => 'Day 4: Should I allow my customers to leave reviews? (mini store)',
                'subject' => 'Should I allow my customers to leave reviews?',
                'email_design_id' => $emails->firstWhere('name', 'OnboardingDay4')->id,
            ],
            [
                'account_id' => $accountId,
                'type' => 'Marketing',
                'name' => 'Day 5: Sell Anything to Anyone (mini store)',
                'subject' => 'Sell Anything to Anyone',
                'email_design_id' => $emails->firstWhere('name', 'OnboardingDay5')->id,
            ],
            [
                'account_id' => $accountId,
                'type' => 'Marketing',
                'name' => 'Day 6: How to convert your visitors into paying customers? (mini store)',
                'subject' => 'How to convert your visitors into paying customers?',
                'email_design_id' => $emails->firstWhere('name', 'OnboardingDay6')->id,
            ],
            [
                'account_id' => $accountId,
                'type' => 'Marketing',
                'name' => 'Day 7: A small but powerful trick to keep your customers coming back (mini store)',
                'subject' => 'A small but powerful trick to keep your customers coming back',
                'email_design_id' => $emails->firstWhere('name', 'OnboardingDay7')->id,
            ],
            [
                'account_id' => $accountId,
                'type' => 'Marketing',
                'name' => 'Day 8: A long queue in front of your store... (mini store)',
                'subject' => 'A long queue in front of your store...',
                'email_design_id' => $emails->firstWhere('name', 'OnboardingDay8')->id,
            ],
            [
                'account_id' => $accountId,
                'type' => 'Marketing',
                'name' => 'Day 9: Does email marketing still work? (mini store)',
                'subject' => 'Does email marketing still work?',
                'email_design_id' => $emails->firstWhere('name', 'OnboardingDay9')->id,
            ],
            [
                'account_id' => $accountId,
                'type' => 'Marketing',
                'name' => 'Day 10: We all agreed.. (mini store)',
                'subject' => 'We all agreed..',
                'email_design_id' => $emails->firstWhere('name', 'OnboardingDay10')->id,
            ],
            [
                'account_id' => $accountId,
                'type' => 'Marketing',
                'name' => 'Day 11: Skyrocket your sales with ZERO budget (mini store)',
                'subject' => 'Skyrocket your sales with ZERO budget',
                'email_design_id' => $emails->firstWhere('name', 'OnboardingDay11')->id,
            ],
            [
                'account_id' => $accountId,
                'type' => 'Marketing',
                'name' => 'Day 12: A special gift for you (promo email)',
                'subject' => 'A special gift for you',
                'email_design_id' => $emails->firstWhere('name', 'OnboardingDay12')->id,
            ],
            [
                'account_id' => $accountId,
                'type' => 'Marketing',
                'name' => 'Day 13: Just 39 cents? (promo email)',
                'subject' => 'Just 39 cents?',
                'email_design_id' => $emails->firstWhere('name', 'OnboardingDay13')->id,
            ],
            [
                'account_id' => $accountId,
                'type' => 'Marketing',
                'name' => 'Day 14: Last 60 minutes... (promo email)',
                'subject' => 'Last 60 minutes...',
                'email_design_id' => $emails->firstWhere('name', 'OnboardingDay14')->id,
            ],
            [
                'account_id' => $accountId,
                'type' => 'Marketing',
                'name' => 'Day 15: The purple badge at the corner.. (mini store)',
                'subject' => 'The purple badge at the corner',
                'email_design_id' => $emails->firstWhere('name', 'OnboardingDay15')->id,
            ],

        ];

        foreach ($data as $entry) {
            Email::updateOrCreate(['account_id' => $accountId, 'name' => $entry['name']], $entry);
        }
    }
}
