<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Services\AuditLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function __construct(private readonly AuditLogService $auditLog)
    {
    }

    public function index(): View
    {
        $this->authorize('viewAny', Product::class);

        return view('admin.products.index', [
            'products' => Product::query()->latest()->paginate(20),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', Product::class);

        return view('admin.products.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Product::class);

        $validated = $this->validatePayload($request);
        $validated['slug'] = $validated['slug'] ?: Str::slug($validated['name']);

        $product = Product::query()->create($validated);

        $this->auditLog->log($request->user(), 'admin.product.created', [
            'product_id' => $product->id,
        ]);

        return redirect()->route('admin.products.index')->with('status', 'Product created successfully.');
    }

    public function edit(Product $product): View
    {
        $this->authorize('update', $product);

        return view('admin.products.edit', [
            'product' => $product,
        ]);
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $this->authorize('update', $product);

        $validated = $this->validatePayload($request, $product);
        $validated['slug'] = $validated['slug'] ?: Str::slug($validated['name']);

        $product->update($validated);

        $this->auditLog->log($request->user(), 'admin.product.updated', [
            'product_id' => $product->id,
        ]);

        return redirect()->route('admin.products.index')->with('status', 'Product updated successfully.');
    }

    public function destroy(Request $request, Product $product): RedirectResponse
    {
        $this->authorize('delete', $product);

        $id = $product->id;
        $product->delete();

        $this->auditLog->log($request->user(), 'admin.product.deleted', [
            'product_id' => $id,
        ]);

        return redirect()->route('admin.products.index')->with('status', 'Product deleted.');
    }

    private function validatePayload(Request $request, ?Product $product = null): array
    {
        $slugRule = Rule::unique('products', 'slug');
        if ($product) {
            $slugRule = $slugRule->ignore($product->id);
        }

        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', $slugRule],
            'description' => ['nullable', 'string', 'max:5000'],
            'price' => ['required', 'numeric', 'min:0'],
            'status' => ['required', Rule::in([Product::STATUS_ACTIVE, Product::STATUS_INACTIVE])],
            'default_commission_type' => ['nullable', Rule::in(['percentage', 'fixed'])],
            'default_commission_value' => ['nullable', 'numeric', 'min:0'],
        ]);
    }
}
