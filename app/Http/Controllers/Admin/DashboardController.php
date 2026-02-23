<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Click;
use App\Models\Commission;
use App\Models\Order;
use App\Models\Product;
use App\Models\Withdrawal;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $validated = $request->validate([
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
        ]);

        $from = isset($validated['date_from'])
            ? Carbon::parse($validated['date_from'])->startOfDay()
            : now()->subDays(29)->startOfDay();
        $to = isset($validated['date_to'])
            ? Carbon::parse($validated['date_to'])->endOfDay()
            : now()->endOfDay();

        $clicks = Click::query()->whereBetween('created_at', [$from, $to]);
        $ordersInRange = Order::query()->whereBetween('created_at', [$from, $to]);
        $convertedInRange = (clone $ordersInRange)->whereIn('status', [Order::STATUS_CONFIRMED, Order::STATUS_COMPLETED]);

        $totalClicks = (clone $clicks)->count();
        $totalOrders = (clone $ordersInRange)->count();
        $conversions = (clone $convertedInRange)->count();
        $totalSales = (float) (clone $convertedInRange)->sum('total_amount');

        $commissionsOwed = (float) Commission::query()
            ->whereBetween('created_at', [$from, $to])
            ->whereIn('status', [Commission::STATUS_PENDING, Commission::STATUS_APPROVED])
            ->sum('amount');

        $commissionsPaid = (float) Commission::query()
            ->whereBetween('created_at', [$from, $to])
            ->where('status', Commission::STATUS_PAID)
            ->sum('amount');

        $withdrawalsPending = Withdrawal::query()
            ->whereBetween('created_at', [$from, $to])
            ->where('status', Withdrawal::STATUS_PENDING)
            ->count();

        $topAffiliates = DB::table('affiliates')
            ->join('users', 'users.id', '=', 'affiliates.user_id')
            ->leftJoin('orders', function ($join) use ($from, $to) {
                $join->on('orders.affiliate_id', '=', 'affiliates.id')
                    ->whereBetween('orders.created_at', [$from, $to])
                    ->whereIn('orders.status', [Order::STATUS_CONFIRMED, Order::STATUS_COMPLETED]);
            })
            ->selectRaw('affiliates.id, affiliates.code, users.name, COALESCE(SUM(orders.total_amount), 0) AS total_sales, COUNT(orders.id) AS conversions')
            ->groupBy('affiliates.id', 'affiliates.code', 'users.name')
            ->orderByDesc('total_sales')
            ->limit(5)
            ->get();

        $topProducts = DB::table('products')
            ->leftJoin('orders', function ($join) use ($from, $to) {
                $join->on('orders.product_id', '=', 'products.id')
                    ->whereBetween('orders.created_at', [$from, $to])
                    ->whereIn('orders.status', [Order::STATUS_CONFIRMED, Order::STATUS_COMPLETED]);
            })
            ->selectRaw('products.id, products.name, products.slug, COALESCE(SUM(orders.total_amount), 0) AS total_sales, COUNT(orders.id) AS orders_count')
            ->groupBy('products.id', 'products.name', 'products.slug')
            ->orderByDesc('total_sales')
            ->limit(5)
            ->get();

        $dailyClicks = Click::query()
            ->selectRaw('DATE(created_at) as day, COUNT(*) as total')
            ->whereBetween('created_at', [now()->subDays(29)->startOfDay(), now()->endOfDay()])
            ->groupBy('day')
            ->orderBy('day')
            ->pluck('total', 'day');

        $dailySales = Order::query()
            ->selectRaw('DATE(created_at) as day, COALESCE(SUM(total_amount), 0) as total')
            ->whereBetween('created_at', [now()->subDays(29)->startOfDay(), now()->endOfDay()])
            ->whereIn('status', [Order::STATUS_CONFIRMED, Order::STATUS_COMPLETED])
            ->groupBy('day')
            ->orderBy('day')
            ->pluck('total', 'day');

        $latestOrders = Order::query()
            ->with(['product', 'affiliate.user'])
            ->latest()
            ->limit(10)
            ->get();

        $pendingWithdrawalsList = Withdrawal::query()
            ->with('affiliate.user')
            ->where('status', Withdrawal::STATUS_PENDING)
            ->latest()
            ->limit(10)
            ->get();

        $conversionRate = $totalClicks > 0
            ? round(($conversions / $totalClicks) * 100, 2)
            : 0;

        return view('admin.dashboard', [
            'dateFrom' => $from->toDateString(),
            'dateTo' => $to->toDateString(),
            'totalClicks' => $totalClicks,
            'totalOrders' => $totalOrders,
            'conversions' => $conversions,
            'totalSales' => $totalSales,
            'commissionsOwed' => $commissionsOwed,
            'commissionsPaid' => $commissionsPaid,
            'withdrawalsPending' => $withdrawalsPending,
            'conversionRate' => $conversionRate,
            'dailyClicks' => $dailyClicks,
            'dailySales' => $dailySales,
            'topAffiliates' => $topAffiliates,
            'topProducts' => $topProducts,
            'latestOrders' => $latestOrders,
            'pendingWithdrawalsList' => $pendingWithdrawalsList,
        ]);
    }
}
