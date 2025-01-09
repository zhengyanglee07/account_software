<?php

namespace Database\Seeders;

use App\Permission;
use Illuminate\Database\Seeder;

class PermissionTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        \DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Permission::truncate();
        \DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $permissions = [
            [
                'name' => 'Add People',
                'slug' => 'add-people',
                'type' => 'integer'
            ],
            [
                'name' => 'Add Product',
                'slug' => 'add-product',
                'type' => 'integer'
            ],
            [
                'name' => 'Upload Image',
                'slug' => 'upload-image',
                'type' => 'integer'
            ],
            [
                'name' => 'Add Form',
                'slug' => 'add-form',
                'type' => 'integer'
            ],
            [
                'name' => 'Add Domain',
                'slug' => 'add-domain',
                'type' => 'integer'
            ],
            [
                'name' => 'Add Funnel',
                'slug' => 'add-funnel',
                'type' => 'integer'
            ],
            [
                'name' => 'Add Landing Page',
                'slug' => 'add-landing-page',
                'type' => 'integer'
            ],
            [
                'name' => 'Add Segment',
                'slug' => 'add-segment',
                'type' => 'integer'
            ],
            [
                'name' => 'Add Tag',
                'slug' => 'add-tag',
                'type' => 'boolean'
            ],
            [
                'name' => 'Import People',
                'slug' => 'import-people',
                'type' => 'boolean'
            ],
            [
                'name' => 'Export People',
                'slug' => 'export-people',
                'type' => 'boolean'
            ],
            [
                'name' => 'Send Email',
                'slug' => 'send-email',
                'type' => 'integer'
            ],
            [
                'name' => 'Add Automation',
                'slug' => 'add-automation',
                'type' => 'integer'
            ],
            [
                'name' => 'Add Custom Field',
                'slug' => 'add-customfield',
                'type' => 'integer'
            ],
            [
                'name' => 'Invite Role',
                'slug' => 'invite-role',
                'type' => 'integer'
            ],
            [
                'name' => 'Add Referral Campaign',
                'slug' => 'add-referral-campaign',
                'type' => 'integer'
            ],
            [
                'name' => 'Add Affiliate Campaign',
                'slug' => 'add-affiliate-campaign',
                'type' => 'integer'
            ],
            [
                'name' => 'Add Affiliate Member',
                'slug' => 'add-affiliate-member',
                'type' => 'integer'
            ],
            [
                'name' => 'Create Online Store Page',
                'slug' => 'create-os-page',
                'type' => 'integer'
            ],
            [
                'name' => 'Add Social Proof',
                'slug' => 'add-social-proof',
                'type' => 'integer'
            ],
            [
                'name' => 'Can Enable Zapier Integration',
                'slug' => 'enable-zapier',
                'type' => 'boolean'
            ],
            [
                'name' => 'Can Share Funnels',
                'slug' => 'can-share-funnel',
                'type' => 'boolean'
            ],
            [
                'name' => 'Can Disabled Badge',
                'slug' => 'can-disabled-badge',
                'type' => 'boolean'
            ],
            [
                'name' => 'Add Email',
                'slug' => 'add-email',
                'type' => 'boolean'
            ],
            [
                'name' => 'Add Promotion',
                'slug' => 'add-promotion',
                'type' => 'boolean'
            ],
            [
                'name' => 'Free Shipping Discount',
                'slug' => 'free-shipping-discount',
                'type' => 'boolean'
            ],
            [
                'name' => 'Product Recommendation',
                'slug' => 'product-recommendation',
                'type' => 'boolean'
            ],
            [
                'name' => 'Review With Rewards',
                'slug' => 'review-with-rewards',
                'type' => 'boolean'
            ],
            [
                'name' => 'Add Cashback',
                'slug' => 'add-cashback',
                'type' => 'boolean'
            ],
            [
                'name' => 'Add Store Credits',
                'slug' => 'add-store-credits',
                'type' => 'boolean'
            ],
            [
                'name' => 'Segment Report',
                'slug' => 'segment-report',
                'type' => 'boolean'
            ],
            [
                'name' => 'Email Report',
                'slug' => 'email-report',
                'type' => 'boolean'
            ],
            [
                'name' => 'Affiliate Report',
                'slug' => 'affiliate-report',
                'type' => 'boolean'
            ],
        ];

        foreach ($permissions as $singlePermission) {
            Permission::create($singlePermission);
        }
    }
}
