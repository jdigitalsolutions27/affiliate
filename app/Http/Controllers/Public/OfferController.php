<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;

class OfferController extends Controller
{
    public function show(string $slug): RedirectResponse
    {
        $product = Product::query()
            ->where('slug', $slug)
            ->where('status', Product::STATUS_ACTIVE)
            ->firstOrFail();

        return redirect()->route('products.show', $product->slug);
    }
}
