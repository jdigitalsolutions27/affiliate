<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-800 leading-tight">Record Manual Order</h2>
    </x-slot>

    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 pb-6">
        <form method="POST" action="{{ route('admin.orders.store') }}" class="bg-white border border-slate-200 rounded-lg p-6">
            @csrf
            @include('admin.orders._form', ['products' => $products, 'affiliates' => $affiliates])

            <div class="mt-6 flex gap-3">
                <button class="inline-flex rounded-md bg-slate-800 px-4 py-2 text-sm font-semibold text-white">Create Order</button>
                <a href="{{ route('admin.orders.index') }}" class="inline-flex rounded-md border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700">Cancel</a>
            </div>
        </form>
    </div>
</x-app-layout>
