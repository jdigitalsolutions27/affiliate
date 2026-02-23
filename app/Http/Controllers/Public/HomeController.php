<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Services\AppSettingService;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Throwable;

class HomeController extends Controller
{
    public function __construct(private readonly AppSettingService $settings)
    {
    }

    public function index(Request $request): View
    {
        try {
            $featuredProducts = Product::query()
                ->with(['images', 'category'])
                ->where('status', Product::STATUS_ACTIVE)
                ->where('is_featured', true)
                ->latest()
                ->take(6)
                ->get();

            if ($featuredProducts->isEmpty()) {
                $featuredProducts = Product::query()
                    ->with(['images', 'category'])
                    ->where('status', Product::STATUS_ACTIVE)
                    ->latest()
                    ->take(6)
                    ->get();
            }

            $bestSellers = Product::query()
                ->with(['images', 'category'])
                ->where('status', Product::STATUS_ACTIVE)
                ->where('is_best_seller', true)
                ->latest()
                ->take(4)
                ->get();

            $categories = Category::query()
                ->active()
                ->orderBy('name')
                ->take(8)
                ->get();
        } catch (Throwable) {
            $featuredProducts = new Collection();
            $bestSellers = new Collection();
            $categories = new Collection();
        }

        return view('public.home', [
            'featuredProducts' => $featuredProducts,
            'bestSellers' => $bestSellers,
            'categories' => $categories,
            'brand' => $this->settings->brandSettings(),
            'orderMode' => $this->settings->get(AppSettingService::KEY_PUBLIC_ORDER_MODE, 'order_request'),
        ]);
    }
}
