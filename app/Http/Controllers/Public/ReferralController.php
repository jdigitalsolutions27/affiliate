<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Affiliate;
use App\Models\Click;
use App\Models\Product;
use App\Services\AppSettingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;

class ReferralController extends Controller
{
    public function __invoke(Request $request, string $code, AppSettingService $settings): RedirectResponse
    {
        $productSlug = $request->query('p');
        abort_if(! $productSlug, 404);

        $affiliate = Affiliate::query()
            ->where('code', $code)
            ->where('status', Affiliate::STATUS_ACTIVE)
            ->firstOrFail();

        $product = Product::query()
            ->where('slug', $productSlug)
            ->where('status', Product::STATUS_ACTIVE)
            ->firstOrFail();

        Click::query()->create([
            'affiliate_id' => $affiliate->id,
            'product_id' => $product->id,
            'referrer' => Arr::get($request->server(), 'HTTP_REFERER'),
            'ua' => $request->userAgent(),
            'ip_hash' => hash('sha256', $request->ip().config('app.key')),
        ]);

        $payload = json_encode([
            'affiliate_id' => $affiliate->id,
            'product_id' => $product->id,
            'tracked_at' => Carbon::now()->toIso8601String(),
        ]);

        $request->session()->put('affiliate_referral', $payload);

        $days = $settings->getInt(AppSettingService::KEY_COOKIE_LIFETIME_DAYS, 30);
        $minutes = $days * 24 * 60;

        return redirect()
            ->route('products.show', $product->slug)
            ->cookie('affiliate_referral', $payload, $minutes);
    }
}
