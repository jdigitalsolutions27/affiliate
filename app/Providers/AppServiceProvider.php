<?php

namespace App\Providers;

use App\Models\Affiliate;
use App\Models\Commission;
use App\Models\Order;
use App\Models\Product;
use App\Models\Withdrawal;
use App\Policies\AffiliatePolicy;
use App\Policies\CommissionPolicy;
use App\Policies\OrderPolicy;
use App\Policies\ProductPolicy;
use App\Policies\WithdrawalPolicy;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (env('VERCEL') || app()->environment('production')) {
            URL::forceScheme('https');
        }

        Gate::policy(Affiliate::class, AffiliatePolicy::class);
        Gate::policy(Product::class, ProductPolicy::class);
        Gate::policy(Order::class, OrderPolicy::class);
        Gate::policy(Commission::class, CommissionPolicy::class);
        Gate::policy(Withdrawal::class, WithdrawalPolicy::class);

        Gate::define('admin-access', fn ($user) => $user->isAdmin());

        RateLimiter::for('referral', function (Request $request) {
            return Limit::perMinute(120)->by($request->ip());
        });
    }
}
