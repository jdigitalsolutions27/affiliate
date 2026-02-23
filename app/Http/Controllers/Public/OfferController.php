<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OfferController extends Controller
{
    public function show(Request $request, string $slug): View
    {
        $product = Product::query()
            ->where('slug', $slug)
            ->where('status', Product::STATUS_ACTIVE)
            ->firstOrFail();

        return view('public.offer', [
            'product' => $product,
        ]);
    }
}
