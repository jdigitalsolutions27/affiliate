<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Services\AuditLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function __construct(private readonly AuditLogService $auditLog)
    {
    }

    public function index(): View
    {
        return view('admin.categories.index', [
            'categories' => Category::query()->latest()->paginate(20),
        ]);
    }

    public function create(): View
    {
        return view('admin.categories.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validatePayload($request);
        $validated['slug'] = $validated['slug'] ?: Str::slug($validated['name']);

        $category = Category::query()->create($validated);

        $this->auditLog->log($request->user(), 'admin.category.created', [
            'category_id' => $category->id,
        ]);

        return redirect()->route('admin.categories.index')->with('status', 'Category created.');
    }

    public function edit(Category $category): View
    {
        return view('admin.categories.edit', [
            'category' => $category,
        ]);
    }

    public function update(Request $request, Category $category): RedirectResponse
    {
        $validated = $this->validatePayload($request, $category);
        $validated['slug'] = $validated['slug'] ?: Str::slug($validated['name']);
        $category->update($validated);

        $this->auditLog->log($request->user(), 'admin.category.updated', [
            'category_id' => $category->id,
        ]);

        return redirect()->route('admin.categories.index')->with('status', 'Category updated.');
    }

    public function destroy(Request $request, Category $category): RedirectResponse
    {
        $id = $category->id;
        $category->delete();

        $this->auditLog->log($request->user(), 'admin.category.deleted', [
            'category_id' => $id,
        ]);

        return redirect()->route('admin.categories.index')->with('status', 'Category deleted.');
    }

    private function validatePayload(Request $request, ?Category $category = null): array
    {
        $slugRule = Rule::unique('categories', 'slug');
        if ($category) {
            $slugRule = $slugRule->ignore($category->id);
        }

        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', $slugRule],
            'description' => ['nullable', 'string', 'max:2000'],
            'status' => ['required', Rule::in([Category::STATUS_ACTIVE, Category::STATUS_INACTIVE])],
        ]);
    }
}

