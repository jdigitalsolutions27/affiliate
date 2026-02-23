<?php

namespace Tests\Feature;

use App\Models\Affiliate;
use App\Models\AppSetting;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommissionCreationTest extends TestCase
{
    use RefreshDatabase;

    public function test_commission_creation_on_order_status_change(): void
    {
        AppSetting::query()->create([
            'key' => 'commission_trigger_status',
            'value' => Order::STATUS_CONFIRMED,
        ]);

        $admin = User::factory()->admin()->create();

        $affiliateUser = User::factory()->create(['role' => User::ROLE_AFFILIATE]);
        $affiliate = Affiliate::factory()->create([
            'user_id' => $affiliateUser->id,
            'status' => Affiliate::STATUS_ACTIVE,
        ]);

        $product = Product::factory()->create([
            'default_commission_type' => 'percentage',
            'default_commission_value' => 20,
        ]);

        $order = Order::factory()->create([
            'affiliate_id' => $affiliate->id,
            'product_id' => $product->id,
            'qty' => 2,
            'total_amount' => 200,
            'status' => Order::STATUS_PENDING,
        ]);

        OrderItem::factory()->create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'qty' => 2,
            'unit_price' => 100,
            'line_total' => 200,
        ]);

        $payload = [
            'customer_name' => $order->customer_name,
            'customer_email' => $order->customer_email,
            'customer_phone' => $order->customer_phone,
            'customer_address' => $order->customer_address,
            'product_id' => $product->id,
            'affiliate_id' => $affiliate->id,
            'qty' => 2,
            'unit_price' => 100,
            'flow_type' => Order::FLOW_CHECKOUT_LITE,
            'status' => Order::STATUS_CONFIRMED,
        ];

        $response = $this->actingAs($admin)->put(route('admin.orders.update', $order), $payload);

        $response->assertRedirect();

        $this->assertDatabaseHas('commissions', [
            'order_id' => $order->id,
            'affiliate_id' => $affiliate->id,
            'status' => 'approved',
            'amount' => '40.00',
        ]);
    }
}
