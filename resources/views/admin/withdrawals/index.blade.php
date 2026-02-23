<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-800 leading-tight">Withdrawals</h2>
    </x-slot>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-4 pb-6">
        <div class="bg-white border border-slate-200 rounded-lg p-4">
            <form method="GET" class="flex flex-wrap items-end gap-3">
                <div>
                    <label class="block text-xs font-semibold text-slate-600">Status</label>
                    <select name="status" class="mt-1 rounded-md border-slate-300 text-sm">
                        <option value="">All</option>
                        @foreach (['pending','approved','rejected','paid'] as $status)
                            <option value="{{ $status }}" @selected($currentStatus === $status)>{{ ucfirst($status) }}</option>
                        @endforeach
                    </select>
                </div>
                <button class="inline-flex h-10 items-center rounded-md bg-slate-800 px-4 text-sm font-semibold text-white">Apply</button>
            </form>
        </div>

        <div class="bg-white border border-slate-200 rounded-lg overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-slate-50 text-left text-slate-600">
                        <tr>
                            <th class="px-4 py-3">ID</th>
                            <th class="px-4 py-3">Affiliate</th>
                            <th class="px-4 py-3">Amount</th>
                            <th class="px-4 py-3">Method</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="px-4 py-3">Requested</th>
                            <th class="px-4 py-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($withdrawals as $withdrawal)
                            <tr class="border-t border-slate-100 align-top">
                                <td class="px-4 py-3">#{{ $withdrawal->id }}</td>
                                <td class="px-4 py-3">
                                    <p class="font-semibold">{{ $withdrawal->affiliate?->user?->name }}</p>
                                    <p class="text-xs text-slate-500">{{ $withdrawal->affiliate?->code }}</p>
                                </td>
                                <td class="px-4 py-3">${{ number_format((float) $withdrawal->amount, 2) }}</td>
                                <td class="px-4 py-3">
                                    <p>{{ $withdrawal->method_text }}</p>
                                    <p class="text-xs text-slate-500">{{ $withdrawal->account_text }}</p>
                                </td>
                                <td class="px-4 py-3"><x-status-badge :status="$withdrawal->status" /></td>
                                <td class="px-4 py-3 text-xs text-slate-500">{{ $withdrawal->created_at->format('Y-m-d H:i') }}</td>
                                <td class="px-4 py-3 space-y-2">
                                    @if ($withdrawal->status === 'pending')
                                        <form method="POST" action="{{ route('admin.withdrawals.approve', $withdrawal) }}" class="space-y-1">
                                            @csrf
                                            @method('PATCH')
                                            <input name="admin_note" placeholder="Note (optional)" class="w-full rounded border-slate-300 text-xs">
                                            <button class="inline-flex rounded border border-indigo-300 px-3 py-1 text-xs font-semibold text-indigo-700">Approve</button>
                                        </form>
                                    @endif

                                    @if (in_array($withdrawal->status, ['pending', 'approved']))
                                        <form method="POST" action="{{ route('admin.withdrawals.reject', $withdrawal) }}" class="space-y-1">
                                            @csrf
                                            @method('PATCH')
                                            <input name="admin_note" placeholder="Rejection reason" required class="w-full rounded border-slate-300 text-xs">
                                            <button class="inline-flex rounded border border-rose-300 px-3 py-1 text-xs font-semibold text-rose-700">Reject</button>
                                        </form>

                                        <form method="POST" action="{{ route('admin.withdrawals.paid', $withdrawal) }}" class="space-y-1">
                                            @csrf
                                            @method('PATCH')
                                            <input name="paid_reference" placeholder="Transaction reference" required class="w-full rounded border-slate-300 text-xs">
                                            <input name="admin_note" placeholder="Note (optional)" class="w-full rounded border-slate-300 text-xs">
                                            <button class="inline-flex rounded border border-teal-300 px-3 py-1 text-xs font-semibold text-teal-700">Mark Paid</button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="px-4 py-8 text-center text-slate-500">No withdrawal records.</td></tr>
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
