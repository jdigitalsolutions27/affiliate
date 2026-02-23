<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-800 leading-tight">Edit Order #{{ $order->id }}</h2>
    </x-slot>

    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 space-y-4 pb-6">
        <form method="POST" action="{{ route('admin.orders.update', $order) }}" class="bg-white border border-slate-200 rounded-lg p-6">
            @csrf
            @method('PUT')
            @include('admin.orders._form', ['order' => $order, 'products' => $products, 'affiliates' => $affiliates])

            <div class="mt-6 flex gap-3">
                <button class="inline-flex rounded-md bg-slate-800 px-4 py-2 text-sm font-semibold text-white">Save Changes</button>
                <a href="{{ route('admin.orders.index') }}" class="inline-flex rounded-md border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700">Back</a>
            </div>
        </form>

        <div class="bg-white border border-slate-200 rounded-lg p-6">
            <h3 class="font-semibold text-slate-800 mb-3">Order Commissions</h3>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="text-left text-slate-600 border-b">
                        <tr>
                            <th class="py-2">ID</th>
                            <th class="py-2">Type</th>
                            <th class="py-2">Rate</th>
                            <th class="py-2">Amount</th>
                            <th class="py-2">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($order->commissions as $commission)
                            <tr class="border-b border-slate-100">
                                <td class="py-2">#{{ $commission->id }}</td>
                                <td class="py-2">{{ ucfirst($commission->commission_type) }}</td>
                                <td class="py-2">{{ $commission->rate_value }}</td>
                                <td class="py-2">${{ number_format((float) $commission->amount, 2) }}</td>
                                <td class="py-2"><x-status-badge :status="$commission->status" /></td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="py-4 text-center text-slate-500">No commissions for this order.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
