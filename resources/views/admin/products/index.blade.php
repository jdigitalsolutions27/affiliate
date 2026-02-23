<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-slate-800 leading-tight">Products</h2>
            <a href="{{ route('admin.products.create') }}" class="inline-flex rounded-md bg-slate-800 px-4 py-2 text-sm font-semibold text-white">Add Product</a>
        </div>
    </x-slot>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-6">
        <div class="bg-white border border-slate-200 rounded-lg overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-slate-50 text-left text-slate-600">
                        <tr>
                            <th class="px-4 py-3">Name</th>
                            <th class="px-4 py-3">Slug</th>
                            <th class="px-4 py-3">Price</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="px-4 py-3">Default Commission</th>
                            <th class="px-4 py-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($products as $product)
                            <tr class="border-t border-slate-100">
                                <td class="px-4 py-3 font-semibold">{{ $product->name }}</td>
                                <td class="px-4 py-3 font-mono text-xs">{{ $product->slug }}</td>
                                <td class="px-4 py-3">${{ number_format((float) $product->price, 2) }}</td>
                                <td class="px-4 py-3"><x-status-badge :status="$product->status" /></td>
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
                            <tr><td colspan="6" class="px-4 py-8 text-center text-slate-500">No products available.</td></tr>
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
