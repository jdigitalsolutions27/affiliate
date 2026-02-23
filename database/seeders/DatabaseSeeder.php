<?php

namespace Database\Seeders;

use App\Models\Affiliate;
use App\Models\AppSetting;
use App\Models\Category;
use App\Models\Click;
use App\Models\Commission;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\User;
use App\Models\Withdrawal;
use App\Services\AppSettingService;
use App\Services\CommissionService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedSettings();

        User::query()->updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'System Admin',
                'password' => Hash::make('password123'),
                'role' => User::ROLE_ADMIN,
            ]
        );

        $affiliates = collect([
            ['name' => 'Alice Partner', 'email' => 'alice.affiliate@example.com', 'code' => 'AFF-ALICE'],
            ['name' => 'Brian Partner', 'email' => 'brian.affiliate@example.com', 'code' => 'AFF-BRIAN'],
            ['name' => 'Cynthia Partner', 'email' => 'cynthia.affiliate@example.com', 'code' => 'AFF-CYNTH'],
        ])->map(function ($data) {
            $user = User::query()->updateOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
                    'password' => Hash::make('password123'),
                    'role' => User::ROLE_AFFILIATE,
                ]
            );

            return Affiliate::query()->updateOrCreate(
                ['user_id' => $user->id],
                [
                    'code' => $data['code'],
                    'status' => Affiliate::STATUS_ACTIVE,
                    'default_commission_type' => 'percentage',
                    'default_commission_value' => 8,
                ]
            );
        });

        $products = collect([
            ['name' => 'Turmeric Glow Soap', 'slug' => 'turmeric-glow-soap', 'price' => 9.95, 'category' => 'Handmade Soaps', 'featured' => true, 'best_seller' => true],
            ['name' => 'Lavender Calm Body Butter', 'slug' => 'lavender-calm-body-butter', 'price' => 14.50, 'category' => 'Body Care', 'featured' => true, 'best_seller' => false],
            ['name' => 'Coconut Oatmeal Scrub Bar', 'slug' => 'coconut-oatmeal-scrub-bar', 'price' => 11.25, 'category' => 'Handmade Soaps', 'featured' => false, 'best_seller' => true],
            ['name' => 'Rosehip Facial Oil', 'slug' => 'rosehip-facial-oil', 'price' => 18.75, 'category' => 'Face Care', 'featured' => true, 'best_seller' => true],
            ['name' => 'Lemongrass Hair Mist', 'slug' => 'lemongrass-hair-mist', 'price' => 12.95, 'category' => 'Hair Care', 'featured' => false, 'best_seller' => false],
        ])->map(function ($data) {
            $category = Category::query()->firstOrCreate(
                ['slug' => Str::slug($data['category'])],
                [
                    'name' => $data['category'],
                    'description' => 'Seeded category for demo catalog.',
                    'status' => Category::STATUS_ACTIVE,
                ]
            );

            return Product::query()->updateOrCreate(
                ['slug' => $data['slug']],
                [
                    'category_id' => $category->id,
                    'name' => $data['name'],
                    'description' => 'Handmade organic product seeded for demo storefront and affiliate dashboards.',
                    'price' => $data['price'],
                    'stock' => random_int(10, 80),
                    'status' => Product::STATUS_ACTIVE,
                    'is_featured' => (bool) ($data['featured'] ?? false),
                    'is_best_seller' => (bool) ($data['best_seller'] ?? false),
                    'default_commission_type' => 'percentage',
                    'default_commission_value' => 10,
                ]
            );
        });

        foreach ($products as $index => $product) {
            ProductImage::query()->updateOrCreate([
                'product_id' => $product->id,
                'sort_order' => 0,
            ], [
                'image_path' => 'https://picsum.photos/seed/redfairy'.($index + 1).'/900/900',
                'alt_text' => $product->name,
            ]);
        }

        $commissionService = app(CommissionService::class);

        foreach ($affiliates as $affiliate) {
            foreach ($products as $product) {
                for ($i = 0; $i < 12; $i++) {
                    Click::query()->create([
                        'affiliate_id' => $affiliate->id,
                        'product_id' => $product->id,
                        'referrer' => 'https://example-source.test/campaign/'.$i,
                        'ua' => 'SeederAgent/1.0',
                        'ip_hash' => hash('sha256', Str::random(16)),
                        'created_at' => Carbon::now()->subDays(random_int(0, 29))->subHours(random_int(0, 23)),
                        'updated_at' => now(),
                    ]);
                }
            }

            for ($i = 0; $i < 8; $i++) {
                $product = $products->random();
                $qty = random_int(1, 3);
                $status = collect([
                    Order::STATUS_PENDING,
                    Order::STATUS_CONFIRMED,
                    Order::STATUS_COMPLETED,
                    Order::STATUS_CANCELLED,
                ])->random();

                $order = Order::query()->create([
                    'customer_name' => fake()->name(),
                    'customer_email' => fake()->safeEmail(),
                    'customer_phone' => fake()->phoneNumber(),
                    'customer_address' => fake()->address(),
                    'customer_notes' => fake()->sentence(),
                    'product_id' => $product->id,
                    'qty' => $qty,
                    'total_amount' => round($qty * (float) $product->price, 2),
                    'affiliate_id' => $affiliate->id,
                    'status' => $status,
                    'source' => 'public',
                    'flow_type' => collect([Order::FLOW_ORDER_REQUEST, Order::FLOW_CHECKOUT_LITE])->random(),
                    'created_at' => Carbon::now()->subDays(random_int(0, 29)),
                    'updated_at' => now(),
                ]);

                OrderItem::query()->create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'qty' => $qty,
                    'unit_price' => $product->price,
                    'line_total' => round($qty * (float) $product->price, 2),
                    'created_at' => $order->created_at,
                    'updated_at' => $order->updated_at,
                ]);

                $commissionService->syncForOrderStatusChange($order, Order::STATUS_PENDING);
            }
        }

        $primaryAffiliate = $affiliates->first();
        if ($primaryAffiliate) {
            Withdrawal::query()->create([
                'affiliate_id' => $primaryAffiliate->id,
                'amount' => 120,
                'method_text' => 'Bank Transfer',
                'account_text' => '111222333 / Primary Account',
                'status' => Withdrawal::STATUS_PENDING,
            ]);

            $paid = Withdrawal::query()->create([
                'affiliate_id' => $primaryAffiliate->id,
                'amount' => 80,
                'method_text' => 'GCash',
                'account_text' => '09991234567',
                'status' => Withdrawal::STATUS_PAID,
                'paid_reference' => 'TXN-DEMO-10001',
                'processed_at' => now()->subDays(2),
            ]);

            $remaining = (float) $paid->amount;
            $commissions = Commission::query()
                ->where('affiliate_id', $primaryAffiliate->id)
                ->where('status', Commission::STATUS_APPROVED)
                ->orderBy('created_at')
                ->get();

            foreach ($commissions as $commission) {
                $amount = (float) $commission->amount;
                if ($remaining < $amount) {
                    continue;
                }

                $commission->update(['status' => Commission::STATUS_PAID]);
                $remaining -= $amount;

                if ($remaining <= 0) {
                    break;
                }
            }
        }
    }

    private function seedSettings(): void
    {
        $defaults = app(AppSettingService::class)->defaults();
        foreach ($defaults as $key => $value) {
            AppSetting::query()->updateOrCreate([
                'key' => $key,
            ], [
                'value' => (string) $value,
            ]);
        }
    }
}
