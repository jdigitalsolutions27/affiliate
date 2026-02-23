<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\AppSettingService;
use App\Services\AuditLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SettingController extends Controller
{
    public function __construct(
        private readonly AppSettingService $settings,
        private readonly AuditLogService $auditLog
    ) {
    }

    public function edit(): View
    {
        return view('admin.settings.edit', [
            'settings' => $this->settings->all(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            AppSettingService::KEY_GLOBAL_COMMISSION_TYPE => ['required', Rule::in(['percentage', 'fixed'])],
            AppSettingService::KEY_GLOBAL_COMMISSION_VALUE => ['required', 'numeric', 'min:0'],
            AppSettingService::KEY_COOKIE_LIFETIME_DAYS => ['required', 'integer', 'min:1', 'max:365'],
            AppSettingService::KEY_COMMISSION_TRIGGER_STATUS => ['required', Rule::in([
                Order::STATUS_CONFIRMED,
                Order::STATUS_COMPLETED,
            ])],
            AppSettingService::KEY_MINIMUM_PAYOUT => ['required', 'numeric', 'min:0'],
            AppSettingService::KEY_PAYOUT_METHODS_LABEL => ['required', 'string', 'max:500'],
        ]);

        $this->settings->setMany($validated);

        $this->auditLog->log($request->user(), 'admin.settings.updated', [
            'keys' => array_keys($validated),
        ]);

        return back()->with('status', 'Settings updated.');
    }
}
