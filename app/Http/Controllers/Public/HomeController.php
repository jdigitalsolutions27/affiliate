<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Throwable;

class HomeController extends Controller
{
    public function index(Request $request): View
    {
        try {
            $products = Product::query()
                ->where('status', Product::STATUS_ACTIVE)
                ->latest()
                ->take(6)
                ->get();
        } catch (Throwable) {
            $products = new Collection();
        }

        return view('public.home', [
            'products' => $products,
        ]);
    }
}
