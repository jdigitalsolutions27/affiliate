@props(['status'])

@php
    $map = [
        'active' => 'bg-emerald-100 text-emerald-800',
        'inactive' => 'bg-slate-100 text-slate-700',
        'pending' => 'bg-amber-100 text-amber-800',
        'confirmed' => 'bg-blue-100 text-blue-800',
        'completed' => 'bg-emerald-100 text-emerald-800',
        'cancelled' => 'bg-rose-100 text-rose-800',
        'refunded' => 'bg-rose-100 text-rose-800',
        'approved' => 'bg-indigo-100 text-indigo-800',
        'paid' => 'bg-teal-100 text-teal-800',
        'rejected' => 'bg-rose-100 text-rose-800',
        'reversed' => 'bg-rose-100 text-rose-800',
    ];

    $class = $map[strtolower((string) $status)] ?? 'bg-slate-100 text-slate-700';
@endphp

<span {{ $attributes->merge(['class' => "inline-flex rounded-full px-2.5 py-1 text-xs font-semibold {$class}"]) }}>
    {{ ucfirst((string) $status) }}
</span>
