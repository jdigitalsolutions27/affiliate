<x-public-layout>
    <section class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        <div class="bg-white border border-slate-200 rounded-xl p-6 sm:p-8">
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div>
                    <p class="text-xs uppercase tracking-[0.2em] text-slate-500">Offer</p>
                    <h1 class="mt-2 text-3xl font-semibold text-slate-900">{{ $product->name }}</h1>
                </div>
                <p class="text-2xl font-bold text-indigo-700">${{ number_format((float) $product->price, 2) }}</p>
            </div>

            <p class="mt-6 text-slate-600">{{ $product->description ?: 'No description available for this offer.' }}</p>

            <div class="mt-8 flex flex-wrap gap-3">
                <a href="{{ route('public.order.create', $product->slug) }}" class="inline-flex rounded-md bg-indigo-600 px-5 py-3 text-sm font-semibold text-white">Place Order</a>
                <a href="{{ route('home') }}" class="inline-flex rounded-md border border-slate-300 px-5 py-3 text-sm font-semibold text-slate-700">Back to Home</a>
            </div>

            <div class="mt-8 rounded-lg border border-amber-200 bg-amber-50 p-4 text-xs text-amber-800">
                This page uses referral cookies for attribution when you arrive via an affiliate link.
            </div>
        </div>
    </section>
</x-public-layout>
