<x-public-layout>
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
            <div>
                <p class="text-xs uppercase tracking-[0.2em] text-amber-700">Catalog</p>
                <h1 class="text-3xl font-semibold mt-2">All Products</h1>
            </div>
            <a href="{{ route('home') }}" class="inline-flex rounded-md border border-amber-300 px-4 py-2 text-sm font-semibold text-amber-900">Back Home</a>
        </div>

        <form method="GET" class="bg-white border border-amber-200 rounded-xl p-4 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3 mb-6">
            <div class="lg:col-span-2">
                <label class="block text-xs font-semibold text-slate-600">Search</label>
                <input type="text" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Product name or keyword" class="mt-1 w-full rounded-md border-slate-300">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-600">Category</label>
                <select name="category" class="mt-1 w-full rounded-md border-slate-300">
                    <option value="">All</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->slug }}" @selected(($filters['category'] ?? '') === $category->slug)>{{ $category->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-600">Min Price</label>
                <input type="number" step="0.01" min="0" name="min_price" value="{{ $filters['min_price'] ?? '' }}" class="mt-1 w-full rounded-md border-slate-300">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-600">Max Price</label>
                <input type="number" step="0.01" min="0" name="max_price" value="{{ $filters['max_price'] ?? '' }}" class="mt-1 w-full rounded-md border-slate-300">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-600">Sort</label>
                <select name="sort" class="mt-1 w-full rounded-md border-slate-300">
                    <option value="latest" @selected(($filters['sort'] ?? 'latest') === 'latest')>Latest</option>
                    <option value="price_asc" @selected(($filters['sort'] ?? '') === 'price_asc')>Price: Low to High</option>
                    <option value="price_desc" @selected(($filters['sort'] ?? '') === 'price_desc')>Price: High to Low</option>
                    <option value="name_asc" @selected(($filters['sort'] ?? '') === 'name_asc')>Name A-Z</option>
                </select>
            </div>
            <div class="sm:col-span-2 lg:col-span-5 flex gap-2">
                <button class="inline-flex rounded-md px-4 py-2 text-sm font-semibold text-white" style="background-color: var(--brand-primary);">Apply Filters</button>
                <a href="{{ route('products.index') }}" class="inline-flex rounded-md border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700">Reset</a>
            </div>
        </form>

        @if ($products->isEmpty())
            <div class="rounded-xl border border-dashed border-amber-300 bg-white p-10 text-center text-slate-500">
                No products match your filters.
            </div>
        @else
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach ($products as $product)
                    @include('public.products._card', ['product' => $product])
                @endforeach
            </div>
            <div class="mt-6">
                {{ $products->links() }}
            </div>
        @endif
    </section>
</x-public-layout>

