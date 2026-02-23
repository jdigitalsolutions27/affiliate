<?php

namespace Tests\Feature;

use App\Models\Affiliate;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AffiliateAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_affiliate_cannot_access_admin_routes(): void
    {
        $affiliateUser = User::factory()->create([
            'role' => User::ROLE_AFFILIATE,
        ]);

        Affiliate::factory()->create([
            'user_id' => $affiliateUser->id,
            'status' => Affiliate::STATUS_ACTIVE,
        ]);

        $response = $this->actingAs($affiliateUser)->get(route('admin.dashboard'));

        $response->assertForbidden();
    }
}
