<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
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

    <div class="sm:col-span-2">
        <label class="block text-sm font-medium text-slate-700">Description</label>
        <textarea name="description" rows="5" class="mt-1 w-full rounded-md border-slate-300">{{ old('description', $product->description ?? '') }}</textarea>
    </div>
</div>
