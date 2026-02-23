<?php

namespace App\Http\Controllers\Affiliate;

use App\Http\Controllers\Controller;
use App\Models\Click;
use Illuminate\Http\Request;
use Illuminate\View\View;
use App\Services\AffiliateBalanceService;

class DashboardController extends Controller
{
    public function __construct(private readonly AffiliateBalanceService $balanceService)
    {
    }

    public function index(Request $request): View
    {
        $affiliate = $request->user()->affiliate;
        abort_if(! $affiliate, 403);

        $summary = $this->balanceService->summary($affiliate);

        $recentClicksSummary = Click::query()
            ->selectRaw('DATE(created_at) as day, COUNT(*) as total')
            ->where('affiliate_id', $affiliate->id)
            ->groupBy('day')
            ->orderByDesc('day')
            ->limit(10)
            ->get();

        $recentOrders = $affiliate->orders()
            ->with('product')
            ->latest()
            ->limit(10)
            ->get();

        $withdrawals = $affiliate->withdrawals()
            ->latest()
            ->limit(10)
            ->get();

        return view('affiliate.dashboard', [
            'summary' => $summary,
            'recentClicksSummary' => $recentClicksSummary,
            'recentOrders' => $recentOrders,
            'withdrawals' => $withdrawals,
        ]);
    }
}
