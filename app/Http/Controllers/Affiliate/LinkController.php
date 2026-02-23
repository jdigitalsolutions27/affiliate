<?php

namespace App\Http\Controllers\Affiliate;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LinkController extends Controller
{
    public function index(Request $request): View
    {
        $affiliate = $request->user()->affiliate;
        abort_if(! $affiliate, 403);

        $products = Product::query()
            ->where('status', Product::STATUS_ACTIVE)
            ->orderBy('name')
            ->get();

        return view('affiliate.links.index', [
            'products' => $products,
            'affiliate' => $affiliate,
        ]);
    }
}
