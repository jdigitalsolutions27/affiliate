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
    public const KEY_PUBLIC_ORDER_MODE = 'public_order_mode';
    public const KEY_BRAND_NAME = 'brand_name';
    public const KEY_BRAND_LOGO_PRIMARY = 'brand_logo_primary';
    public const KEY_BRAND_LOGO_ICON = 'brand_logo_icon';
    public const KEY_BRAND_PRIMARY_COLOR = 'brand_primary_color';
    public const KEY_BRAND_SECONDARY_COLOR = 'brand_secondary_color';
    public const KEY_BRAND_ACCENT_COLOR = 'brand_accent_color';
    public const KEY_BRAND_FONT_FAMILY = 'brand_font_family';
    public const KEY_BRAND_HERO_TITLE = 'brand_hero_title';
    public const KEY_BRAND_HERO_SUBTITLE = 'brand_hero_subtitle';
    public const KEY_BRAND_ABOUT_TEXT = 'brand_about_text';
    public const KEY_BRAND_CONTACT_EMAIL = 'brand_contact_email';
    public const KEY_BRAND_CONTACT_PHONE = 'brand_contact_phone';
    public const KEY_BRAND_CONTACT_ADDRESS = 'brand_contact_address';
    public const KEY_BRAND_FACEBOOK_URL = 'brand_facebook_url';
    public const KEY_BRAND_INSTAGRAM_URL = 'brand_instagram_url';
    public const KEY_META_PAGE_ID = 'meta_page_id';
    public const KEY_META_CATALOG_ID = 'meta_catalog_id';
    public const KEY_META_ACCESS_TOKEN = 'meta_access_token';

    public function defaults(): array
    {
        return [
            self::KEY_GLOBAL_COMMISSION_TYPE => 'percentage',
            self::KEY_GLOBAL_COMMISSION_VALUE => '10',
            self::KEY_COOKIE_LIFETIME_DAYS => '30',
            self::KEY_COMMISSION_TRIGGER_STATUS => 'confirmed',
            self::KEY_MINIMUM_PAYOUT => '100',
            self::KEY_PAYOUT_METHODS_LABEL => 'GCash, Bank, PayPal',
            self::KEY_PUBLIC_ORDER_MODE => 'order_request',
            self::KEY_BRAND_NAME => 'Red Fairy Handmade Organic',
            self::KEY_BRAND_LOGO_PRIMARY => '',
            self::KEY_BRAND_LOGO_ICON => '',
            self::KEY_BRAND_PRIMARY_COLOR => '#B45309',
            self::KEY_BRAND_SECONDARY_COLOR => '#92400E',
            self::KEY_BRAND_ACCENT_COLOR => '#166534',
            self::KEY_BRAND_FONT_FAMILY => 'Lora',
            self::KEY_BRAND_HERO_TITLE => 'Handmade organic care for your skin and home.',
            self::KEY_BRAND_HERO_SUBTITLE => 'Crafted in small batches with gentle, natural ingredients inspired by Red Fairy Handmade Organic.',
            self::KEY_BRAND_ABOUT_TEXT => 'Red Fairy Handmade Organic focuses on thoughtful ingredients, artisanal production, and customer-first support.',
            self::KEY_BRAND_CONTACT_EMAIL => 'hello@example.com',
            self::KEY_BRAND_CONTACT_PHONE => '+63 900 000 0000',
            self::KEY_BRAND_CONTACT_ADDRESS => 'Your studio or business address',
            self::KEY_BRAND_FACEBOOK_URL => 'https://www.facebook.com/RedFairyHandmadeOrganic',
            self::KEY_BRAND_INSTAGRAM_URL => '',
            self::KEY_META_PAGE_ID => '',
            self::KEY_META_CATALOG_ID => '',
            self::KEY_META_ACCESS_TOKEN => '',
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

    public function getBool(string $key, bool $default = false): bool
    {
        $value = strtolower(trim((string) ($this->get($key, $default ? '1' : '0') ?? '0')));

        return in_array($value, ['1', 'true', 'yes', 'on'], true);
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

    public function brandSettings(): array
    {
        $settings = $this->all();

        return [
            'name' => $settings[self::KEY_BRAND_NAME] ?? 'Red Fairy Handmade Organic',
            'logo_primary' => $settings[self::KEY_BRAND_LOGO_PRIMARY] ?? '',
            'logo_icon' => $settings[self::KEY_BRAND_LOGO_ICON] ?? '',
            'primary_color' => $settings[self::KEY_BRAND_PRIMARY_COLOR] ?? '#B45309',
            'secondary_color' => $settings[self::KEY_BRAND_SECONDARY_COLOR] ?? '#92400E',
            'accent_color' => $settings[self::KEY_BRAND_ACCENT_COLOR] ?? '#166534',
            'font_family' => $settings[self::KEY_BRAND_FONT_FAMILY] ?? 'Lora',
            'hero_title' => $settings[self::KEY_BRAND_HERO_TITLE] ?? '',
            'hero_subtitle' => $settings[self::KEY_BRAND_HERO_SUBTITLE] ?? '',
            'about_text' => $settings[self::KEY_BRAND_ABOUT_TEXT] ?? '',
            'contact_email' => $settings[self::KEY_BRAND_CONTACT_EMAIL] ?? '',
            'contact_phone' => $settings[self::KEY_BRAND_CONTACT_PHONE] ?? '',
            'contact_address' => $settings[self::KEY_BRAND_CONTACT_ADDRESS] ?? '',
            'facebook_url' => $settings[self::KEY_BRAND_FACEBOOK_URL] ?? '',
            'instagram_url' => $settings[self::KEY_BRAND_INSTAGRAM_URL] ?? '',
        ];
    }
}
