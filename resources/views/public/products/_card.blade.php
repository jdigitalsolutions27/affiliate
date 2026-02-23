@php
    $primaryImage = $product->images->first();
    $imageUrl = null;
    if ($primaryImage) {
        $imageUrl = str_starts_with($primaryImage->image_path, 'http://') || str_starts_with($primaryImage->image_path, 'https://')
            ? $primaryImage->image_path
            : Storage::disk('public')->url($primaryImage->image_path);
    }
@endphp
<article class="bg-white border border-amber-200 rounded-xl shadow-sm overflow-hidden">
    <div class="h-48 bg-amber-100">
        @if ($imageUrl)
            <img src="{{ $imageUrl }}" alt="{{ $primaryImage->alt_text ?: $product->name }}" class="h-full w-full object-cover">
        @else
            <div class="h-full w-full flex items-center justify-center text-amber-700 text-sm">No image</div>
        @endif
    </div>
    <div class="p-5">
        @if ($product->category)
            <a href="{{ route('categories.show', $product->category->slug) }}" class="text-xs font-semibold uppercase tracking-wide text-amber-700">{{ $product->category->name }}</a>
        @endif
        <h3 class="text-lg font-semibold mt-2">{{ $product->name }}</h3>
        <p class="text-sm text-slate-600 mt-2 line-clamp-3">{{ $product->description ?: 'Handmade product from Red Fairy Handmade Organic.' }}</p>
        <div class="mt-4 flex items-center justify-between">
            <span class="text-xl font-bold" style="color: var(--brand-primary);">${{ number_format((float) $product->price, 2) }}</span>
            <a href="{{ route('products.show', $product->slug) }}" class="inline-flex rounded-md px-3 py-2 text-xs font-semibold text-white" style="background-color: var(--brand-primary);">View Product</a>
        </div>
    </div>
</article>

