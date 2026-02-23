<x-public-layout>
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <div>
                @php
                    $images = $product->images;
                    $mainImage = $images->first();
                    $mainImageUrl = null;
                    if ($mainImage) {
                        $mainImageUrl = str_starts_with($mainImage->image_path, 'http://') || str_starts_with($mainImage->image_path, 'https://')
                            ? $mainImage->image_path
                            : Storage::disk('public')->url($mainImage->image_path);
                    }
                @endphp
                <div class="rounded-xl border border-amber-200 overflow-hidden bg-white">
                    <div class="h-[420px]">
                        @if ($mainImageUrl)
                            <img src="{{ $mainImageUrl }}" alt="{{ $mainImage->alt_text ?: $product->name }}" class="h-full w-full object-cover">
                        @else
                            <div class="h-full flex items-center justify-center text-slate-400">No product image</div>
                        @endif
                    </div>
                </div>
                @if ($images->count() > 1)
                    <div class="grid grid-cols-4 gap-3 mt-3">
                        @foreach ($images as $image)
                            @php
                                $thumb = str_starts_with($image->image_path, 'http://') || str_starts_with($image->image_path, 'https://')
                                    ? $image->image_path
                                    : Storage::disk('public')->url($image->image_path);
                            @endphp
                            <img src="{{ $thumb }}" alt="{{ $image->alt_text ?: $product->name }}" class="h-20 w-full rounded-lg object-cover border border-amber-200">
                        @endforeach
                    </div>
                @endif
            </div>

            <div class="bg-white border border-amber-200 rounded-xl p-6">
                @if ($product->category)
                    <a href="{{ route('categories.show', $product->category->slug) }}" class="text-xs uppercase tracking-[0.2em] font-semibold text-amber-700">{{ $product->category->name }}</a>
                @endif
                <h1 class="mt-3 text-3xl font-semibold">{{ $product->name }}</h1>
                <p class="mt-4 text-slate-600 leading-relaxed">{{ $product->description ?: 'No description provided for this product yet.' }}</p>

                <div class="mt-6 flex items-center justify-between">
                    <p class="text-3xl font-bold" style="color: var(--brand-primary);">${{ number_format((float) $product->price, 2) }}</p>
                    @if (! is_null($product->stock))
                        <span class="text-sm text-slate-500">Stock: {{ $product->stock }}</span>
                    @endif
                </div>

                <div class="mt-6 flex flex-wrap gap-3">
                    <a href="{{ route('public.order.create', $product->slug) }}" class="inline-flex rounded-md px-5 py-3 text-sm font-semibold text-white" style="background-color: var(--brand-primary);">
                        {{ $orderMode === \App\Models\Order::FLOW_CHECKOUT_LITE ? 'Order Now' : 'Send Order Request' }}
                    </a>
                    <a href="{{ route('products.index') }}" class="inline-flex rounded-md border border-slate-300 px-5 py-3 text-sm font-semibold text-slate-700">Back to Products</a>
                </div>

                <div class="mt-6 rounded-lg border border-amber-200 bg-amber-50 p-4 text-xs text-amber-900">
                    Affiliate tracking is active. If you arrived from a referral link, attribution is stored for {{ app(\App\Services\AppSettingService::class)->getInt(\App\Services\AppSettingService::KEY_COOKIE_LIFETIME_DAYS, 30) }} days.
                </div>
            </div>
        </div>
    </section>

    @if ($relatedProducts->isNotEmpty())
        <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-12">
            <h2 class="text-2xl font-semibold mb-6">Related Products</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                @foreach ($relatedProducts as $item)
                    @include('public.products._card', ['product' => $item])
                @endforeach
            </div>
        </section>
    @endif
</x-public-layout>

