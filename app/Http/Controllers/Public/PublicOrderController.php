<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Affiliate;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PublicOrderController extends Controller
{
    public function create(Request $request, string $product_slug): View
    {
        $product = Product::query()
            ->where('slug', $product_slug)
            ->where('status', Product::STATUS_ACTIVE)
            ->firstOrFail();

        return view('public.order-form', [
            'product' => $product,
        ]);
    }

    public function store(Request $request, string $product_slug): RedirectResponse
    {
        $product = Product::query()
            ->where('slug', $product_slug)
            ->where('status', Product::STATUS_ACTIVE)
            ->firstOrFail();

        $validated = $request->validate([
            'customer_name' => ['required', 'string', 'max:255'],
            'customer_email' => ['nullable', 'email', 'max:255'],
            'customer_phone' => ['nullable', 'string', 'max:50'],
            'customer_address' => ['nullable', 'string', 'max:2000'],
            'qty' => ['required', 'integer', 'min:1', 'max:1000'],
            'price' => ['required', 'numeric', 'min:0'],
        ]);

        if (! $validated['customer_email'] && ! $validated['customer_phone']) {
            return back()->withErrors([
                'customer_email' => 'Email or phone is required.',
            ])->withInput();
        }

        $affiliateId = $this->extractAffiliateId($request, $product->id);

        DB::transaction(function () use ($validated, $product, $affiliateId) {
            $qty = (int) $validated['qty'];
            $unitPrice = (float) $validated['price'];
            $lineTotal = round($qty * $unitPrice, 2);

            $order = Order::query()->create([
                'customer_name' => $validated['customer_name'],
                'customer_email' => $validated['customer_email'] ?? null,
                'customer_phone' => $validated['customer_phone'] ?? null,
                'customer_address' => $validated['customer_address'] ?? null,
                'product_id' => $product->id,
                'qty' => $qty,
                'total_amount' => $lineTotal,
                'affiliate_id' => $affiliateId,
                'status' => Order::STATUS_PENDING,
                'source' => 'public',
            ]);

            OrderItem::query()->create([
                'order_id' => $order->id,
                'product_id' => $product->id,
                'qty' => $qty,
                'unit_price' => $unitPrice,
                'line_total' => $lineTotal,
            ]);
        });

        return redirect()
            ->route('offers.show', $product->slug)
            ->with('status', 'Order submitted successfully. Our team will review it.');
    }

    private function extractAffiliateId(Request $request, int $productId): ?int
    {
        $raw = $request->cookie('affiliate_referral') ?: $request->session()->get('affiliate_referral');
        if (! $raw) {
            return null;
        }

        $payload = json_decode((string) $raw, true);
        if (! is_array($payload)) {
            return null;
        }

        $affiliateId = $payload['affiliate_id'] ?? null;
        if (! $affiliateId) {
            return null;
        }

        $productMatches = (int) ($payload['product_id'] ?? 0) === $productId;
        if (! $productMatches) {
            return null;
        }

        $active = Affiliate::query()
            ->where('id', $affiliateId)
            ->where('status', Affiliate::STATUS_ACTIVE)
            ->exists();

        return $active ? (int) $affiliateId : null;
    }
}
