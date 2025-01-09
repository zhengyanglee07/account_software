<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call(ShopTypesTableSeeder::class);
        $this->call(SaleChannelsTableSeeder::class);
        $this->call(MiniStoreThemeTableSeeder::class);
        $this->call(UsersTableSeeder::class);
        // $this->call(ShopProfilesTableSeeder::class);
        $this->call(TemplateStatusSeeder::class);
        $this->call(EmailDesignTypesTableSeeder::class);
        $this->call(TemplateStatusSeeder::class);
        $this->call(EmailDesignsTableSeeder::class);
        $this->call(EmailStatusTableSeeder::class);
        $this->call(SubscriptionPlanTableSeeder::class);
        $this->call(SubscriptionPlanPriceTableSeeder::class);
        $this->call(MasterAdminTableSeeder::class);
        $this->call(TriggerTableSeeder::class);
        $this->call(AutomationStatusTableSeeder::class);
        $this->call(AutomationProvidersTableSeeder::class);
        $this->call(AutomationStepTypesTableSeeder::class);
        $this->call(PermissionTableSeeder::class);
        $this->call(SubPlanPermissionTableSeeder::class);
        $this->call(SocialMediaProvidersTableSeeder::class);
        $this->call(EmailsTableSeeder::class);
        $this->call(LegalPolicyTypeTableSeeder::class);
        $this->call(AppsTableSeeder::class);
        $this->call(AccountPermissionSeeder::class);
        $this->call(ProductRecommendationsTableSeeder::class);
        $this->call(ReferralCampaignActionTypeTableSeeder::class);
        $this->call(ReferralCampaignRewardTypeTableSeeder::class);
        $this->call(EcommercePagesTableSeeder::class);
    }
}
