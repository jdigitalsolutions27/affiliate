<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-800 leading-tight">Admin Dashboard</h2>
    </x-slot>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
        <div class="bg-white border border-slate-200 rounded-lg p-4">
            <form method="GET" class="grid grid-cols-1 sm:grid-cols-3 gap-3 items-end">
                <div>
                    <label class="block text-xs font-semibold text-slate-600">Date From</label>
                    <input type="date" name="date_from" value="{{ $dateFrom }}" class="mt-1 w-full rounded-md border-slate-300">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600">Date To</label>
                    <input type="date" name="date_to" value="{{ $dateTo }}" class="mt-1 w-full rounded-md border-slate-300">
                </div>
                <button class="inline-flex h-10 justify-center items-center rounded-md bg-slate-800 px-4 text-sm font-semibold text-white">Apply Filter</button>
            </form>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <x-metric-card label="Total Sales" :value="'$'.number_format($totalSales, 2)" />
            <x-metric-card label="Orders" :value="$totalOrders" />
            <x-metric-card label="Clicks" :value="$totalClicks" />
            <x-metric-card label="Conversion Rate" :value="$conversionRate.'%'" />
            <x-metric-card label="Commissions Owed" :value="'$'.number_format($commissionsOwed, 2)" />
            <x-metric-card label="Commissions Paid" :value="'$'.number_format($commissionsPaid, 2)" />
            <x-metric-card label="Pending Withdrawals" :value="$withdrawalsPending" />
            <x-metric-card label="Conversions" :value="$conversions" />
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="bg-white border border-slate-200 rounded-lg p-4">
                <h3 class="font-semibold text-slate-900 mb-3">Daily Clicks (Last 30 Days)</h3>
                <div class="max-h-64 overflow-auto space-y-2">
                    @forelse ($dailyClicks as $day => $total)
                        <div class="flex justify-between text-sm border-b border-slate-100 pb-1">
                            <span>{{ $day }}</span>
                            <span class="font-semibold">{{ $total }}</span>
                        </div>
                    @empty
                        <p class="text-sm text-slate-500">No click data.</p>
                    @endforelse
                </div>
            </div>

            <div class="bg-white border border-slate-200 rounded-lg p-4">
                <h3 class="font-semibold text-slate-900 mb-3">Daily Sales (Last 30 Days)</h3>
                <div class="max-h-64 overflow-auto space-y-2">
                    @forelse ($dailySales as $day => $total)
                        <div class="flex justify-between text-sm border-b border-slate-100 pb-1">
                            <span>{{ $day }}</span>
                            <span class="font-semibold">${{ number_format((float) $total, 2) }}</span>
                        </div>
                    @empty
                        <p class="text-sm text-slate-500">No sales data.</p>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="bg-white border border-slate-200 rounded-lg p-4">
                <h3 class="font-semibold text-slate-900 mb-3">Top Affiliates</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="text-left text-slate-500 border-b">
                                <th class="py-2">Affiliate</th>
                                <th class="py-2">Code</th>
                                <th class="py-2">Sales</th>
                                <th class="py-2">Conversions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($topAffiliates as $item)
                                <tr class="border-b border-slate-100">
                                    <td class="py-2">{{ $item->name }}</td>
                                    <td class="py-2">{{ $item->code }}</td>
                                    <td class="py-2">${{ number_format((float) $item->total_sales, 2) }}</td>
                                    <td class="py-2">{{ $item->conversions }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="py-4 text-center text-slate-500">No affiliate performance data.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="bg-white border border-slate-200 rounded-lg p-4">
                <h3 class="font-semibold text-slate-900 mb-3">Top Products</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="text-left text-slate-500 border-b">
                                <th class="py-2">Product</th>
                                <th class="py-2">Sales</th>
                                <th class="py-2">Orders</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($topProducts as $item)
                                <tr class="border-b border-slate-100">
                                    <td class="py-2">{{ $item->name }}</td>
                                    <td class="py-2">${{ number_format((float) $item->total_sales, 2) }}</td>
                                    <td class="py-2">{{ $item->orders_count }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="py-4 text-center text-slate-500">No product sales data.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 pb-6">
            <div class="bg-white border border-slate-200 rounded-lg p-4">
                <h3 class="font-semibold text-slate-900 mb-3">Latest Orders</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="text-left text-slate-500 border-b">
                                <th class="py-2">Order</th>
                                <th class="py-2">Product</th>
                                <th class="py-2">Affiliate</th>
                                <th class="py-2">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($latestOrders as $order)
                                <tr class="border-b border-slate-100">
                                    <td class="py-2">#{{ $order->id }}</td>
                                    <td class="py-2">{{ $order->product?->name ?? 'N/A' }}</td>
                                    <td class="py-2">{{ $order->affiliate?->user?->name ?? '-' }}</td>
                                    <td class="py-2"><x-status-badge :status="$order->status" /></td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="py-4 text-center text-slate-500">No orders found.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="bg-white border border-slate-200 rounded-lg p-4">
                <h3 class="font-semibold text-slate-900 mb-3">Pending Withdrawals</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="text-left text-slate-500 border-b">
                                <th class="py-2">Affiliate</th>
                                <th class="py-2">Amount</th>
                                <th class="py-2">Method</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($pendingWithdrawalsList as $withdrawal)
                                <tr class="border-b border-slate-100">
                                    <td class="py-2">{{ $withdrawal->affiliate?->user?->name }}</td>
                                    <td class="py-2">${{ number_format((float) $withdrawal->amount, 2) }}</td>
                                    <td class="py-2">{{ $withdrawal->method_text }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="py-4 text-center text-slate-500">No pending withdrawals.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
