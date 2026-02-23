<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
    <div>
        <label class="block text-sm font-medium text-slate-700">Category Name</label>
        <input name="name" value="{{ old('name', $category->name ?? '') }}" required class="mt-1 w-full rounded-md border-slate-300">
    </div>
    <div>
        <label class="block text-sm font-medium text-slate-700">Slug</label>
        <input name="slug" value="{{ old('slug', $category->slug ?? '') }}" class="mt-1 w-full rounded-md border-slate-300 font-mono text-sm" placeholder="auto-generate-if-empty">
    </div>

    <div class="sm:col-span-2">
        <label class="block text-sm font-medium text-slate-700">Description</label>
        <textarea name="description" rows="4" class="mt-1 w-full rounded-md border-slate-300">{{ old('description', $category->description ?? '') }}</textarea>
    </div>

    <div>
        <label class="block text-sm font-medium text-slate-700">Status</label>
        <select name="status" class="mt-1 w-full rounded-md border-slate-300">
            <option value="active" @selected(old('status', $category->status ?? 'active') === 'active')>Active</option>
            <option value="inactive" @selected(old('status', $category->status ?? 'active') === 'inactive')>Inactive</option>
        </select>
    </div>
</div>

