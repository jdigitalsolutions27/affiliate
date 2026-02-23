<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-800 leading-tight">My Tracking Links</h2>
    </x-slot>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-6">
        <div class="bg-white border border-slate-200 rounded-lg overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-slate-50 text-left text-slate-600">
                        <tr>
                            <th class="px-4 py-3">Product</th>
                            <th class="px-4 py-3">Price</th>
                            <th class="px-4 py-3">Offer URL</th>
                            <th class="px-4 py-3">Tracking Link</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($products as $product)
                            @php
                                $trackingLink = route('referral.track', ['code' => $affiliate->code]).'?p='.$product->slug;
                            @endphp
                            <tr class="border-t border-slate-100">
                                <td class="px-4 py-3 font-semibold">{{ $product->name }}</td>
                                <td class="px-4 py-3">${{ number_format((float) $product->price, 2) }}</td>
                                <td class="px-4 py-3">
                                    <a href="{{ route('offers.show', $product->slug) }}" class="text-indigo-700 underline" target="_blank">{{ route('offers.show', $product->slug) }}</a>
                                </td>
                                <td class="px-4 py-3">
                                    <input readonly value="{{ $trackingLink }}" class="w-full rounded-md border-slate-300 bg-slate-50 text-xs font-mono" onclick="this.select();">
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="px-4 py-8 text-center text-slate-500">No active products available.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
