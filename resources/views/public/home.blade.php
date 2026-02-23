<x-public-layout>
    <section class="relative overflow-hidden" style="background: linear-gradient(135deg, {{ $brand['primary_color'] }} 0%, {{ $brand['secondary_color'] }} 55%, {{ $brand['accent_color'] }} 100%);">
        <div class="absolute -top-24 -right-20 h-64 w-64 rounded-full bg-white/15 blur-2xl"></div>
        <div class="absolute -bottom-20 -left-20 h-64 w-64 rounded-full bg-white/10 blur-2xl"></div>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 lg:py-20 relative z-10 text-white">
            <p class="text-sm uppercase tracking-[0.24em] text-amber-100">Red Fairy Handmade Organic</p>
            <h1 class="mt-5 text-4xl md:text-5xl font-bold max-w-4xl">{{ $brand['hero_title'] }}</h1>
            <p class="mt-6 max-w-2xl text-amber-100/90">{{ $brand['hero_subtitle'] }}</p>
            <div class="mt-8 flex flex-wrap gap-3">
                <a href="{{ route('products.index') }}" class="inline-flex rounded-md bg-white px-5 py-3 text-sm font-semibold text-slate-900">Browse Products</a>
                <a href="{{ route('login') }}" class="inline-flex rounded-md border border-white/40 px-5 py-3 text-sm font-semibold text-white">Affiliate Login</a>
                @if (! empty($brand['facebook_url']))
                    <a href="{{ $brand['facebook_url'] }}" target="_blank" rel="noopener" class="inline-flex rounded-md border border-white/40 px-5 py-3 text-sm font-semibold text-white">Facebook Page</a>
                @endif
            </div>
        </div>
    </section>

    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
            <h2 class="text-2xl font-semibold">Featured Products</h2>
            <a href="{{ route('products.index') }}" class="text-sm font-semibold underline" style="color: {{ $brand['primary_color'] }};">View All</a>
        </div>

        @if ($featuredProducts->isEmpty())
            <div class="rounded-lg border border-dashed border-amber-300 bg-white p-8 text-center text-slate-500">
                No active products yet.
            </div>
        @else
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach ($featuredProducts as $product)
                    @include('public.products._card', ['product' => $product])
                @endforeach
            </div>
        @endif
    </section>

    @if ($categories->isNotEmpty())
        <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-6">
            <h2 class="text-2xl font-semibold mb-4">Shop by Category</h2>
            <div class="flex flex-wrap gap-2">
                @foreach ($categories as $category)
                    <a href="{{ route('categories.show', $category->slug) }}" class="inline-flex rounded-full border border-amber-300 bg-white px-4 py-2 text-sm text-slate-700 hover:bg-amber-100">
                        {{ $category->name }}
                    </a>
                @endforeach
            </div>
        </section>
    @endif

    @if ($bestSellers->isNotEmpty())
        <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-12">
            <h2 class="text-2xl font-semibold mb-6">Best Sellers</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                @foreach ($bestSellers as $product)
                    @include('public.products._card', ['product' => $product])
                @endforeach
            </div>
        </section>
    @endif
</x-public-layout>

