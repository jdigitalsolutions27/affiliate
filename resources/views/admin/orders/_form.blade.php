@php
    $editing = isset($order);
    $selectedProduct = old('product_id', $order->product_id ?? '');
    $selectedAffiliate = old('affiliate_id', $order->affiliate_id ?? '');
@endphp

<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
    <div>
        <label class="block text-sm font-medium text-slate-700">Customer Name</label>
        <input name="customer_name" value="{{ old('customer_name', $order->customer_name ?? '') }}" required class="mt-1 w-full rounded-md border-slate-300">
    </div>
    <div>
        <label class="block text-sm font-medium text-slate-700">Customer Email</label>
        <input type="email" name="customer_email" value="{{ old('customer_email', $order->customer_email ?? '') }}" class="mt-1 w-full rounded-md border-slate-300">
    </div>
    <div>
        <label class="block text-sm font-medium text-slate-700">Customer Phone</label>
        <input name="customer_phone" value="{{ old('customer_phone', $order->customer_phone ?? '') }}" class="mt-1 w-full rounded-md border-slate-300">
    </div>
    <div>
        <label class="block text-sm font-medium text-slate-700">Status</label>
        <select name="status" class="mt-1 w-full rounded-md border-slate-300">
            @foreach (['pending','confirmed','completed','cancelled','refunded'] as $status)
                <option value="{{ $status }}" @selected(old('status', $order->status ?? 'pending') === $status)>{{ ucfirst($status) }}</option>
            @endforeach
        </select>
    </div>

    <div>
        <label class="block text-sm font-medium text-slate-700">Product</label>
        <select name="product_id" required class="mt-1 w-full rounded-md border-slate-300">
            <option value="">Select Product</option>
            @foreach ($products as $product)
                <option value="{{ $product->id }}" @selected((string) $selectedProduct === (string) $product->id)>
                    {{ $product->name }}
                </option>
            @endforeach
        </select>
    </div>

    <div>
        <label class="block text-sm font-medium text-slate-700">Affiliate (Optional)</label>
        <select name="affiliate_id" class="mt-1 w-full rounded-md border-slate-300">
            <option value="">No Affiliate</option>
            @foreach ($affiliates as $affiliate)
                <option value="{{ $affiliate->id }}" @selected((string) $selectedAffiliate === (string) $affiliate->id)>
                    {{ $affiliate->user?->name }} ({{ $affiliate->code }})
                </option>
            @endforeach
        </select>
    </div>

    <div>
        <label class="block text-sm font-medium text-slate-700">Quantity</label>
        <input type="number" name="qty" min="1" value="{{ old('qty', $order->qty ?? 1) }}" required class="mt-1 w-full rounded-md border-slate-300">
    </div>

    <div>
        <label class="block text-sm font-medium text-slate-700">Unit Price</label>
        @php
            $defaultUnitPrice = $editing ? ($order->items->first()->unit_price ?? $order->total_amount) : '';
        @endphp
        <input type="number" name="unit_price" step="0.01" min="0" value="{{ old('unit_price', $defaultUnitPrice) }}" required class="mt-1 w-full rounded-md border-slate-300">
    </div>

    <div class="sm:col-span-2">
        <label class="block text-sm font-medium text-slate-700">Customer Address</label>
        <textarea name="customer_address" rows="3" class="mt-1 w-full rounded-md border-slate-300">{{ old('customer_address', $order->customer_address ?? '') }}</textarea>
    </div>
</div>
