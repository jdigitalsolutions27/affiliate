<x-public-layout>
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
            <div>
                <p class="text-xs uppercase tracking-[0.2em] text-amber-700">Category</p>
                <h1 class="text-3xl font-semibold mt-2">{{ $category->name }}</h1>
                <p class="text-sm text-slate-600 mt-2">{{ $category->description ?: 'Curated products in this category.' }}</p>
            </div>
            <a href="{{ route('products.index') }}" class="inline-flex rounded-md border border-amber-300 px-4 py-2 text-sm font-semibold text-amber-900">All Products</a>
        </div>

        @if ($products->isEmpty())
            <div class="rounded-xl border border-dashed border-amber-300 bg-white p-10 text-center text-slate-500">
                No active products in this category.
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

