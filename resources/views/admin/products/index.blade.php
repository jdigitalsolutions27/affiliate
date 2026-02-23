<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <h2 class="font-semibold text-xl text-slate-800 leading-tight">Products</h2>
            <div class="flex gap-2">
                <a href="{{ route('admin.products.create') }}" class="inline-flex rounded-md bg-slate-800 px-4 py-2 text-sm font-semibold text-white">Add Product</a>
            </div>
        </div>
    </x-slot>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-6">
        <div class="bg-white border border-slate-200 rounded-lg p-4 mb-4">
            <form method="POST" action="{{ route('admin.products.import-csv') }}" enctype="multipart/form-data" class="flex flex-col sm:flex-row gap-3 items-start sm:items-end">
                @csrf
                <div class="flex-1">
                    <label class="block text-sm font-medium text-slate-700">CSV Import (required module)</label>
                    <input type="file" name="csv_file" accept=".csv,text/csv" required class="mt-1 block w-full text-sm text-slate-700">
                    <p class="text-xs text-slate-500 mt-1">
                        Columns: name, slug, description, price, category, stock, status, is_featured, is_best_seller, default_commission_type, default_commission_value, image_url
                    </p>
                </div>
                <button class="inline-flex rounded-md border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700">Import CSV</button>
            </form>
        </div>

        <div class="bg-white border border-slate-200 rounded-lg overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-slate-50 text-left text-slate-600">
                        <tr>
                            <th class="px-4 py-3">Image</th>
                            <th class="px-4 py-3">Name</th>
                            <th class="px-4 py-3">Category</th>
                            <th class="px-4 py-3">Slug</th>
                            <th class="px-4 py-3">Price</th>
                            <th class="px-4 py-3">Stock</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="px-4 py-3">Flags</th>
                            <th class="px-4 py-3">Default Commission</th>
                            <th class="px-4 py-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($products as $product)
                            @php
                                $primaryImage = $product->images->first();
                                $imageUrl = null;
                                if ($primaryImage) {
                                    $imageUrl = str_starts_with($primaryImage->image_path, 'http://') || str_starts_with($primaryImage->image_path, 'https://')
                                        ? $primaryImage->image_path
                                        : Storage::disk('public')->url($primaryImage->image_path);
                                }
                            @endphp
                            <tr class="border-t border-slate-100">
                                <td class="px-4 py-3">
                                    @if ($imageUrl)
                                        <img src="{{ $imageUrl }}" alt="{{ $product->name }}" class="h-10 w-10 rounded object-cover">
                                    @else
                                        <span class="text-xs text-slate-400">No image</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 font-semibold">{{ $product->name }}</td>
                                <td class="px-4 py-3">{{ $product->category?->name ?? '-' }}</td>
                                <td class="px-4 py-3 font-mono text-xs">{{ $product->slug }}</td>
                                <td class="px-4 py-3">${{ number_format((float) $product->price, 2) }}</td>
                                <td class="px-4 py-3">{{ $product->stock ?? '-' }}</td>
                                <td class="px-4 py-3"><x-status-badge :status="$product->status" /></td>
                                <td class="px-4 py-3 text-xs">
                                    @if ($product->is_featured)
                                        <span class="inline-flex rounded-full bg-amber-100 px-2 py-1 text-amber-800 mr-1">Featured</span>
                                    @endif
                                    @if ($product->is_best_seller)
                                        <span class="inline-flex rounded-full bg-emerald-100 px-2 py-1 text-emerald-800">Best Seller</span>
                                    @endif
                                    @if (! $product->is_featured && ! $product->is_best_seller)
                                        <span class="text-slate-400">-</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    @if ($product->default_commission_type)
                                        {{ ucfirst($product->default_commission_type) }}: {{ $product->default_commission_value }}
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex gap-2">
                                        <a href="{{ route('admin.products.edit', $product) }}" class="inline-flex rounded border border-slate-300 px-3 py-1 text-xs font-semibold text-slate-700">Edit</a>
                                        <form method="POST" action="{{ route('admin.products.destroy', $product) }}" onsubmit="return confirm('Delete this product?')">
                                            @csrf
                                            @method('DELETE')
                                            <button class="inline-flex rounded border border-rose-300 px-3 py-1 text-xs font-semibold text-rose-700">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="10" class="px-4 py-8 text-center text-slate-500">No products available.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="px-4 py-3 border-t border-slate-100">
                {{ $products->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
