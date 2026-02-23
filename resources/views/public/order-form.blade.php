<x-public-layout>
    <section class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        <div class="bg-white border border-slate-200 rounded-xl p-6 sm:p-8">
            @php
                $isCheckoutLite = ($orderMode ?? 'order_request') === \App\Models\Order::FLOW_CHECKOUT_LITE;
            @endphp
            <h1 class="text-2xl font-semibold text-slate-900">
                {{ $isCheckoutLite ? 'Checkout' : 'Order Request' }}: {{ $product->name }}
            </h1>
            <p class="mt-2 text-sm text-slate-500">
                {{ $isCheckoutLite
                    ? 'Simple checkout mode. Submit details and we will confirm your order.'
                    : 'Request mode for inquiry-driven sales (Messenger-friendly). Referral attribution is automatic if a valid tracking cookie exists.' }}
            </p>

            <form method="POST" action="{{ route('public.order.store', $product->slug) }}" class="mt-6 space-y-4">
                @csrf

                <div>
                    <label for="customer_name" class="block text-sm font-medium text-slate-700">Name</label>
                    <input id="customer_name" name="customer_name" value="{{ old('customer_name') }}" required class="mt-1 w-full rounded-md border-slate-300 focus:border-indigo-500 focus:ring-indigo-500">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="customer_email" class="block text-sm font-medium text-slate-700">Email</label>
                        <input id="customer_email" name="customer_email" type="email" value="{{ old('customer_email') }}" class="mt-1 w-full rounded-md border-slate-300 focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label for="customer_phone" class="block text-sm font-medium text-slate-700">Phone</label>
                        <input id="customer_phone" name="customer_phone" value="{{ old('customer_phone') }}" class="mt-1 w-full rounded-md border-slate-300 focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                </div>

                <div>
                    <label for="customer_address" class="block text-sm font-medium text-slate-700">Address</label>
                    <textarea id="customer_address" name="customer_address" rows="3" class="mt-1 w-full rounded-md border-slate-300 focus:border-indigo-500 focus:ring-indigo-500">{{ old('customer_address') }}</textarea>
                </div>
                <div>
                    <label for="customer_notes" class="block text-sm font-medium text-slate-700">Notes</label>
                    <textarea id="customer_notes" name="customer_notes" rows="3" class="mt-1 w-full rounded-md border-slate-300 focus:border-indigo-500 focus:ring-indigo-500">{{ old('customer_notes') }}</textarea>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="qty" class="block text-sm font-medium text-slate-700">Quantity</label>
                        <input id="qty" name="qty" type="number" min="1" value="{{ old('qty', 1) }}" required class="mt-1 w-full rounded-md border-slate-300 focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                    @if ($isCheckoutLite)
                        <div>
                            <label for="price" class="block text-sm font-medium text-slate-700">Unit Price</label>
                            <input id="price" name="price" type="number" step="0.01" min="0" value="{{ old('price', number_format((float) $product->price, 2, '.', '')) }}" required class="mt-1 w-full rounded-md border-slate-300 focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                    @else
                        <div>
                            <label class="block text-sm font-medium text-slate-700">Current Price</label>
                            <div class="mt-1 rounded-md border border-slate-300 px-3 py-2 text-slate-700">${{ number_format((float) $product->price, 2) }}</div>
                        </div>
                    @endif
                </div>

                <div class="pt-2 flex flex-wrap gap-3">
                    <button class="inline-flex rounded-md bg-indigo-600 px-5 py-3 text-sm font-semibold text-white">
                        {{ $isCheckoutLite ? 'Place Order' : 'Submit Request' }}
                    </button>
                    <a href="{{ route('products.show', $product->slug) }}" class="inline-flex rounded-md border border-slate-300 px-5 py-3 text-sm font-semibold text-slate-700">Back to Product</a>
                </div>
            </form>
        </div>
    </section>
</x-public-layout>
