<?php

namespace App\Services;

use App\Models\Affiliate;
use App\Models\Commission;
use App\Models\Order;
use App\Models\Withdrawal;

class AffiliateBalanceService
{
    public function summary(Affiliate $affiliate): array
    {
        $totalClicks = $affiliate->clicks()->count();
        $conversions = $affiliate->orders()
            ->whereIn('status', [Order::STATUS_CONFIRMED, Order::STATUS_COMPLETED])
            ->count();
        $totalSales = (float) $affiliate->orders()
            ->whereIn('status', [Order::STATUS_CONFIRMED, Order::STATUS_COMPLETED])
            ->sum('total_amount');

        $totalEarnings = (float) $affiliate->commissions()
            ->whereIn('status', [Commission::STATUS_APPROVED, Commission::STATUS_PAID])
            ->sum('amount');

        $paidCommissions = (float) $affiliate->commissions()
            ->where('status', Commission::STATUS_PAID)
            ->sum('amount');

        $pendingWithdrawals = (float) $affiliate->withdrawals()
            ->whereIn('status', [Withdrawal::STATUS_PENDING, Withdrawal::STATUS_APPROVED])
            ->sum('amount');

        $totalPaidWithdrawals = (float) $affiliate->withdrawals()
            ->where('status', Withdrawal::STATUS_PAID)
            ->sum('amount');

        $availableBalance = max($totalEarnings - $totalPaidWithdrawals - $pendingWithdrawals, 0);

        return [
            'total_clicks' => $totalClicks,
            'conversions' => $conversions,
            'total_sales' => round($totalSales, 2),
            'total_earnings' => round($totalEarnings, 2),
            'available_balance' => round($availableBalance, 2),
            'paid_commissions' => round($paidCommissions, 2),
            'paid_withdrawals' => round($totalPaidWithdrawals, 2),
            'pending_withdrawals' => round($pendingWithdrawals, 2),
        ];
    }
}

