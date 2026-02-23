@php
    $settingService = app(\App\Services\AppSettingService::class);
    $brand = $settingService->brandSettings();

    $fontName = $brand['font_family'] ?: 'Lora';
    $fontQuery = str_replace(' ', '+', $fontName);

    $logoPrimary = $brand['logo_primary'] ?? '';
    $logoIcon = $brand['logo_icon'] ?? '';

    $logoPrimaryUrl = '';
    if ($logoPrimary) {
        $logoPrimaryUrl = str_starts_with($logoPrimary, 'http://') || str_starts_with($logoPrimary, 'https://')
            ? $logoPrimary
            : Storage::disk('public')->url($logoPrimary);
    }

    $logoIconUrl = '';
    if ($logoIcon) {
        $logoIconUrl = str_starts_with($logoIcon, 'http://') || str_starts_with($logoIcon, 'https://')
            ? $logoIcon
            : Storage::disk('public')->url($logoIcon);
    }
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $brand['name'] ?: config('app.name', 'Red Fairy Handmade Organic') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family={{ $fontQuery }}:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        :root {
            --brand-primary: {{ $brand['primary_color'] }};
            --brand-secondary: {{ $brand['secondary_color'] }};
            --brand-accent: {{ $brand['accent_color'] }};
            --brand-font: "{{ $fontName }}", serif;
        }
    </style>
</head>
<body class="antialiased bg-amber-50/40 text-slate-900" style="font-family: var(--brand-font);">
    <header class="sticky top-0 z-20 bg-white/95 backdrop-blur border-b border-amber-200/60">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-3 flex items-center justify-between gap-3">
            <a href="{{ route('home') }}" class="flex items-center gap-3 min-w-0">
                @if ($logoIconUrl)
                    <img src="{{ $logoIconUrl }}" alt="{{ $brand['name'] }}" class="h-10 w-10 rounded-full object-cover border border-amber-200">
                @else
                    <span class="inline-flex h-10 w-10 items-center justify-center rounded-full text-white text-sm font-bold" style="background: linear-gradient(135deg, var(--brand-primary), var(--brand-secondary));">
                        RF
                    </span>
                @endif
                <span class="truncate font-semibold text-base sm:text-lg">{{ $brand['name'] }}</span>
            </a>
            <div class="flex items-center gap-2 sm:gap-3">
                <a href="{{ route('products.index') }}" class="hidden sm:inline-flex rounded-md border border-amber-300 px-4 py-2 text-sm font-semibold text-amber-900 hover:bg-amber-100">Products</a>
                @auth
                    <a href="{{ route('dashboard') }}" class="inline-flex rounded-md px-4 py-2 text-sm font-semibold text-white" style="background-color: var(--brand-primary);">Dashboard</a>
                @else
                    <a href="{{ route('login') }}" class="inline-flex rounded-md px-4 py-2 text-sm font-semibold text-white" style="background-color: var(--brand-primary);">Affiliate Login</a>
                @endauth
            </div>
        </div>
    </header>

    @if (session('status'))
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4">
            <div class="rounded-lg bg-emerald-50 border border-emerald-200 px-4 py-3 text-sm text-emerald-700">
                {{ session('status') }}
            </div>
        </div>
    @endif

    @if ($errors->any())
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4">
            <div class="rounded-lg bg-rose-50 border border-rose-200 px-4 py-3 text-sm text-rose-700">
                <ul class="list-disc pl-5 space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    <main>
        {{ $slot }}
    </main>

    <footer class="mt-12 border-t border-amber-200 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 grid grid-cols-1 sm:grid-cols-3 gap-6 text-sm">
            <div>
                <h3 class="font-semibold text-base mb-2">{{ $brand['name'] }}</h3>
                <p class="text-slate-600">{{ $brand['about_text'] }}</p>
            </div>
            <div>
                <h3 class="font-semibold text-base mb-2">Contact</h3>
                <p class="text-slate-600">{{ $brand['contact_email'] ?: 'N/A' }}</p>
                <p class="text-slate-600">{{ $brand['contact_phone'] ?: 'N/A' }}</p>
                <p class="text-slate-600">{{ $brand['contact_address'] ?: 'N/A' }}</p>
            </div>
            <div>
                <h3 class="font-semibold text-base mb-2">Social</h3>
                @if (! empty($brand['facebook_url']))
                    <a href="{{ $brand['facebook_url'] }}" target="_blank" rel="noopener" class="block text-slate-600 hover:underline">Facebook</a>
                @endif
                @if (! empty($brand['instagram_url']))
                    <a href="{{ $brand['instagram_url'] }}" target="_blank" rel="noopener" class="block text-slate-600 hover:underline">Instagram</a>
                @endif
                <a href="{{ route('products.index') }}" class="block text-slate-600 hover:underline">All Products</a>
            </div>
        </div>
    </footer>
</body>
</html>

