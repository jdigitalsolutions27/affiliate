<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
    <div>
        <label class="block text-sm font-medium text-slate-700">Category</label>
        <select name="category_id" class="mt-1 w-full rounded-md border-slate-300">
            <option value="">Uncategorized</option>
            @foreach ($categories as $category)
                <option value="{{ $category->id }}" @selected((string) old('category_id', $product->category_id ?? '') === (string) $category->id)>
                    {{ $category->name }}
                </option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="block text-sm font-medium text-slate-700">Name</label>
        <input name="name" value="{{ old('name', $product->name ?? '') }}" required class="mt-1 w-full rounded-md border-slate-300">
    </div>
    <div>
        <label class="block text-sm font-medium text-slate-700">Slug</label>
        <input name="slug" value="{{ old('slug', $product->slug ?? '') }}" placeholder="auto-generate-if-empty" class="mt-1 w-full rounded-md border-slate-300 font-mono text-sm">
    </div>
    <div>
        <label class="block text-sm font-medium text-slate-700">Price</label>
        <input type="number" step="0.01" min="0" name="price" value="{{ old('price', $product->price ?? '') }}" required class="mt-1 w-full rounded-md border-slate-300">
    </div>
    <div>
        <label class="block text-sm font-medium text-slate-700">Stock (optional)</label>
        <input type="number" min="0" name="stock" value="{{ old('stock', $product->stock ?? '') }}" class="mt-1 w-full rounded-md border-slate-300">
    </div>
    <div>
        <label class="block text-sm font-medium text-slate-700">Status</label>
        <select name="status" class="mt-1 w-full rounded-md border-slate-300">
            <option value="active" @selected(old('status', $product->status ?? 'active') === 'active')>Active</option>
            <option value="inactive" @selected(old('status', $product->status ?? 'active') === 'inactive')>Inactive</option>
        </select>
    </div>
    <div>
        <label class="block text-sm font-medium text-slate-700">Default Commission Type</label>
        <select name="default_commission_type" class="mt-1 w-full rounded-md border-slate-300">
            <option value="">No Default</option>
            <option value="percentage" @selected(old('default_commission_type', $product->default_commission_type ?? '') === 'percentage')>Percentage</option>
            <option value="fixed" @selected(old('default_commission_type', $product->default_commission_type ?? '') === 'fixed')>Fixed</option>
        </select>
    </div>
    <div>
        <label class="block text-sm font-medium text-slate-700">Default Commission Value</label>
        <input type="number" step="0.01" min="0" name="default_commission_value" value="{{ old('default_commission_value', $product->default_commission_value ?? '') }}" class="mt-1 w-full rounded-md border-slate-300">
    </div>
    <div class="sm:col-span-2 grid grid-cols-1 sm:grid-cols-2 gap-4">
        <label class="inline-flex items-center gap-2 text-sm text-slate-700">
            <input type="checkbox" name="is_featured" value="1" @checked(old('is_featured', $product->is_featured ?? false)) class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
            Featured Product
        </label>
        <label class="inline-flex items-center gap-2 text-sm text-slate-700">
            <input type="checkbox" name="is_best_seller" value="1" @checked(old('is_best_seller', $product->is_best_seller ?? false)) class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
            Best Seller
        </label>
    </div>

    <div class="sm:col-span-2">
        <label class="block text-sm font-medium text-slate-700">Description</label>
        <textarea name="description" rows="5" class="mt-1 w-full rounded-md border-slate-300">{{ old('description', $product->description ?? '') }}</textarea>
    </div>

    <div class="sm:col-span-2">
        <label class="block text-sm font-medium text-slate-700">Product Images (multiple)</label>
        <input type="file" name="images[]" multiple accept="image/*" class="mt-1 block w-full text-sm text-slate-700">
        <p class="text-xs text-slate-500 mt-1">PNG, JPG, WEBP up to 4MB each.</p>
    </div>

    @if (! empty($product) && $product->images->isNotEmpty())
        <div class="sm:col-span-2">
            <p class="text-sm font-medium text-slate-700 mb-2">Existing Images</p>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                @foreach ($product->images as $image)
                    @php
                        $imageUrl = str_starts_with($image->image_path, 'http://') || str_starts_with($image->image_path, 'https://')
                            ? $image->image_path
                            : Storage::disk('public')->url($image->image_path);
                    @endphp
                    <label class="border border-slate-200 rounded p-2 text-xs text-slate-600">
                        <img src="{{ $imageUrl }}" alt="{{ $image->alt_text ?: $product->name }}" class="h-20 w-full object-cover rounded">
                        <span class="mt-2 inline-flex items-center gap-2">
                            <input type="checkbox" name="delete_image_ids[]" value="{{ $image->id }}" class="rounded border-slate-300 text-rose-600 focus:ring-rose-500">
                            Remove
                        </span>
                    </label>
                @endforeach
            </div>
        </div>
    @endif
</div>
