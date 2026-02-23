<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-800 leading-tight">Platform Settings</h2>
    </x-slot>

    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 pb-6">
        <form method="POST" action="{{ route('admin.settings.update') }}" class="bg-white border border-slate-200 rounded-lg p-6 space-y-5">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700">Global Commission Type</label>
                    <select name="global_commission_type" class="mt-1 w-full rounded-md border-slate-300">
                        <option value="percentage" @selected(($settings['global_commission_type'] ?? 'percentage') === 'percentage')>Percentage</option>
                        <option value="fixed" @selected(($settings['global_commission_type'] ?? 'percentage') === 'fixed')>Fixed</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">Global Commission Value</label>
                    <input type="number" step="0.01" min="0" name="global_commission_value" value="{{ $settings['global_commission_value'] ?? '10' }}" class="mt-1 w-full rounded-md border-slate-300">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">Referral Cookie Lifetime (days)</label>
                    <input type="number" min="1" max="365" name="cookie_lifetime_days" value="{{ $settings['cookie_lifetime_days'] ?? '30' }}" class="mt-1 w-full rounded-md border-slate-300">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">Commission Trigger Status</label>
                    <select name="commission_trigger_status" class="mt-1 w-full rounded-md border-slate-300">
                        <option value="confirmed" @selected(($settings['commission_trigger_status'] ?? 'confirmed') === 'confirmed')>Confirmed</option>
                        <option value="completed" @selected(($settings['commission_trigger_status'] ?? 'confirmed') === 'completed')>Completed</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">Minimum Payout</label>
                    <input type="number" step="0.01" min="0" name="minimum_payout" value="{{ $settings['minimum_payout'] ?? '100' }}" class="mt-1 w-full rounded-md border-slate-300">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">Payout Methods Label</label>
                    <input name="payout_methods_label" value="{{ $settings['payout_methods_label'] ?? '' }}" class="mt-1 w-full rounded-md border-slate-300" placeholder="GCash, Bank, PayPal">
                </div>
            </div>

            <div>
                <button class="inline-flex rounded-md bg-slate-800 px-4 py-2 text-sm font-semibold text-white">Save Settings</button>
            </div>
        </form>
    </div>
</x-app-layout>
