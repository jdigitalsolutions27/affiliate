<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-800 leading-tight">My Withdrawals</h2>
    </x-slot>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6 pb-6">
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <x-metric-card label="Total Earnings" :value="'$'.number_format($summary['total_earnings'], 2)" />
            <x-metric-card label="Available Balance" :value="'$'.number_format($summary['available_balance'], 2)" />
            <x-metric-card label="Paid" :value="'$'.number_format($summary['paid_withdrawals'], 2)" />
        </div>

        <div class="bg-white border border-slate-200 rounded-lg p-6">
            <h3 class="font-semibold text-slate-900">Request Withdrawal</h3>
            <p class="mt-1 text-sm text-slate-500">Minimum payout: ${{ number_format((float) $minimumPayout, 2) }}. Supported methods: {{ $payoutMethodsLabel }}</p>

            <form method="POST" action="{{ route('affiliate.withdrawals.store') }}" class="mt-4 grid grid-cols-1 sm:grid-cols-3 gap-4">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-slate-700">Amount</label>
                    <input type="number" step="0.01" min="0.01" name="amount" value="{{ old('amount') }}" required class="mt-1 w-full rounded-md border-slate-300">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">Payout Method</label>
                    <input name="method_text" value="{{ old('method_text') }}" required class="mt-1 w-full rounded-md border-slate-300" placeholder="GCash / Bank">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">Account Details</label>
                    <input name="account_text" value="{{ old('account_text') }}" required class="mt-1 w-full rounded-md border-slate-300" placeholder="Account number / recipient">
                </div>
                <div class="sm:col-span-3">
                    <button class="inline-flex rounded-md bg-slate-800 px-4 py-2 text-sm font-semibold text-white">Submit Withdrawal Request</button>
                </div>
            </form>
        </div>

        <div class="bg-white border border-slate-200 rounded-lg overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-slate-50 text-left text-slate-600">
                        <tr>
                            <th class="px-4 py-3">Date</th>
                            <th class="px-4 py-3">Amount</th>
                            <th class="px-4 py-3">Method</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="px-4 py-3">Reference</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($withdrawals as $withdrawal)
                            <tr class="border-t border-slate-100">
                                <td class="px-4 py-3">{{ $withdrawal->created_at->format('Y-m-d H:i') }}</td>
                                <td class="px-4 py-3">${{ number_format((float) $withdrawal->amount, 2) }}</td>
                                <td class="px-4 py-3">
                                    <p>{{ $withdrawal->method_text }}</p>
                                    <p class="text-xs text-slate-500">{{ $withdrawal->account_text }}</p>
                                </td>
                                <td class="px-4 py-3"><x-status-badge :status="$withdrawal->status" /></td>
                                <td class="px-4 py-3 text-xs">{{ $withdrawal->paid_reference ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-4 py-8 text-center text-slate-500">No withdrawals yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-4 py-3 border-t border-slate-100">
                {{ $withdrawals->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
