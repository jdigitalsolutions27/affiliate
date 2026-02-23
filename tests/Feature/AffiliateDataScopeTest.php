<?php

namespace Tests\Feature;

use App\Models\Affiliate;
use App\Models\Commission;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AffiliateDataScopeTest extends TestCase
{
    use RefreshDatabase;

    public function test_affiliate_can_only_see_their_own_orders_and_commissions(): void
    {
        $product = Product::factory()->create();

        $userA = User::factory()->create(['role' => User::ROLE_AFFILIATE]);
        $affiliateA = Affiliate::factory()->create(['user_id' => $userA->id, 'status' => Affiliate::STATUS_ACTIVE]);

        $userB = User::factory()->create(['role' => User::ROLE_AFFILIATE]);
        $affiliateB = Affiliate::factory()->create(['user_id' => $userB->id, 'status' => Affiliate::STATUS_ACTIVE]);

        $orderA = Order::factory()->create([
            'affiliate_id' => $affiliateA->id,
            'product_id' => $product->id,
            'status' => Order::STATUS_CONFIRMED,
            'total_amount' => 200,
        ]);
        $itemA = OrderItem::factory()->create([
            'order_id' => $orderA->id,
            'product_id' => $product->id,
            'line_total' => 200,
        ]);
        Commission::factory()->create([
            'order_id' => $orderA->id,
            'order_item_id' => $itemA->id,
            'affiliate_id' => $affiliateA->id,
            'amount' => 20,
            'status' => Commission::STATUS_APPROVED,
        ]);

        $orderB = Order::factory()->create([
            'affiliate_id' => $affiliateB->id,
            'product_id' => $product->id,
            'status' => Order::STATUS_CONFIRMED,
            'total_amount' => 300,
        ]);
        $itemB = OrderItem::factory()->create([
            'order_id' => $orderB->id,
            'product_id' => $product->id,
            'line_total' => 300,
        ]);
        Commission::factory()->create([
            'order_id' => $orderB->id,
            'order_item_id' => $itemB->id,
            'affiliate_id' => $affiliateB->id,
            'amount' => 60,
            'status' => Commission::STATUS_APPROVED,
        ]);

        $response = $this->actingAs($userA)->get(route('affiliate.dashboard'));

        $response->assertOk();
        $response->assertViewHas('recentOrders', function ($orders) use ($affiliateA) {
            return $orders->every(fn ($order) => $order->affiliate_id === $affiliateA->id);
        });
        $response->assertViewHas('summary', function ($summary) {
            return (float) $summary['total_earnings'] === 20.0;
        });
    }
}
