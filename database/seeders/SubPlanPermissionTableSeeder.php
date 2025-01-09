<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Permission;
use App\SubplansPermission;
use App\SubscriptionPlan;
use Illuminate\Support\Facades\DB;

class SubPlanPermissionTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('subplans_permissions')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        //                                 Free    Square   Triangle  Circle    Square+   Triangle+ Circle+
        $data['add-people'] =             [30,     300,     1000,     3000,     6000,     12000,    24000   ];
        $data['add-product'] =            [100,    500,     1000,     3000,     6000,     9000,     12000   ];
        $data['upload-image'] =           [100000, 5000000, 10000000, 15000000, 20000000, 25000000, 30000000];
        $data['add-form'] =               [50,     0,       0,        0,        0,        0,        0       ];
        $data['add-domain'] =             [1,      3,       6,        9,        12,       15,       18      ];
        $data['add-funnel'] =             [1,      3,       10,       30,       60,       90,       120     ];
        $data['add-landing-page'] =       [1,      10,      30,       90,       180,      270,      360     ];
        $data['add-segment'] =            [0,      3,       6,        10,       30,       60,       120     ];
        $data['add-tag'] =                [1,      1,       1,        1,        1,        1,        1       ];
        $data['import-people'] =          [1,      1,       1,        1,        1,        1,        1       ];
        $data['export-people'] =          [1,      1,       1,        1,        1,        1,        1       ];
        $data['send-email'] =             [1000,   3000,    10000,    30000,    60000,    120000,   240000  ];
        $data['add-automation'] =         [0,      3,       6,        9,        12,       16,       20      ];
        $data['add-customfield'] =        [0,      3,       6,        10,       30,       00,       120     ];
        $data['invite-role'] =            [1,      4,       7,        10,       13,       16,       19      ];
        $data['add-referral-campaign'] =  [0,      0,       1,        3,        9,        12,       15      ];
        $data['add-affiliate-campaign'] = [0,      0,       1,        3,        9,        12,       15      ];
        $data['add-affiliate-member'] =   [0,      0,       30,       100,      600,      1200,     2400    ];
        $data['create-os-page'] =         [1,      10,      30,       100,      300,      600,      900     ];
        $data['add-social-proof'] =       [0,      1,       3,        6,        9,        12,       15      ];
        $data['enable-zapier'] =          [0,      1,       1,        1,        1,        1,        1       ];
        $data['can-share-funnel'] =       [0,      1,       1,        1,        1,        1,        1       ];
        $data['can-disabled-badge'] =     [0,      0,       1,        1,        1,        1,        1       ];
        $data['add-email'] =              [1,      1,       1,        1,        1,        1,        1       ];
        $data['add-promotion'] =          [0,      1,       1,        1,        1,        1,        1       ];
        $data['free-shipping-discount'] = [0,      1,       1,        1,        1,        1,        1       ];
        $data['product-recommendation'] = [0,      1,       1,        1,        1,        1,        1       ];
        $data['review-with-rewards'] =    [0,      1,       1,        1,        1,        1,        1       ];
        $data['add-cashback'] =           [0,      1,       1,        1,        1,        1,        1       ];
        $data['add-store-credits'] =      [0,      1,       1,        1,        1,        1,        1       ];
        $data['segment-report'] =         [0,      1,       1,        1,        1,        1,        1       ];
        $data['email-report'] =           [1,      1,       1,        1,        1,        1,        1       ];
        $data['affiliate-report'] =       [0,      1,       1,        1,        1,        1,        1       ];

        $subscriptionPlans = SubscriptionPlan::all();

        $permissions = Permission::all();
        foreach ($permissions as $permission) {
            foreach ($subscriptionPlans as $key => $subscriptionPlan) {
                SubplansPermission::create(
                    [
                        'plan_id' => $subscriptionPlan->id,
                        'permission_id' => $permission->id,
                        'max_value' => $data[$permission->slug][$key]
                    ]
                );
            }
        }
    }
}
