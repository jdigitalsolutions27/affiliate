<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(Request $request): View
    {
        $products = Product::query()
            ->where('status', Product::STATUS_ACTIVE)
            ->latest()
            ->take(6)
            ->get();

        return view('public.home', [
            'products' => $products,
        ]);
    }
}
