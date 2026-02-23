<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Affiliate;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Services\AuditLogService;
use App\Services\CommissionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function __construct(
        private readonly CommissionService $commissionService,
        private readonly AuditLogService $auditLog
    ) {
    }

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Order::class);

        $validated = $request->validate([
            'status' => ['nullable', Rule::in([
                Order::STATUS_PENDING,
                Order::STATUS_CONFIRMED,
                Order::STATUS_CANCELLED,
                Order::STATUS_REFUNDED,
                Order::STATUS_COMPLETED,
            ])],
        ]);

        $query = Order::query()->with(['product', 'affiliate.user']);

        if (! empty($validated['status'])) {
            $query->where('status', $validated['status']);
        }

        return view('admin.orders.index', [
            'orders' => $query->latest()->paginate(20)->withQueryString(),
            'currentStatus' => $validated['status'] ?? null,
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', Order::class);

        return view('admin.orders.create', [
            'products' => Product::query()->where('status', Product::STATUS_ACTIVE)->orderBy('name')->get(),
            'affiliates' => Affiliate::query()->with('user')->where('status', Affiliate::STATUS_ACTIVE)->orderBy('id')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Order::class);

        $validated = $this->validatePayload($request);

        $order = DB::transaction(function () use ($validated) {
            $qty = (int) $validated['qty'];
            $unitPrice = (float) $validated['unit_price'];
            $lineTotal = round($qty * $unitPrice, 2);

            $order = Order::query()->create([
                'customer_name' => $validated['customer_name'],
                'customer_email' => $validated['customer_email'] ?? null,
                'customer_phone' => $validated['customer_phone'] ?? null,
                'customer_address' => $validated['customer_address'] ?? null,
                'customer_notes' => $validated['customer_notes'] ?? null,
                'product_id' => $validated['product_id'],
                'qty' => $qty,
                'total_amount' => $lineTotal,
                'affiliate_id' => $validated['affiliate_id'] ?? null,
                'status' => $validated['status'],
                'source' => 'admin',
                'flow_type' => $validated['flow_type'],
            ]);

            OrderItem::query()->create([
                'order_id' => $order->id,
                'product_id' => $validated['product_id'],
                'qty' => $qty,
                'unit_price' => $unitPrice,
                'line_total' => $lineTotal,
            ]);

            $this->commissionService->syncForOrderStatusChange($order, Order::STATUS_PENDING);

            return $order;
        });

        $this->auditLog->log($request->user(), 'admin.order.created', [
            'order_id' => $order->id,
        ]);

        return redirect()->route('admin.orders.index')->with('status', 'Order created successfully.');
    }

    public function edit(Order $order): View
    {
        $this->authorize('update', $order);

        $order->load(['items.product', 'affiliate.user', 'product', 'commissions']);

        return view('admin.orders.edit', [
            'order' => $order,
            'products' => Product::query()->where('status', Product::STATUS_ACTIVE)->orderBy('name')->get(),
            'affiliates' => Affiliate::query()->with('user')->where('status', Affiliate::STATUS_ACTIVE)->orderBy('id')->get(),
        ]);
    }

    public function update(Request $request, Order $order): RedirectResponse
    {
        $this->authorize('update', $order);

        $validated = $this->validatePayload($request, true);
        $previousStatus = $order->status;

        DB::transaction(function () use ($validated, $order, $previousStatus) {
            $qty = (int) $validated['qty'];
            $unitPrice = (float) $validated['unit_price'];
            $lineTotal = round($qty * $unitPrice, 2);

            $order->update([
                'customer_name' => $validated['customer_name'],
                'customer_email' => $validated['customer_email'] ?? null,
                'customer_phone' => $validated['customer_phone'] ?? null,
                'customer_address' => $validated['customer_address'] ?? null,
                'customer_notes' => $validated['customer_notes'] ?? null,
                'product_id' => $validated['product_id'],
                'qty' => $qty,
                'total_amount' => $lineTotal,
                'affiliate_id' => $validated['affiliate_id'] ?? null,
                'status' => $validated['status'],
                'flow_type' => $validated['flow_type'],
            ]);

            $item = $order->items()->first();
            if ($item) {
                $item->update([
                    'product_id' => $validated['product_id'],
                    'qty' => $qty,
                    'unit_price' => $unitPrice,
                    'line_total' => $lineTotal,
                ]);
            } else {
                OrderItem::query()->create([
                    'order_id' => $order->id,
                    'product_id' => $validated['product_id'],
                    'qty' => $qty,
                    'unit_price' => $unitPrice,
                    'line_total' => $lineTotal,
                ]);
            }

            $this->commissionService->syncForOrderStatusChange($order->fresh(['items', 'affiliate', 'product']), $previousStatus);
        });

        $this->auditLog->log($request->user(), 'admin.order.updated', [
            'order_id' => $order->id,
            'previous_status' => $previousStatus,
            'new_status' => $validated['status'],
        ]);

        return redirect()->route('admin.orders.edit', $order)->with('status', 'Order updated successfully.');
    }

    private function validatePayload(Request $request, bool $isUpdate = false): array
    {
        $rules = [
            'customer_name' => ['required', 'string', 'max:255'],
            'customer_email' => ['nullable', 'email', 'max:255'],
            'customer_phone' => ['nullable', 'string', 'max:50'],
            'customer_address' => ['nullable', 'string', 'max:2000'],
            'customer_notes' => ['nullable', 'string', 'max:2000'],
            'product_id' => ['required', 'exists:products,id'],
            'affiliate_id' => ['nullable', 'exists:affiliates,id'],
            'qty' => ['required', 'integer', 'min:1', 'max:1000'],
            'unit_price' => ['required', 'numeric', 'min:0'],
            'flow_type' => ['required', Rule::in([
                Order::FLOW_ORDER_REQUEST,
                Order::FLOW_CHECKOUT_LITE,
            ])],
            'status' => ['required', Rule::in([
                Order::STATUS_PENDING,
                Order::STATUS_CONFIRMED,
                Order::STATUS_CANCELLED,
                Order::STATUS_REFUNDED,
                Order::STATUS_COMPLETED,
            ])],
        ];

        $validated = $request->validate($rules);

        if (! $validated['customer_email'] && ! $validated['customer_phone']) {
            throw ValidationException::withMessages([
                'customer_email' => 'Email or phone is required.',
            ]);
        }

        return $validated;
    }
}
