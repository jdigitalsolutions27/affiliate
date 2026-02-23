<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Affiliate;
use App\Models\AffiliateProductRate;
use App\Models\Product;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AffiliateController extends Controller
{
    public function __construct(private readonly AuditLogService $auditLog)
    {
    }

    public function index(): View
    {
        $this->authorize('viewAny', Affiliate::class);

        $affiliates = Affiliate::query()
            ->with('user')
            ->withCount('clicks')
            ->withSum('commissions as commissions_sum_amount', 'amount')
            ->latest()
            ->paginate(20);

        return view('admin.affiliates.index', [
            'affiliates' => $affiliates,
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', Affiliate::class);

        $products = Product::query()->orderBy('name')->get();

        return view('admin.affiliates.create', [
            'products' => $products,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Affiliate::class);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'code' => ['nullable', 'string', 'max:50', 'unique:affiliates,code'],
            'status' => ['required', Rule::in([Affiliate::STATUS_ACTIVE, Affiliate::STATUS_INACTIVE])],
            'default_commission_type' => ['nullable', Rule::in(['percentage', 'fixed'])],
            'default_commission_value' => ['nullable', 'numeric', 'min:0'],
            'rates' => ['nullable', 'array'],
            'rates.*.enabled' => ['nullable', 'boolean'],
            'rates.*.commission_type' => ['nullable', Rule::in(['percentage', 'fixed'])],
            'rates.*.commission_value' => ['nullable', 'numeric', 'min:0'],
        ]);

        $affiliate = DB::transaction(function () use ($validated) {
            $user = User::query()->create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'role' => User::ROLE_AFFILIATE,
            ]);

            $affiliate = Affiliate::query()->create([
                'user_id' => $user->id,
                'code' => $validated['code'] ?? $this->generateCode(),
                'status' => $validated['status'],
                'default_commission_type' => $validated['default_commission_type'] ?? null,
                'default_commission_value' => $validated['default_commission_value'] ?? null,
            ]);

            $this->syncRates($affiliate, $validated['rates'] ?? []);

            return $affiliate;
        });

        $this->auditLog->log($request->user(), 'admin.affiliate.created', [
            'affiliate_id' => $affiliate->id,
        ]);

        return redirect()->route('admin.affiliates.index')->with('status', 'Affiliate created successfully.');
    }

    public function edit(Affiliate $affiliate): View
    {
        $this->authorize('update', $affiliate);

        $affiliate->load('user', 'productRates');
        $products = Product::query()->orderBy('name')->get();

        $ratesByProduct = $affiliate->productRates->keyBy('product_id');

        return view('admin.affiliates.edit', [
            'affiliate' => $affiliate,
            'products' => $products,
            'ratesByProduct' => $ratesByProduct,
        ]);
    }

    public function update(Request $request, Affiliate $affiliate): RedirectResponse
    {
        $this->authorize('update', $affiliate);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($affiliate->user_id)],
            'code' => ['required', 'string', 'max:50', Rule::unique('affiliates', 'code')->ignore($affiliate->id)],
            'status' => ['required', Rule::in([Affiliate::STATUS_ACTIVE, Affiliate::STATUS_INACTIVE])],
            'default_commission_type' => ['nullable', Rule::in(['percentage', 'fixed'])],
            'default_commission_value' => ['nullable', 'numeric', 'min:0'],
            'rates' => ['nullable', 'array'],
            'rates.*.enabled' => ['nullable', 'boolean'],
            'rates.*.commission_type' => ['nullable', Rule::in(['percentage', 'fixed'])],
            'rates.*.commission_value' => ['nullable', 'numeric', 'min:0'],
        ]);

        DB::transaction(function () use ($validated, $affiliate) {
            $affiliate->user->update([
                'name' => $validated['name'],
                'email' => $validated['email'],
            ]);

            $affiliate->update([
                'code' => $validated['code'],
                'status' => $validated['status'],
                'default_commission_type' => $validated['default_commission_type'] ?? null,
                'default_commission_value' => $validated['default_commission_value'] ?? null,
            ]);

            $this->syncRates($affiliate, $validated['rates'] ?? []);
        });

        if ($affiliate->status === Affiliate::STATUS_INACTIVE) {
            DB::table('sessions')->where('user_id', $affiliate->user_id)->delete();
        }

        $this->auditLog->log($request->user(), 'admin.affiliate.updated', [
            'affiliate_id' => $affiliate->id,
        ]);

        return redirect()->route('admin.affiliates.index')->with('status', 'Affiliate updated successfully.');
    }

    public function destroy(Request $request, Affiliate $affiliate): RedirectResponse
    {
        $this->authorize('delete', $affiliate);

        $affiliateId = $affiliate->id;

        DB::transaction(function () use ($affiliate) {
            $user = $affiliate->user;
            $affiliate->delete();
            $user?->delete();
        });

        $this->auditLog->log($request->user(), 'admin.affiliate.deleted', [
            'affiliate_id' => $affiliateId,
        ]);

        return redirect()->route('admin.affiliates.index')->with('status', 'Affiliate deleted.');
    }

    public function toggleStatus(Request $request, Affiliate $affiliate): RedirectResponse
    {
        $this->authorize('update', $affiliate);

        $validated = $request->validate([
            'status' => ['required', Rule::in([Affiliate::STATUS_ACTIVE, Affiliate::STATUS_INACTIVE])],
        ]);

        $affiliate->update(['status' => $validated['status']]);

        if ($validated['status'] === Affiliate::STATUS_INACTIVE) {
            DB::table('sessions')->where('user_id', $affiliate->user_id)->delete();
        }

        $this->auditLog->log($request->user(), 'admin.affiliate.status_changed', [
            'affiliate_id' => $affiliate->id,
            'status' => $validated['status'],
        ]);

        return back()->with('status', 'Affiliate status updated.');
    }

    public function resetPassword(Request $request, Affiliate $affiliate): RedirectResponse
    {
        $this->authorize('update', $affiliate);

        $newPassword = Str::random(12);

        $affiliate->user->update([
            'password' => Hash::make($newPassword),
            'remember_token' => null,
        ]);

        DB::table('sessions')->where('user_id', $affiliate->user_id)->delete();

        $this->auditLog->log($request->user(), 'admin.affiliate.password_reset', [
            'affiliate_id' => $affiliate->id,
        ]);

        return back()->with('status', "Password reset. New password: {$newPassword}");
    }

    public function revokeTokens(Request $request, Affiliate $affiliate): RedirectResponse
    {
        $this->authorize('update', $affiliate);

        $affiliate->user->update(['remember_token' => null]);
        DB::table('sessions')->where('user_id', $affiliate->user_id)->delete();

        $this->auditLog->log($request->user(), 'admin.affiliate.sessions_revoked', [
            'affiliate_id' => $affiliate->id,
        ]);

        return back()->with('status', 'Affiliate sessions revoked.');
    }

    private function syncRates(Affiliate $affiliate, array $rates): void
    {
        $productIds = Product::query()->pluck('id')->all();

        foreach ($productIds as $productId) {
            $rate = $rates[$productId] ?? null;
            $enabled = isset($rate['enabled']) && (int) $rate['enabled'] === 1;

            if (! $enabled || empty($rate['commission_type']) || ! isset($rate['commission_value'])) {
                AffiliateProductRate::query()
                    ->where('affiliate_id', $affiliate->id)
                    ->where('product_id', $productId)
                    ->delete();

                continue;
            }

            AffiliateProductRate::query()->updateOrCreate(
                [
                    'affiliate_id' => $affiliate->id,
                    'product_id' => $productId,
                ],
                [
                    'commission_type' => $rate['commission_type'],
                    'commission_value' => $rate['commission_value'],
                ]
            );
        }
    }

    private function generateCode(): string
    {
        do {
            $code = 'AFF-'.strtoupper(Str::random(6));
        } while (Affiliate::query()->where('code', $code)->exists());

        return $code;
    }
}
