<?php

namespace Tests\Feature;

use App\Models\Affiliate;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReferralTrackingTest extends TestCase
{
    use RefreshDatabase;

    public function test_referral_tracking_sets_cookie_and_logs_click(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_AFFILIATE]);
        $affiliate = Affiliate::factory()->create([
            'user_id' => $user->id,
            'code' => 'AFF-TRACK',
            'status' => Affiliate::STATUS_ACTIVE,
        ]);

        $product = Product::factory()->create([
            'slug' => 'demo-product',
            'status' => Product::STATUS_ACTIVE,
        ]);

        $response = $this->get(route('referral.track', ['code' => $affiliate->code, 'p' => $product->slug]));

        $response->assertRedirect(route('products.show', $product->slug));
        $response->assertCookie('affiliate_referral');

        $this->assertDatabaseHas('clicks', [
            'affiliate_id' => $affiliate->id,
            'product_id' => $product->id,
        ]);
    }
}
