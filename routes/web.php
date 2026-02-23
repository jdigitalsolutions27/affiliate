<?php

use App\Http\Controllers\Admin\AffiliateController as AdminAffiliateController;
use App\Http\Controllers\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Admin\SettingController as AdminSettingController;
use App\Http\Controllers\Admin\WithdrawalController as AdminWithdrawalController;
use App\Http\Controllers\Affiliate\DashboardController as AffiliateDashboardController;
use App\Http\Controllers\Affiliate\LinkController as AffiliateLinkController;
use App\Http\Controllers\Affiliate\WithdrawalController as AffiliateWithdrawalController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Public\HomeController;
use App\Http\Controllers\Public\OfferController;
use App\Http\Controllers\Public\ProductCatalogController;
use App\Http\Controllers\Public\PublicOrderController;
use App\Http\Controllers\Public\ReferralController;
use App\Models\User;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/products', [ProductCatalogController::class, 'index'])->name('products.index');
Route::get('/product/{slug}', [ProductCatalogController::class, 'show'])->name('products.show');
Route::get('/category/{slug}', [ProductCatalogController::class, 'byCategory'])->name('categories.show');
Route::get('/offer/{slug}', [OfferController::class, 'show'])->name('offers.show');
Route::get('/r/{code}', ReferralController::class)
    ->middleware('throttle:referral')
    ->name('referral.track');

Route::get('/order/{product_slug}', [PublicOrderController::class, 'create'])->name('public.order.create');
Route::post('/order/{product_slug}', [PublicOrderController::class, 'store'])->name('public.order.store');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', function () {
        /** @var User $user */
        $user = auth()->user();

        if ($user->isAdmin()) {
            return redirect()->route('admin.dashboard');
        }

        return redirect()->route('affiliate.dashboard');
    })->name('dashboard');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::prefix('admin')
    ->name('admin.')
    ->middleware(['auth', 'role:admin'])
    ->group(function () {
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

        Route::resource('affiliates', AdminAffiliateController::class)->except('show');
        Route::patch('/affiliates/{affiliate}/status', [AdminAffiliateController::class, 'toggleStatus'])->name('affiliates.status');
        Route::post('/affiliates/{affiliate}/reset-password', [AdminAffiliateController::class, 'resetPassword'])->name('affiliates.reset-password');
        Route::post('/affiliates/{affiliate}/revoke-sessions', [AdminAffiliateController::class, 'revokeTokens'])->name('affiliates.revoke-sessions');

        Route::resource('categories', AdminCategoryController::class)->except('show');
        Route::resource('products', AdminProductController::class)->except('show');
        Route::post('/products/import-csv', [AdminProductController::class, 'importCsv'])->name('products.import-csv');

        Route::get('/orders', [AdminOrderController::class, 'index'])->name('orders.index');
        Route::get('/orders/create', [AdminOrderController::class, 'create'])->name('orders.create');
        Route::post('/orders', [AdminOrderController::class, 'store'])->name('orders.store');
        Route::get('/orders/{order}/edit', [AdminOrderController::class, 'edit'])->name('orders.edit');
        Route::put('/orders/{order}', [AdminOrderController::class, 'update'])->name('orders.update');

        Route::get('/withdrawals', [AdminWithdrawalController::class, 'index'])->name('withdrawals.index');
        Route::patch('/withdrawals/{withdrawal}/approve', [AdminWithdrawalController::class, 'approve'])->name('withdrawals.approve');
        Route::patch('/withdrawals/{withdrawal}/reject', [AdminWithdrawalController::class, 'reject'])->name('withdrawals.reject');
        Route::patch('/withdrawals/{withdrawal}/paid', [AdminWithdrawalController::class, 'markPaid'])->name('withdrawals.paid');

        Route::get('/settings', [AdminSettingController::class, 'edit'])->name('settings.edit');
        Route::put('/settings', [AdminSettingController::class, 'update'])->name('settings.update');
        Route::post('/settings/meta/test', [AdminSettingController::class, 'testMetaConnection'])->name('settings.meta.test');
        Route::post('/settings/meta/sync', [AdminSettingController::class, 'syncMetaProducts'])->name('settings.meta.sync');
    });

Route::prefix('affiliate')
    ->name('affiliate.')
    ->middleware(['auth', 'role:affiliate', 'affiliate.active'])
    ->group(function () {
        Route::get('/dashboard', [AffiliateDashboardController::class, 'index'])->name('dashboard');
        Route::get('/links', [AffiliateLinkController::class, 'index'])->name('links.index');
        Route::get('/withdrawals', [AffiliateWithdrawalController::class, 'index'])->name('withdrawals.index');
        Route::post('/withdrawals', [AffiliateWithdrawalController::class, 'store'])->name('withdrawals.store');
    });

require __DIR__.'/auth.php';
