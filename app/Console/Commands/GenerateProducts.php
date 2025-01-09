<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\User;
use App\Account;

class GenerateProducts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'generate:products {--email=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate products for testing purposes.';

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

        $email = $this->option('email');
        $account = $this->getAccountByEmail($email);
        if ($account == null) {
            return $this->error('User does not exist in database!');
        }
        $this->info('Generating...');
        $this->info('');
        $this->runSeeder($account);
        $this->info('');
        $this->info('Done');

        return 0;
    }

    protected function getAccountByEmail($email)
    {
        $account = User::where('email', $email)->first();
        if ($account === null) {
            return null;
        }
        return $account->accounts()->first();
    }

    protected function runSeeder($account)
    {
        $productsWithoutVariant = [
            [
                'account_id' => $account->id,
                'title' => 'Normal Product (normal price)',
                'path' => 'testing-product-0001',
                'image_url' => 'https://cdn.hypershapes.com/assets/product-default-image.png',
                'status' => 'active',
                'price' => 500.00,
                'compare_price' => 600.00,
                'type' => 'physical',
                'reference_key' => '999999999991',
                'quantity' => 100,
                'is_selling' => 1,
                'weight' => 0.00,
            ],
            [
                'account_id' => $account->id,
                'title' => 'Normal Product (no original price)',
                'path' => 'testing-product-0002',
                'image_url' => 'https://cdn.hypershapes.com/assets/product-default-image.png',
                'status' => 'active',
                'price' => 500.00,
                'compare_price' => null,
                'type' => 'physical',
                'reference_key' => '999999999992',
                'quantity' => 100,
                'is_selling' => 1,
                'weight' => 0.00,
            ],
            [
                'account_id' => $account->id,
                'title' => 'Normal Product (zero price)',
                'path' => 'testing-product-0003',
                'image_url' => 'https://cdn.hypershapes.com/assets/product-default-image.png',
                'status' => 'active',
                'price' => 0.00,
                'compare_price' => null,
                'type' => 'physical',
                'reference_key' => '999999999993',
                'quantity' => 100,
                'is_selling' => 1,
                'weight' => 0.00,
            ],
        ];
        $productsWithVariant = [
            [
                'account_id' => $account->id,
                'title' => 'Variant Product (same price)',
                'path' => 'testing-product-0004',
                'image_url' => 'https://cdn.hypershapes.com/assets/product-default-image.png',
                'status' => 'active',
                'price' => 500.00,
                'compare_price' => 600.00,
                'type' => 'physical',
                'reference_key' => '999999999994',
                'quantity' => 100,
                'is_selling' => 1,
                'weight' => 0.00,
            ],
            [
                'account_id' => $account->id,
                'title' => 'Variant Product (no original price)',
                'path' => 'testing-product-0005',
                'image_url' => 'https://cdn.hypershapes.com/assets/product-default-image.png',
                'status' => 'active',
                'price' => 500.00,
                'compare_price' => null,
                'type' => 'physical',
                'reference_key' => '999999999995',
                'quantity' => 100,
                'is_selling' => 1,
                'weight' => 0.00,
            ],
            [
                'account_id' => $account->id,
                'title' => 'Variant Product (zero price)',
                'path' => 'testing-product-0006',
                'image_url' => 'https://cdn.hypershapes.com/assets/product-default-image.png',
                'status' => 'active',
                'price' => 0.00,
                'compare_price' => null,
                'type' => 'physical',
                'reference_key' => '999999999996',
                'quantity' => 100,
                'is_selling' => 1,
                'weight' => 0.00,
            ],
            [
                'account_id' => $account->id,
                'title' => 'Variant Product (different price)',
                'path' => 'testing-product-0007',
                'image_url' => 'https://cdn.hypershapes.com/assets/product-default-image.png',
                'status' => 'active',
                'price' => 500.00,
                'compare_price' => 600.00,
                'type' => 'physical',
                'reference_key' => '999999999997',
                'quantity' => 100,
                'is_selling' => 1,
                'weight' => 0.00,
            ],
        ];
        DB::table('products')->insert($productsWithoutVariant);
        $this->info('Product without variant generated.');

        foreach ($productsWithVariant as $key => $product) {
            $productId = DB::table('products')->insertGetId($product);
            // insert variant
            $this->insertVariant($productId, $account->id, $key);
        }
        $this->info('Product with variant generated.');
    }

    protected function insertVariant($productId, $accountId, $index)
    {
        $variants = [
            [
                'type' => 'button',
                'is_shared' => 0,
                'account_id' => $accountId,
                'variant_name' => 'Variant A',
                'display_name' => 'Variant A',
            ],
            [
                'type' => 'dropdown',
                'is_shared' => 0,
                'account_id' => $accountId,
                'variant_name' => 'Variant B',
                'display_name' => 'Variant B',
            ]
        ];

        $variantValueIds = array();

        foreach ($variants as $key => $variant) {
            $variantId = DB::table('variants')->insertGetId($variant);

            // insert variant value
            $valueIdsBuffer = $this->insertVariantValues($variantId, $key);
            array_push($variantValueIds, $valueIdsBuffer);

            // insert pivot
            DB::table('product_variants')->insert([
                [
                    'product_id' => $productId,
                    'variant_id' => $variantId,
                ]
            ]);
        }

        $this->insertCombinations($productId, $variantValueIds[0], $variantValueIds[1], $index);
    }

    protected function insertVariantValues($variantId, $index)
    {
        $variantValuesA = [
            [
                'variant_id' => $variantId,
                'variant_value' => 'Value A'
            ],
            [
                'variant_id' => $variantId,
                'variant_value' => 'Value AA'
            ],
            [
                'variant_id' => $variantId,
                'variant_value' => 'Value AAA'
            ],
        ];
        $variantValuesB = [
            [
                'variant_id' => $variantId,
                'variant_value' => 'Value B'
            ],
            [
                'variant_id' => $variantId,
                'variant_value' => 'Value BB'
            ],
            [
                'variant_id' => $variantId,
                'variant_value' => 'Value BBB'
            ],
        ];

        $selectedVariant = $index === 0 ? $variantValuesA : $variantValuesB;
        $valueIds = array();

        foreach ($selectedVariant as $selected) {
            $valueId = DB::table('variant_values')->insertGetId($selected);
            array_push($valueIds, $valueId);
        }

        return $valueIds;
    }

    protected function insertCombinations($productId, $combinationArrayA, $combinationArrayB, $case)
    {
        $referenceKey = 888888888880;
        $price = 500.00;
        $comparePrice = 600.00;

        switch ($case) {
            case 1:
                $price = 500.00;
                $comparePrice = null;
                $referenceKey = 777777777770;
                break;
            case 2:
                $price = 0.00;
                $comparePrice = null;
                $referenceKey = 666666666660;
                break;
            case 3:
                $referenceKey = 555555555550;
                break;
            default:
                break;
        }

        foreach ($combinationArrayA as $aValue) {
            foreach ($combinationArrayB as $bValue) {
                if ($case === 3) {
                    $price += 10;
                    $comparePrice += 10;
                }
                DB::table('variant_details')->insert([
                    [
                        'reference_key' => $referenceKey++,
                        'product_id' => $productId,
                        'option_1_id' => $aValue,
                        'option_2_id' => $bValue,
                        'sku' => "same-price-variation",
                        'price' => $price,
                        'comparePrice' => $comparePrice,
                        'image_url' => "https://cdn.hypershapes.com/assets/product-default-image.png",
                        'is_visible' => 1,
                        'weight' => 1.00,
                        'quantity' => 100,
                        'is_selling' => 1
                    ]
                ]);
            }
        }
    }
}
