<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Services\AuditLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
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
            'products' => Product::query()
                ->with(['category', 'images'])
                ->latest()
                ->paginate(20),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', Product::class);

        return view('admin.products.create', [
            'categories' => Category::query()->active()->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Product::class);

        $validated = $this->validatePayload($request);
        $validated['slug'] = $validated['slug'] ?: Str::slug($validated['name']);
        $attributes = Arr::except($validated, ['images', 'delete_image_ids']);

        $product = DB::transaction(function () use ($attributes, $request) {
            $product = Product::query()->create($attributes);
            $this->syncImages($request, $product);

            return $product;
        });

        $this->auditLog->log($request->user(), 'admin.product.created', [
            'product_id' => $product->id,
        ]);

        return redirect()->route('admin.products.index')->with('status', 'Product created successfully.');
    }

    public function edit(Product $product): View
    {
        $this->authorize('update', $product);

        return view('admin.products.edit', [
            'product' => $product->load('images'),
            'categories' => Category::query()->active()->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $this->authorize('update', $product);

        $validated = $this->validatePayload($request, $product);
        $validated['slug'] = $validated['slug'] ?: Str::slug($validated['name']);
        $attributes = Arr::except($validated, ['images', 'delete_image_ids']);

        DB::transaction(function () use ($product, $attributes, $request) {
            $product->update($attributes);
            $this->syncImages($request, $product);
        });

        $this->auditLog->log($request->user(), 'admin.product.updated', [
            'product_id' => $product->id,
        ]);

        return redirect()->route('admin.products.index')->with('status', 'Product updated successfully.');
    }

    public function destroy(Request $request, Product $product): RedirectResponse
    {
        $this->authorize('delete', $product);

        $id = $product->id;
        foreach ($product->images as $image) {
            $this->deleteImageAsset($image);
        }
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

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', $slugRule],
            'category_id' => ['nullable', 'exists:categories,id'],
            'description' => ['nullable', 'string', 'max:5000'],
            'price' => ['required', 'numeric', 'min:0'],
            'stock' => ['nullable', 'integer', 'min:0'],
            'status' => ['required', Rule::in([Product::STATUS_ACTIVE, Product::STATUS_INACTIVE])],
            'is_featured' => ['nullable', 'boolean'],
            'is_best_seller' => ['nullable', 'boolean'],
            'default_commission_type' => ['nullable', Rule::in(['percentage', 'fixed'])],
            'default_commission_value' => ['nullable', 'numeric', 'min:0'],
            'images.*' => ['nullable', 'image', 'max:4096'],
            'delete_image_ids' => ['nullable', 'array'],
            'delete_image_ids.*' => ['integer'],
        ]);

        $validated['is_featured'] = $request->boolean('is_featured');
        $validated['is_best_seller'] = $request->boolean('is_best_seller');

        return $validated;
    }

    public function importCsv(Request $request): RedirectResponse
    {
        $this->authorize('create', Product::class);

        $payload = $request->validate([
            'csv_file' => ['required', 'file', 'mimes:csv,txt', 'max:8192'],
        ]);

        $rows = array_map('str_getcsv', file($payload['csv_file']->getRealPath()));
        if (count($rows) < 2) {
            return back()->withErrors(['csv_file' => 'CSV file is empty.']);
        }

        $header = array_map(fn ($item) => Str::of((string) $item)->trim()->lower()->toString(), array_shift($rows));
        $required = ['name', 'price'];
        foreach ($required as $column) {
            if (! in_array($column, $header, true)) {
                return back()->withErrors(['csv_file' => "Missing required CSV column: {$column}"]);
            }
        }

        $processed = 0;
        DB::transaction(function () use ($rows, $header, &$processed) {
            foreach ($rows as $row) {
                if (count(array_filter($row, fn ($cell) => trim((string) $cell) !== '')) === 0) {
                    continue;
                }

                $item = [];
                foreach ($header as $index => $column) {
                    $item[$column] = isset($row[$index]) ? trim((string) $row[$index]) : null;
                }

                $name = $item['name'] ?? null;
                if (! $name) {
                    continue;
                }

                $slug = ! empty($item['slug']) ? Str::slug($item['slug']) : Str::slug($name);
                if ($slug === '') {
                    continue;
                }

                $categoryId = null;
                if (! empty($item['category'])) {
                    $category = Category::query()->firstOrCreate(
                        ['slug' => Str::slug($item['category'])],
                        ['name' => $item['category'], 'status' => Category::STATUS_ACTIVE]
                    );
                    $categoryId = $category->id;
                }

                $product = Product::query()->updateOrCreate(
                    ['slug' => $slug],
                    [
                        'category_id' => $categoryId,
                        'name' => $name,
                        'description' => $item['description'] ?? null,
                        'price' => max((float) ($item['price'] ?? 0), 0),
                        'stock' => isset($item['stock']) && $item['stock'] !== '' ? max((int) $item['stock'], 0) : null,
                        'status' => ($item['status'] ?? Product::STATUS_ACTIVE) === Product::STATUS_INACTIVE
                            ? Product::STATUS_INACTIVE
                            : Product::STATUS_ACTIVE,
                        'is_featured' => in_array(strtolower((string) ($item['is_featured'] ?? '0')), ['1', 'yes', 'true', 'y'], true),
                        'is_best_seller' => in_array(strtolower((string) ($item['is_best_seller'] ?? '0')), ['1', 'yes', 'true', 'y'], true),
                        'default_commission_type' => in_array(($item['default_commission_type'] ?? ''), ['percentage', 'fixed'], true)
                            ? $item['default_commission_type']
                            : null,
                        'default_commission_value' => isset($item['default_commission_value']) && $item['default_commission_value'] !== ''
                            ? max((float) $item['default_commission_value'], 0)
                            : null,
                    ]
                );

                if (! empty($item['image_url'])) {
                    ProductImage::query()->firstOrCreate(
                        ['product_id' => $product->id, 'image_path' => $item['image_url']],
                        ['alt_text' => $product->name, 'sort_order' => 0]
                    );
                }

                $processed++;
            }
        });

        $this->auditLog->log($request->user(), 'admin.product.csv_imported', [
            'processed' => $processed,
        ]);

        return redirect()->route('admin.products.index')->with('status', "CSV import complete. {$processed} product(s) processed.");
    }

    private function syncImages(Request $request, Product $product): void
    {
        $deleteImageIds = collect($request->input('delete_image_ids', []))
            ->filter(fn ($id) => is_numeric($id))
            ->map(fn ($id) => (int) $id)
            ->values();

        if ($deleteImageIds->isNotEmpty()) {
            $imagesToDelete = $product->images()->whereIn('id', $deleteImageIds)->get();
            foreach ($imagesToDelete as $image) {
                $this->deleteImageAsset($image);
                $image->delete();
            }
        }

        $uploads = $request->file('images', []);
        if (! is_array($uploads) || empty($uploads)) {
            return;
        }

        $nextSort = (int) $product->images()->max('sort_order') + 1;

        foreach ($uploads as $upload) {
            if (! $upload) {
                continue;
            }

            $path = $upload->store('products', 'public');
            ProductImage::query()->create([
                'product_id' => $product->id,
                'image_path' => $path,
                'alt_text' => $product->name,
                'sort_order' => $nextSort++,
            ]);
        }
    }

    private function deleteImageAsset(ProductImage $image): void
    {
        if (str_starts_with($image->image_path, 'http://') || str_starts_with($image->image_path, 'https://')) {
            return;
        }

        if (Storage::disk('public')->exists($image->image_path)) {
            Storage::disk('public')->delete($image->image_path);
        }
    }
}
