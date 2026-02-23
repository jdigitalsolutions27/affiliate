<?php

namespace App\Services;

use App\Models\AppSetting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

class AppSettingService
{
    public const KEY_GLOBAL_COMMISSION_TYPE = 'global_commission_type';
    public const KEY_GLOBAL_COMMISSION_VALUE = 'global_commission_value';
    public const KEY_COOKIE_LIFETIME_DAYS = 'cookie_lifetime_days';
    public const KEY_COMMISSION_TRIGGER_STATUS = 'commission_trigger_status';
    public const KEY_MINIMUM_PAYOUT = 'minimum_payout';
    public const KEY_PAYOUT_METHODS_LABEL = 'payout_methods_label';

    public function defaults(): array
    {
        return [
            self::KEY_GLOBAL_COMMISSION_TYPE => 'percentage',
            self::KEY_GLOBAL_COMMISSION_VALUE => '10',
            self::KEY_COOKIE_LIFETIME_DAYS => '30',
            self::KEY_COMMISSION_TRIGGER_STATUS => 'confirmed',
            self::KEY_MINIMUM_PAYOUT => '100',
            self::KEY_PAYOUT_METHODS_LABEL => 'GCash, Bank, PayPal',
        ];
    }

    public function all(): array
    {
        $defaults = $this->defaults();
        if (! Schema::hasTable('app_settings')) {
            return $defaults;
        }

        $stored = Cache::remember('app_settings.all', 300, function () {
            return AppSetting::query()->pluck('value', 'key')->toArray();
        });

        return array_merge($defaults, $stored);
    }

    public function get(string $key, ?string $default = null): ?string
    {
        $all = $this->all();

        return $all[$key] ?? $default;
    }

    public function getInt(string $key, int $default): int
    {
        return (int) ($this->get($key, (string) $default) ?? $default);
    }

    public function getFloat(string $key, float $default): float
    {
        return (float) ($this->get($key, (string) $default) ?? $default);
    }

    public function setMany(array $settings): void
    {
        foreach ($settings as $key => $value) {
            AppSetting::query()->updateOrCreate(
                ['key' => $key],
                ['value' => (string) $value]
            );
        }

        Cache::forget('app_settings.all');
    }
}

