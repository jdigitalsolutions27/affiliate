<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-slate-800 leading-tight">Affiliates</h2>
            <a href="{{ route('admin.affiliates.create') }}" class="inline-flex rounded-md bg-slate-800 px-4 py-2 text-sm font-semibold text-white">Add Affiliate</a>
        </div>
    </x-slot>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-6">
        <div class="bg-white border border-slate-200 rounded-lg overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-slate-50 text-left text-slate-600">
                        <tr>
                            <th class="px-4 py-3">Affiliate</th>
                            <th class="px-4 py-3">Code</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="px-4 py-3">Clicks</th>
                            <th class="px-4 py-3">Commissions</th>
                            <th class="px-4 py-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($affiliates as $affiliate)
                            <tr class="border-t border-slate-100 align-top">
                                <td class="px-4 py-3">
                                    <p class="font-semibold text-slate-900">{{ $affiliate->user?->name }}</p>
                                    <p class="text-xs text-slate-500">{{ $affiliate->user?->email }}</p>
                                </td>
                                <td class="px-4 py-3 font-mono text-xs">{{ $affiliate->code }}</td>
                                <td class="px-4 py-3"><x-status-badge :status="$affiliate->status" /></td>
                                <td class="px-4 py-3">{{ $affiliate->clicks_count }}</td>
                                <td class="px-4 py-3">${{ number_format((float) ($affiliate->commissions_sum_amount ?? 0), 2) }}</td>
                                <td class="px-4 py-3 space-y-2">
                                    <div class="flex flex-wrap gap-2">
                                        <a href="{{ route('admin.affiliates.edit', $affiliate) }}" class="inline-flex rounded border border-slate-300 px-3 py-1 text-xs font-semibold text-slate-700">Edit</a>

                                        <form method="POST" action="{{ route('admin.affiliates.reset-password', $affiliate) }}">
                                            @csrf
                                            <button class="inline-flex rounded border border-indigo-300 px-3 py-1 text-xs font-semibold text-indigo-700">Reset Password</button>
                                        </form>

                                        <form method="POST" action="{{ route('admin.affiliates.revoke-sessions', $affiliate) }}">
                                            @csrf
                                            <button class="inline-flex rounded border border-amber-300 px-3 py-1 text-xs font-semibold text-amber-700">Revoke Sessions</button>
                                        </form>
                                    </div>

                                    <div class="flex flex-wrap gap-2">
                                        <form method="POST" action="{{ route('admin.affiliates.status', $affiliate) }}" class="flex items-center gap-2">
                                            @csrf
                                            @method('PATCH')
                                            <select name="status" class="rounded-md border-slate-300 text-xs">
                                                <option value="active" @selected($affiliate->status === 'active')>Active</option>
                                                <option value="inactive" @selected($affiliate->status === 'inactive')>Inactive</option>
                                            </select>
                                            <button class="inline-flex rounded border border-slate-300 px-3 py-1 text-xs font-semibold text-slate-700">Apply</button>
                                        </form>

                                        <form method="POST" action="{{ route('admin.affiliates.destroy', $affiliate) }}" onsubmit="return confirm('Delete this affiliate?')">
                                            @csrf
                                            @method('DELETE')
                                            <button class="inline-flex rounded border border-rose-300 px-3 py-1 text-xs font-semibold text-rose-700">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-8 text-center text-slate-500">No affiliates yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="px-4 py-3 border-t border-slate-100">
                {{ $affiliates->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
