<?php

namespace App\Http\Controllers\Affiliate;

use App\Http\Controllers\Controller;
use App\Models\Withdrawal;
use App\Services\AffiliateBalanceService;
use App\Services\AppSettingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class WithdrawalController extends Controller
{
    public function __construct(
        private readonly AffiliateBalanceService $balanceService,
        private readonly AppSettingService $settings
    ) {
    }

    public function index(Request $request): View
    {
        $affiliate = $request->user()->affiliate;
        abort_if(! $affiliate, 403);

        $this->authorize('viewAny', Withdrawal::class);

        return view('affiliate.withdrawals.index', [
            'summary' => $this->balanceService->summary($affiliate),
            'minimumPayout' => $this->settings->getFloat(AppSettingService::KEY_MINIMUM_PAYOUT, 100),
            'payoutMethodsLabel' => $this->settings->get(AppSettingService::KEY_PAYOUT_METHODS_LABEL, 'GCash, Bank, PayPal'),
            'withdrawals' => $affiliate->withdrawals()->latest()->paginate(15),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $affiliate = $request->user()->affiliate;
        abort_if(! $affiliate, 403);

        $this->authorize('create', Withdrawal::class);

        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:0.01'],
            'method_text' => ['required', 'string', 'max:255'],
            'account_text' => ['required', 'string', 'max:2000'],
        ]);

        $summary = $this->balanceService->summary($affiliate);
        $minimumPayout = $this->settings->getFloat(AppSettingService::KEY_MINIMUM_PAYOUT, 100);

        if ((float) $validated['amount'] < $minimumPayout) {
            return back()->withErrors([
                'amount' => "Amount must be at least {$minimumPayout}.",
            ])->withInput();
        }

        if ((float) $validated['amount'] > $summary['available_balance']) {
            return back()->withErrors([
                'amount' => 'Amount exceeds available balance.',
            ])->withInput();
        }

        DB::transaction(function () use ($validated, $affiliate) {
            Withdrawal::query()->create([
                'affiliate_id' => $affiliate->id,
                'amount' => $validated['amount'],
                'method_text' => $validated['method_text'],
                'account_text' => $validated['account_text'],
                'status' => Withdrawal::STATUS_PENDING,
            ]);
        });

        return back()->with('status', 'Withdrawal request submitted.');
    }
}
