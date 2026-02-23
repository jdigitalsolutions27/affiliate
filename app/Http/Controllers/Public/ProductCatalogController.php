<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Services\AppSettingService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProductCatalogController extends Controller
{
    public function __construct(private readonly AppSettingService $settings)
    {
    }

    public function index(Request $request): View
    {
        $validated = $request->validate([
            'q' => ['nullable', 'string', 'max:120'],
            'category' => ['nullable', 'string', 'max:120'],
            'min_price' => ['nullable', 'numeric', 'min:0'],
            'max_price' => ['nullable', 'numeric', 'min:0'],
            'sort' => ['nullable', Rule::in(['latest', 'price_asc', 'price_desc', 'name_asc'])],
        ]);

        $query = Product::query()
            ->with(['images', 'category'])
            ->where('status', Product::STATUS_ACTIVE);

        if (! empty($validated['q'])) {
            $keyword = trim((string) $validated['q']);
            $query->where(function ($nested) use ($keyword) {
                $nested->where('name', 'like', "%{$keyword}%")
                    ->orWhere('description', 'like', "%{$keyword}%");
            });
        }

        if (! empty($validated['category'])) {
            $query->whereHas('category', function ($categoryQuery) use ($validated) {
                $categoryQuery->where('slug', $validated['category']);
            });
        }

        if (isset($validated['min_price'])) {
            $query->where('price', '>=', (float) $validated['min_price']);
        }
        if (isset($validated['max_price'])) {
            $query->where('price', '<=', (float) $validated['max_price']);
        }

        match ($validated['sort'] ?? 'latest') {
            'price_asc' => $query->orderBy('price'),
            'price_desc' => $query->orderByDesc('price'),
            'name_asc' => $query->orderBy('name'),
            default => $query->latest(),
        };

        return view('public.products.index', [
            'products' => $query->paginate(12)->withQueryString(),
            'categories' => Category::query()->active()->orderBy('name')->get(),
            'filters' => $validated,
            'brand' => $this->settings->brandSettings(),
        ]);
    }

    public function show(string $slug): View
    {
        $product = Product::query()
            ->with(['images', 'category'])
            ->where('slug', $slug)
            ->where('status', Product::STATUS_ACTIVE)
            ->firstOrFail();

        $relatedProducts = Product::query()
            ->with('images')
            ->where('status', Product::STATUS_ACTIVE)
            ->where('id', '!=', $product->id)
            ->when($product->category_id, fn ($query) => $query->where('category_id', $product->category_id))
            ->latest()
            ->limit(4)
            ->get();

        return view('public.products.show', [
            'product' => $product,
            'relatedProducts' => $relatedProducts,
            'brand' => $this->settings->brandSettings(),
            'orderMode' => $this->settings->get(AppSettingService::KEY_PUBLIC_ORDER_MODE, 'order_request'),
        ]);
    }

    public function byCategory(string $slug): View
    {
        $category = Category::query()
            ->active()
            ->where('slug', $slug)
            ->firstOrFail();

        $products = Product::query()
            ->with('images')
            ->where('status', Product::STATUS_ACTIVE)
            ->where('category_id', $category->id)
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('public.products.category', [
            'category' => $category,
            'products' => $products,
            'categories' => Category::query()->active()->orderBy('name')->get(),
            'brand' => $this->settings->brandSettings(),
            'filters' => [
                'category' => $slug,
            ],
        ]);
    }
}
