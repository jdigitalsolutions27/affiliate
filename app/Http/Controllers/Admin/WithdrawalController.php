<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Commission;
use App\Models\Withdrawal;
use App\Services\AuditLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class WithdrawalController extends Controller
{
    public function __construct(private readonly AuditLogService $auditLog)
    {
    }

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Withdrawal::class);

        $validated = $request->validate([
            'status' => ['nullable', Rule::in([
                Withdrawal::STATUS_PENDING,
                Withdrawal::STATUS_APPROVED,
                Withdrawal::STATUS_REJECTED,
                Withdrawal::STATUS_PAID,
            ])],
        ]);

        $query = Withdrawal::query()->with('affiliate.user', 'processor');

        if (! empty($validated['status'])) {
            $query->where('status', $validated['status']);
        }

        return view('admin.withdrawals.index', [
            'withdrawals' => $query->latest()->paginate(20)->withQueryString(),
            'currentStatus' => $validated['status'] ?? null,
        ]);
    }

    public function approve(Request $request, Withdrawal $withdrawal): RedirectResponse
    {
        $this->authorize('update', $withdrawal);

        if ($withdrawal->status !== Withdrawal::STATUS_PENDING) {
            return back()->withErrors(['status' => 'Only pending withdrawals can be approved.']);
        }

        $withdrawal->update([
            'status' => Withdrawal::STATUS_APPROVED,
            'admin_note' => $request->input('admin_note'),
            'processed_by' => $request->user()->id,
            'processed_at' => now(),
        ]);

        $this->auditLog->log($request->user(), 'admin.withdrawal.approved', [
            'withdrawal_id' => $withdrawal->id,
        ]);

        return back()->with('status', 'Withdrawal approved.');
    }

    public function reject(Request $request, Withdrawal $withdrawal): RedirectResponse
    {
        $this->authorize('update', $withdrawal);

        if (! in_array($withdrawal->status, [Withdrawal::STATUS_PENDING, Withdrawal::STATUS_APPROVED], true)) {
            return back()->withErrors(['status' => 'This withdrawal cannot be rejected.']);
        }

        $validated = $request->validate([
            'admin_note' => ['required', 'string', 'max:2000'],
        ]);

        $withdrawal->update([
            'status' => Withdrawal::STATUS_REJECTED,
            'admin_note' => $validated['admin_note'],
            'processed_by' => $request->user()->id,
            'processed_at' => now(),
        ]);

        $this->auditLog->log($request->user(), 'admin.withdrawal.rejected', [
            'withdrawal_id' => $withdrawal->id,
        ]);

        return back()->with('status', 'Withdrawal rejected.');
    }

    public function markPaid(Request $request, Withdrawal $withdrawal): RedirectResponse
    {
        $this->authorize('update', $withdrawal);

        if (! in_array($withdrawal->status, [Withdrawal::STATUS_APPROVED, Withdrawal::STATUS_PENDING], true)) {
            return back()->withErrors(['status' => 'Only pending/approved withdrawals can be marked as paid.']);
        }

        $validated = $request->validate([
            'paid_reference' => ['required', 'string', 'max:255'],
            'admin_note' => ['nullable', 'string', 'max:2000'],
        ]);

        DB::transaction(function () use ($request, $withdrawal, $validated) {
            $withdrawal->update([
                'status' => Withdrawal::STATUS_PAID,
                'paid_reference' => $validated['paid_reference'],
                'admin_note' => $validated['admin_note'] ?? null,
                'processed_by' => $request->user()->id,
                'processed_at' => now(),
            ]);

            $remaining = (float) $withdrawal->amount;

            $commissions = Commission::query()
                ->where('affiliate_id', $withdrawal->affiliate_id)
                ->where('status', Commission::STATUS_APPROVED)
                ->orderBy('created_at')
                ->get();

            foreach ($commissions as $commission) {
                $amount = (float) $commission->amount;
                if ($remaining < $amount) {
                    continue;
                }

                $commission->update([
                    'status' => Commission::STATUS_PAID,
                ]);

                $remaining -= $amount;
                if ($remaining <= 0) {
                    break;
                }
            }
        });

        $this->auditLog->log($request->user(), 'admin.withdrawal.paid', [
            'withdrawal_id' => $withdrawal->id,
        ]);

        return back()->with('status', 'Withdrawal marked as paid.');
    }
}
