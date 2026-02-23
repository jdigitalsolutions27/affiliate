<x-public-layout>
    <section class="bg-gradient-to-br from-slate-900 via-slate-800 to-indigo-900 text-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
            <p class="text-sm uppercase tracking-[0.2em] text-indigo-200">Performance Marketing Platform</p>
            <h1 class="mt-4 text-4xl md:text-5xl font-bold max-w-3xl">Track affiliate traffic, sales, and commissions in one secure web app.</h1>
            <p class="mt-6 max-w-2xl text-slate-200">Built for teams that need transparent reporting, manual payout control, and affiliate-level access restrictions.</p>
            <div class="mt-8 flex flex-wrap gap-3">
                <a href="{{ route('login') }}" class="inline-flex rounded-md bg-white px-5 py-3 text-sm font-semibold text-slate-900">Affiliate Login</a>
                <a href="#offers" class="inline-flex rounded-md border border-white/30 px-5 py-3 text-sm font-semibold">Browse Offers</a>
            </div>
        </div>
    </section>

    <section id="offers" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-2xl font-semibold">Active Offers</h2>
            <span class="text-sm text-slate-500">{{ $products->count() }} products</span>
        </div>

        @if ($products->isEmpty())
            <div class="rounded-lg border border-dashed border-slate-300 bg-white p-8 text-center text-slate-500">
                No active offers available yet.
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach ($products as $product)
                    <article class="bg-white border border-slate-200 rounded-lg p-5 shadow-sm">
                        <h3 class="text-lg font-semibold">{{ $product->name }}</h3>
                        <p class="text-sm text-slate-500 mt-2 line-clamp-3">{{ $product->description ?: 'No description provided.' }}</p>
                        <p class="mt-4 text-xl font-bold text-slate-900">${{ number_format((float) $product->price, 2) }}</p>
                        <div class="mt-4 flex gap-2">
                            <a href="{{ route('offers.show', $product->slug) }}" class="inline-flex rounded-md bg-slate-800 px-3 py-2 text-xs font-semibold text-white">View Offer</a>
                            <a href="{{ route('public.order.create', $product->slug) }}" class="inline-flex rounded-md border border-slate-300 px-3 py-2 text-xs font-semibold text-slate-700">Order Form</a>
                        </div>
                    </article>
                @endforeach
            </div>
        @endif
    </section>
</x-public-layout>
