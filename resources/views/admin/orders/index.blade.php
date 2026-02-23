<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-slate-800 leading-tight">Orders</h2>
            <a href="{{ route('admin.orders.create') }}" class="inline-flex rounded-md bg-slate-800 px-4 py-2 text-sm font-semibold text-white">Record Manual Order</a>
        </div>
    </x-slot>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-4 pb-6">
        <div class="bg-white border border-slate-200 rounded-lg p-4">
            <form method="GET" class="flex flex-wrap items-end gap-3">
                <div>
                    <label class="block text-xs font-semibold text-slate-600">Status</label>
                    <select name="status" class="mt-1 rounded-md border-slate-300 text-sm">
                        <option value="">All</option>
                        @foreach (['pending','confirmed','completed','cancelled','refunded'] as $status)
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
                            <th class="px-4 py-3">Customer</th>
                            <th class="px-4 py-3">Product</th>
                            <th class="px-4 py-3">Affiliate</th>
                            <th class="px-4 py-3">Total</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="px-4 py-3">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($orders as $order)
                            <tr class="border-t border-slate-100">
                                <td class="px-4 py-3">#{{ $order->id }}</td>
                                <td class="px-4 py-3">
                                    <p class="font-semibold">{{ $order->customer_name }}</p>
                                    <p class="text-xs text-slate-500">{{ $order->customer_email ?: $order->customer_phone }}</p>
                                </td>
                                <td class="px-4 py-3">{{ $order->product?->name ?? 'N/A' }}</td>
                                <td class="px-4 py-3">{{ $order->affiliate?->user?->name ?? '-' }}</td>
                                <td class="px-4 py-3">${{ number_format((float) $order->total_amount, 2) }}</td>
                                <td class="px-4 py-3"><x-status-badge :status="$order->status" /></td>
                                <td class="px-4 py-3">
                                    <a href="{{ route('admin.orders.edit', $order) }}" class="inline-flex rounded border border-slate-300 px-3 py-1 text-xs font-semibold text-slate-700">Edit</a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="px-4 py-8 text-center text-slate-500">No orders found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="px-4 py-3 border-t border-slate-100">
                {{ $orders->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
