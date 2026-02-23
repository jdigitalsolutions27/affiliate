<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Affiliate Platform') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-slate-50 text-slate-900">
    <header class="bg-white border-b border-slate-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 flex items-center justify-between">
            <a href="{{ route('home') }}" class="font-bold text-lg">Affiliate Platform</a>
            <div class="space-x-2">
                @auth
                    <a href="{{ route('dashboard') }}" class="inline-flex items-center rounded-md bg-slate-800 px-4 py-2 text-sm font-semibold text-white">Dashboard</a>
                @else
                    <a href="{{ route('login') }}" class="inline-flex items-center rounded-md bg-slate-800 px-4 py-2 text-sm font-semibold text-white">Affiliate Login</a>
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

    <footer class="py-8 text-center text-xs text-slate-500">
        Affiliate Sales & Commission Tracking Platform
    </footer>
</body>
</html>
