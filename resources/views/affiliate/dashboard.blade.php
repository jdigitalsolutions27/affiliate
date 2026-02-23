<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-800 leading-tight">Affiliate Dashboard</h2>
    </x-slot>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6 pb-6">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            <x-metric-card label="Total Clicks" :value="$summary['total_clicks']" />
            <x-metric-card label="Conversions" :value="$summary['conversions']" />
            <x-metric-card label="Total Sales" :value="'$'.number_format($summary['total_sales'], 2)" />
            <x-metric-card label="Total Earnings" :value="'$'.number_format($summary['total_earnings'], 2)" />
            <x-metric-card label="Available Balance" :value="'$'.number_format($summary['available_balance'], 2)" />
            <x-metric-card label="Paid Out" :value="'$'.number_format($summary['paid_withdrawals'], 2)" />
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="bg-white border border-slate-200 rounded-lg p-4">
                <h3 class="font-semibold text-slate-900 mb-3">Recent Clicks Summary</h3>
                <div class="space-y-2">
                    @forelse ($recentClicksSummary as $item)
                        <div class="flex justify-between border-b border-slate-100 pb-1 text-sm">
                            <span>{{ $item->day }}</span>
                            <span class="font-semibold">{{ $item->total }}</span>
                        </div>
                    @empty
                        <p class="text-sm text-slate-500">No click data yet.</p>
                    @endforelse
                </div>
            </div>

            <div class="bg-white border border-slate-200 rounded-lg p-4 lg:col-span-2">
                <h3 class="font-semibold text-slate-900 mb-3">Recent Attributed Orders</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="text-left text-slate-500 border-b">
                            <tr>
                                <th class="py-2">Order</th>
                                <th class="py-2">Product</th>
                                <th class="py-2">Total</th>
                                <th class="py-2">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($recentOrders as $order)
                                <tr class="border-b border-slate-100">
                                    <td class="py-2">#{{ $order->id }}</td>
                                    <td class="py-2">{{ $order->product?->name ?? 'N/A' }}</td>
                                    <td class="py-2">${{ number_format((float) $order->total_amount, 2) }}</td>
                                    <td class="py-2"><x-status-badge :status="$order->status" /></td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="py-4 text-center text-slate-500">No orders yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="bg-white border border-slate-200 rounded-lg p-4">
            <h3 class="font-semibold text-slate-900 mb-3">Recent Withdrawals</h3>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="text-left text-slate-500 border-b">
                        <tr>
                            <th class="py-2">Date</th>
                            <th class="py-2">Amount</th>
                            <th class="py-2">Method</th>
                            <th class="py-2">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($withdrawals as $withdrawal)
                            <tr class="border-b border-slate-100">
                                <td class="py-2">{{ $withdrawal->created_at->format('Y-m-d') }}</td>
                                <td class="py-2">${{ number_format((float) $withdrawal->amount, 2) }}</td>
                                <td class="py-2">{{ $withdrawal->method_text }}</td>
                                <td class="py-2"><x-status-badge :status="$withdrawal->status" /></td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="py-4 text-center text-slate-500">No withdrawals yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
