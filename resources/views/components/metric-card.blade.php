@props(['label', 'value'])

<div {{ $attributes->merge(['class' => 'bg-white border border-slate-200 rounded-lg p-4']) }}>
    <p class="text-sm text-slate-500">{{ $label }}</p>
    <p class="mt-2 text-2xl font-semibold text-slate-900">{{ $value }}</p>
</div>
