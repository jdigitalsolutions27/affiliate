<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-800 leading-tight">Platform & Brand Settings</h2>
    </x-slot>

    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 pb-6 space-y-6">
        @error('meta')
            <div class="rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                {{ $message }}
            </div>
        @enderror

        <form method="POST" action="{{ route('admin.settings.update') }}" enctype="multipart/form-data" class="bg-white border border-slate-200 rounded-lg p-6 space-y-8">
            @csrf
            @method('PUT')

            <section class="space-y-4">
                <h3 class="text-lg font-semibold text-slate-900">Commission & Payout Rules</h3>
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
            </section>

            <section class="space-y-4 pt-2 border-t border-slate-100">
                <h3 class="text-lg font-semibold text-slate-900">Order / Inquiry Flow</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Public Order Mode</label>
                        <select name="public_order_mode" class="mt-1 w-full rounded-md border-slate-300">
                            <option value="order_request" @selected(($settings['public_order_mode'] ?? 'order_request') === 'order_request')>Order Request (recommended)</option>
                            <option value="checkout_lite" @selected(($settings['public_order_mode'] ?? 'order_request') === 'checkout_lite')>Checkout-lite</option>
                        </select>
                    </div>
                </div>
            </section>

            <section class="space-y-4 pt-2 border-t border-slate-100">
                <h3 class="text-lg font-semibold text-slate-900">Brand Settings</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="sm:col-span-2">
                        <label class="block text-sm font-medium text-slate-700">Brand Name</label>
                        <input name="brand_name" value="{{ $settings['brand_name'] ?? 'Red Fairy Handmade Organic' }}" class="mt-1 w-full rounded-md border-slate-300">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Primary Color</label>
                        <input type="color" name="brand_primary_color" value="{{ $settings['brand_primary_color'] ?? '#B45309' }}" class="mt-1 h-10 w-full rounded-md border-slate-300">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Secondary Color</label>
                        <input type="color" name="brand_secondary_color" value="{{ $settings['brand_secondary_color'] ?? '#92400E' }}" class="mt-1 h-10 w-full rounded-md border-slate-300">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Accent Color</label>
                        <input type="color" name="brand_accent_color" value="{{ $settings['brand_accent_color'] ?? '#166534' }}" class="mt-1 h-10 w-full rounded-md border-slate-300">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Typography</label>
                        <select name="brand_font_family" class="mt-1 w-full rounded-md border-slate-300">
                            @foreach ($fontOptions as $fontValue => $fontLabel)
                                <option value="{{ $fontValue }}" @selected(($settings['brand_font_family'] ?? 'Lora') === $fontValue)>{{ $fontLabel }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Logo (Primary)</label>
                        <input type="file" name="brand_logo_primary_upload" accept="image/*" class="mt-1 w-full text-sm">
                        @if (! empty($settings['brand_logo_primary']))
                            <img src="{{ str_starts_with($settings['brand_logo_primary'], 'http://') || str_starts_with($settings['brand_logo_primary'], 'https://') ? $settings['brand_logo_primary'] : Storage::disk('public')->url($settings['brand_logo_primary']) }}" alt="Brand Logo" class="mt-2 h-12 rounded object-cover border border-slate-200">
                        @endif
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Logo (Icon)</label>
                        <input type="file" name="brand_logo_icon_upload" accept="image/*" class="mt-1 w-full text-sm">
                        @if (! empty($settings['brand_logo_icon']))
                            <img src="{{ str_starts_with($settings['brand_logo_icon'], 'http://') || str_starts_with($settings['brand_logo_icon'], 'https://') ? $settings['brand_logo_icon'] : Storage::disk('public')->url($settings['brand_logo_icon']) }}" alt="Brand Icon" class="mt-2 h-12 w-12 rounded-full object-cover border border-slate-200">
                        @endif
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-sm font-medium text-slate-700">Hero Title</label>
                        <input name="brand_hero_title" value="{{ $settings['brand_hero_title'] ?? '' }}" class="mt-1 w-full rounded-md border-slate-300">
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-sm font-medium text-slate-700">Hero Subtitle</label>
                        <textarea name="brand_hero_subtitle" rows="3" class="mt-1 w-full rounded-md border-slate-300">{{ $settings['brand_hero_subtitle'] ?? '' }}</textarea>
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-sm font-medium text-slate-700">About Text</label>
                        <textarea name="brand_about_text" rows="4" class="mt-1 w-full rounded-md border-slate-300">{{ $settings['brand_about_text'] ?? '' }}</textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Contact Email</label>
                        <input name="brand_contact_email" type="email" value="{{ $settings['brand_contact_email'] ?? '' }}" class="mt-1 w-full rounded-md border-slate-300">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Contact Phone</label>
                        <input name="brand_contact_phone" value="{{ $settings['brand_contact_phone'] ?? '' }}" class="mt-1 w-full rounded-md border-slate-300">
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-sm font-medium text-slate-700">Contact Address</label>
                        <input name="brand_contact_address" value="{{ $settings['brand_contact_address'] ?? '' }}" class="mt-1 w-full rounded-md border-slate-300">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Facebook URL</label>
                        <input name="brand_facebook_url" value="{{ $settings['brand_facebook_url'] ?? '' }}" class="mt-1 w-full rounded-md border-slate-300">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Instagram URL</label>
                        <input name="brand_instagram_url" value="{{ $settings['brand_instagram_url'] ?? '' }}" class="mt-1 w-full rounded-md border-slate-300">
                    </div>
                </div>
            </section>

            <section class="space-y-4 pt-2 border-t border-slate-100">
                <h3 class="text-lg font-semibold text-slate-900">Meta Graph API Product Sync (Optional)</h3>
                <p class="text-sm text-slate-500">This module is optional and OFF by default. If not configured, use CSV import from Products page.</p>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Meta Page ID</label>
                        <input name="meta_page_id" value="{{ $settings['meta_page_id'] ?? '' }}" class="mt-1 w-full rounded-md border-slate-300">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Meta Catalog ID</label>
                        <input name="meta_catalog_id" value="{{ $settings['meta_catalog_id'] ?? '' }}" class="mt-1 w-full rounded-md border-slate-300">
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-sm font-medium text-slate-700">Meta Access Token</label>
                        <textarea name="meta_access_token" rows="3" class="mt-1 w-full rounded-md border-slate-300">{{ $settings['meta_access_token'] ?? '' }}</textarea>
                    </div>
                </div>
            </section>

            <div class="flex flex-wrap gap-3">
                <button class="inline-flex rounded-md bg-slate-800 px-4 py-2 text-sm font-semibold text-white">Save Settings</button>
            </div>
        </form>

        <div class="bg-white border border-slate-200 rounded-lg p-4 flex flex-wrap gap-3">
            <form method="POST" action="{{ route('admin.settings.meta.test') }}">
                @csrf
                <button class="inline-flex rounded-md border border-indigo-300 px-4 py-2 text-sm font-semibold text-indigo-700">
                    Test Meta Connection
                </button>
            </form>
            <form method="POST" action="{{ route('admin.settings.meta.sync') }}">
                @csrf
                <button class="inline-flex rounded-md border border-emerald-300 px-4 py-2 text-sm font-semibold text-emerald-700">
                    Sync Products Now
                </button>
            </form>
        </div>
    </div>
</x-app-layout>
