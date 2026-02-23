<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductImage;
use App\Services\AppSettingService;
use App\Services\AuditLogService;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
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
            'fontOptions' => $this->fontOptions(),
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
            AppSettingService::KEY_PUBLIC_ORDER_MODE => ['required', Rule::in([
                Order::FLOW_ORDER_REQUEST,
                Order::FLOW_CHECKOUT_LITE,
            ])],
            AppSettingService::KEY_BRAND_NAME => ['required', 'string', 'max:120'],
            AppSettingService::KEY_BRAND_PRIMARY_COLOR => ['required', 'regex:/^#([0-9a-fA-F]{6})$/'],
            AppSettingService::KEY_BRAND_SECONDARY_COLOR => ['required', 'regex:/^#([0-9a-fA-F]{6})$/'],
            AppSettingService::KEY_BRAND_ACCENT_COLOR => ['required', 'regex:/^#([0-9a-fA-F]{6})$/'],
            AppSettingService::KEY_BRAND_FONT_FAMILY => ['required', Rule::in(array_keys($this->fontOptions()))],
            AppSettingService::KEY_BRAND_HERO_TITLE => ['required', 'string', 'max:220'],
            AppSettingService::KEY_BRAND_HERO_SUBTITLE => ['required', 'string', 'max:600'],
            AppSettingService::KEY_BRAND_ABOUT_TEXT => ['required', 'string', 'max:3000'],
            AppSettingService::KEY_BRAND_CONTACT_EMAIL => ['nullable', 'email', 'max:255'],
            AppSettingService::KEY_BRAND_CONTACT_PHONE => ['nullable', 'string', 'max:100'],
            AppSettingService::KEY_BRAND_CONTACT_ADDRESS => ['nullable', 'string', 'max:500'],
            AppSettingService::KEY_BRAND_FACEBOOK_URL => ['nullable', 'url', 'max:255'],
            AppSettingService::KEY_BRAND_INSTAGRAM_URL => ['nullable', 'url', 'max:255'],
            AppSettingService::KEY_META_PAGE_ID => ['nullable', 'string', 'max:120'],
            AppSettingService::KEY_META_CATALOG_ID => ['nullable', 'string', 'max:120'],
            AppSettingService::KEY_META_ACCESS_TOKEN => ['nullable', 'string', 'max:2000'],
            'brand_logo_primary_upload' => ['nullable', 'image', 'max:3072'],
            'brand_logo_icon_upload' => ['nullable', 'image', 'max:2048'],
        ]);

        $existing = $this->settings->all();
        $updates = $validated;

        unset($updates['brand_logo_primary_upload'], $updates['brand_logo_icon_upload']);

        if ($request->hasFile('brand_logo_primary_upload')) {
            $updates[AppSettingService::KEY_BRAND_LOGO_PRIMARY] = $request->file('brand_logo_primary_upload')->store('brand', 'public');
            $this->deleteReplacedAsset($existing[AppSettingService::KEY_BRAND_LOGO_PRIMARY] ?? null);
        }

        if ($request->hasFile('brand_logo_icon_upload')) {
            $updates[AppSettingService::KEY_BRAND_LOGO_ICON] = $request->file('brand_logo_icon_upload')->store('brand', 'public');
            $this->deleteReplacedAsset($existing[AppSettingService::KEY_BRAND_LOGO_ICON] ?? null);
        }

        if (empty($updates[AppSettingService::KEY_BRAND_FACEBOOK_URL])) {
            $updates[AppSettingService::KEY_BRAND_FACEBOOK_URL] = '';
        }
        if (empty($updates[AppSettingService::KEY_BRAND_INSTAGRAM_URL])) {
            $updates[AppSettingService::KEY_BRAND_INSTAGRAM_URL] = '';
        }

        $this->settings->setMany($updates);

        $this->auditLog->log($request->user(), 'admin.settings.updated', [
            'keys' => array_keys($updates),
        ]);

        return back()->with('status', 'Settings updated.');
    }

    public function testMetaConnection(Request $request): RedirectResponse
    {
        $settings = $this->settings->all();
        $pageId = trim((string) ($settings[AppSettingService::KEY_META_PAGE_ID] ?? ''));
        $token = trim((string) ($settings[AppSettingService::KEY_META_ACCESS_TOKEN] ?? ''));

        if ($pageId === '' || $token === '') {
            return back()->withErrors([
                'meta' => 'Set Meta Page ID and Access Token first, then save settings.',
            ]);
        }

        try {
            $response = Http::timeout(12)
                ->retry(1, 200)
                ->get("https://graph.facebook.com/v20.0/{$pageId}", [
                    'fields' => 'id,name',
                    'access_token' => $token,
                ])
                ->throw()
                ->json();
        } catch (RequestException $exception) {
            return back()->withErrors([
                'meta' => 'Meta connection failed: '.$this->metaErrorMessage($exception),
            ]);
        }

        $this->auditLog->log($request->user(), 'admin.meta.connection_tested', [
            'page_id' => $pageId,
        ]);

        return back()->with('status', 'Meta connection successful for page: '.($response['name'] ?? $response['id'] ?? $pageId));
    }

    public function syncMetaProducts(Request $request): RedirectResponse
    {
        $settings = $this->settings->all();
        $catalogId = trim((string) ($settings[AppSettingService::KEY_META_CATALOG_ID] ?? ''));
        $token = trim((string) ($settings[AppSettingService::KEY_META_ACCESS_TOKEN] ?? ''));

        if ($catalogId === '' || $token === '') {
            return back()->withErrors([
                'meta' => 'Set Meta Catalog ID and Access Token first, then save settings. If you do not have a catalog, use CSV import.',
            ]);
        }

        try {
            $response = Http::timeout(20)
                ->retry(1, 300)
                ->get("https://graph.facebook.com/v20.0/{$catalogId}/products", [
                    'fields' => 'retailer_id,name,description,price,availability,image_url',
                    'limit' => 100,
                    'access_token' => $token,
                ])
                ->throw()
                ->json();
        } catch (RequestException $exception) {
            return back()->withErrors([
                'meta' => 'Meta sync failed: '.$this->metaErrorMessage($exception),
            ]);
        }

        $rows = $response['data'] ?? [];
        if (! is_array($rows) || empty($rows)) {
            return back()->withErrors([
                'meta' => 'Meta sync returned no products. If your catalog is empty, use CSV import.',
            ]);
        }

        $synced = 0;
        DB::transaction(function () use ($rows, &$synced) {
            $category = Category::query()->firstOrCreate(
                ['slug' => 'meta-catalog'],
                ['name' => 'Meta Catalog', 'description' => 'Products imported from Meta Graph API', 'status' => Category::STATUS_ACTIVE]
            );

            foreach ($rows as $row) {
                $name = trim((string) ($row['name'] ?? ''));
                if ($name === '') {
                    continue;
                }

                $sourceId = trim((string) ($row['retailer_id'] ?? ''));
                $slugBase = $sourceId !== '' ? $sourceId : $name;
                $slug = Str::slug($slugBase);
                if ($slug === '') {
                    continue;
                }

                $product = Product::query()->updateOrCreate(
                    ['slug' => $slug],
                    [
                        'category_id' => $category->id,
                        'name' => $name,
                        'description' => $row['description'] ?? null,
                        'price' => $this->parseMetaPrice($row['price'] ?? null),
                        'status' => in_array(strtolower((string) ($row['availability'] ?? 'in stock')), ['out of stock', 'discontinued'], true)
                            ? Product::STATUS_INACTIVE
                            : Product::STATUS_ACTIVE,
                        'stock' => null,
                        'is_featured' => false,
                        'is_best_seller' => false,
                    ]
                );

                $imageUrl = trim((string) ($row['image_url'] ?? ''));
                if ($imageUrl !== '') {
                    ProductImage::query()->firstOrCreate([
                        'product_id' => $product->id,
                        'image_path' => $imageUrl,
                    ], [
                        'alt_text' => $product->name,
                        'sort_order' => 0,
                    ]);
                }

                $synced++;
            }
        });

        $this->auditLog->log($request->user(), 'admin.meta.products_synced', [
            'synced' => $synced,
            'catalog_id' => $catalogId,
        ]);

        return back()->with('status', "Meta sync completed. {$synced} product(s) processed.");
    }

    private function fontOptions(): array
    {
        return [
            'Lora' => 'Lora (serif)',
            'Nunito Sans' => 'Nunito Sans',
            'Poppins' => 'Poppins',
            'Merriweather' => 'Merriweather',
            'Playfair Display' => 'Playfair Display',
            'Montserrat' => 'Montserrat',
            'Source Sans 3' => 'Source Sans 3',
        ];
    }

    private function deleteReplacedAsset(?string $oldPath): void
    {
        if (! $oldPath || str_starts_with($oldPath, 'http://') || str_starts_with($oldPath, 'https://')) {
            return;
        }

        if (Storage::disk('public')->exists($oldPath)) {
            Storage::disk('public')->delete($oldPath);
        }
    }

    private function parseMetaPrice(mixed $raw): float
    {
        if (is_numeric($raw)) {
            return round((float) $raw, 2);
        }

        $value = (string) $raw;
        if (preg_match('/([0-9]+(?:\.[0-9]+)?)/', $value, $match) === 1) {
            return round((float) $match[1], 2);
        }

        return 0.0;
    }

    private function metaErrorMessage(RequestException $exception): string
    {
        $payload = $exception->response?->json();
        $message = $payload['error']['message'] ?? $exception->getMessage();

        return is_string($message) ? $message : 'Unknown Meta API error';
    }
}
