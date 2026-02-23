@php
    $editing = isset($affiliate);
@endphp

<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
    <div>
        <label class="block text-sm font-medium text-slate-700">Name</label>
        <input name="name" value="{{ old('name', $affiliate->user->name ?? '') }}" required class="mt-1 w-full rounded-md border-slate-300">
    </div>
    <div>
        <label class="block text-sm font-medium text-slate-700">Email</label>
        <input type="email" name="email" value="{{ old('email', $affiliate->user->email ?? '') }}" required class="mt-1 w-full rounded-md border-slate-300">
    </div>
    <div>
        <label class="block text-sm font-medium text-slate-700">Affiliate Code</label>
        <input name="code" value="{{ old('code', $affiliate->code ?? '') }}" placeholder="Auto-generated if empty" class="mt-1 w-full rounded-md border-slate-300 font-mono text-sm">
    </div>
    <div>
        <label class="block text-sm font-medium text-slate-700">Status</label>
        <select name="status" class="mt-1 w-full rounded-md border-slate-300">
            <option value="active" @selected(old('status', $affiliate->status ?? 'active') === 'active')>Active</option>
            <option value="inactive" @selected(old('status', $affiliate->status ?? 'active') === 'inactive')>Inactive</option>
        </select>
    </div>

    @if (! $editing)
        <div class="sm:col-span-2">
            <label class="block text-sm font-medium text-slate-700">Initial Password</label>
            <input type="text" name="password" value="{{ old('password') }}" required class="mt-1 w-full rounded-md border-slate-300">
        </div>
    @endif

    <div>
        <label class="block text-sm font-medium text-slate-700">Default Commission Type</label>
        <select name="default_commission_type" class="mt-1 w-full rounded-md border-slate-300">
            <option value="">No Default</option>
            <option value="percentage" @selected(old('default_commission_type', $affiliate->default_commission_type ?? '') === 'percentage')>Percentage</option>
            <option value="fixed" @selected(old('default_commission_type', $affiliate->default_commission_type ?? '') === 'fixed')>Fixed</option>
        </select>
    </div>
    <div>
        <label class="block text-sm font-medium text-slate-700">Default Commission Value</label>
        <input type="number" step="0.01" min="0" name="default_commission_value" value="{{ old('default_commission_value', $affiliate->default_commission_value ?? '') }}" class="mt-1 w-full rounded-md border-slate-300">
    </div>
</div>

<div class="mt-6">
    <h3 class="text-sm font-semibold text-slate-800 mb-2">Per Product Commission Override</h3>
    <div class="overflow-x-auto border border-slate-200 rounded-lg">
        <table class="min-w-full text-sm">
            <thead class="bg-slate-50 text-left text-slate-600">
                <tr>
                    <th class="px-3 py-2">Enable</th>
                    <th class="px-3 py-2">Product</th>
                    <th class="px-3 py-2">Type</th>
                    <th class="px-3 py-2">Value</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($products as $product)
                    @php
                        $rate = $editing ? ($ratesByProduct[$product->id] ?? null) : null;
                        $enabled = old("rates.{$product->id}.enabled", $rate ? 1 : 0);
                    @endphp
                    <tr class="border-t border-slate-100">
                        <td class="px-3 py-2">
                            <input type="checkbox" name="rates[{{ $product->id }}][enabled]" value="1" @checked((int) $enabled === 1)>
                        </td>
                        <td class="px-3 py-2">{{ $product->name }}</td>
                        <td class="px-3 py-2">
                            <select name="rates[{{ $product->id }}][commission_type]" class="rounded-md border-slate-300 text-xs">
                                <option value="">-</option>
                                <option value="percentage" @selected(old("rates.{$product->id}.commission_type", $rate->commission_type ?? '') === 'percentage')>Percentage</option>
                                <option value="fixed" @selected(old("rates.{$product->id}.commission_type", $rate->commission_type ?? '') === 'fixed')>Fixed</option>
                            </select>
                        </td>
                        <td class="px-3 py-2">
                            <input type="number" step="0.01" min="0" name="rates[{{ $product->id }}][commission_value]" value="{{ old("rates.{$product->id}.commission_value", $rate->commission_value ?? '') }}" class="rounded-md border-slate-300 text-xs w-28">
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
