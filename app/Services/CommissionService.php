<?php

namespace App\Services;

use App\Models\Affiliate;
use App\Models\AffiliateProductRate;
use App\Models\Commission;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

class CommissionService
{
    public function __construct(
        private readonly AppSettingService $settings
    ) {
    }

    public function triggerStatus(): string
    {
        return $this->settings->get(
            AppSettingService::KEY_COMMISSION_TRIGGER_STATUS,
            Order::STATUS_CONFIRMED
        ) ?? Order::STATUS_CONFIRMED;
    }

    public function resolveRate(Affiliate $affiliate, Product $product): array
    {
        $override = AffiliateProductRate::query()
            ->where('affiliate_id', $affiliate->id)
            ->where('product_id', $product->id)
            ->first();

        if ($override) {
            return [
                'type' => $override->commission_type,
                'value' => (float) $override->commission_value,
                'source' => 'product_affiliate_override',
            ];
        }

        if ($product->default_commission_type && $product->default_commission_value !== null) {
            return [
                'type' => $product->default_commission_type,
                'value' => (float) $product->default_commission_value,
                'source' => 'product_default',
            ];
        }

        if ($affiliate->default_commission_type && $affiliate->default_commission_value !== null) {
            return [
                'type' => $affiliate->default_commission_type,
                'value' => (float) $affiliate->default_commission_value,
                'source' => 'affiliate_default',
            ];
        }

        return [
            'type' => $this->settings->get(AppSettingService::KEY_GLOBAL_COMMISSION_TYPE, 'percentage'),
            'value' => $this->settings->getFloat(AppSettingService::KEY_GLOBAL_COMMISSION_VALUE, 10),
            'source' => 'global_default',
        ];
    }

    public function syncForOrderStatusChange(Order $order, string $previousStatus): void
    {
        $newStatus = $order->status;
        $triggerStatus = $this->triggerStatus();
        $triggerStatuses = $triggerStatus === Order::STATUS_CONFIRMED
            ? [Order::STATUS_CONFIRMED, Order::STATUS_COMPLETED]
            : [Order::STATUS_COMPLETED];

        DB::transaction(function () use ($order, $newStatus, $previousStatus, $triggerStatuses) {
            $isTriggeredNow = in_array($newStatus, $triggerStatuses, true);
            $wasTriggeredBefore = in_array($previousStatus, $triggerStatuses, true);

            if ($isTriggeredNow && ! $wasTriggeredBefore) {
                $this->createForOrder($order);
            }

            if (in_array($newStatus, [Order::STATUS_CANCELLED, Order::STATUS_REFUNDED], true)) {
                $this->reverseForOrder($order, "Order status changed to {$newStatus}");
            }
        });
    }

    public function createForOrder(Order $order): void
    {
        if (! $order->affiliate_id) {
            return;
        }

        $order->loadMissing(['affiliate', 'product', 'items.product']);

        if (! $order->affiliate) {
            return;
        }

        $existing = Commission::query()
            ->where('order_id', $order->id)
            ->whereNot('status', Commission::STATUS_REVERSED)
            ->exists();

        if ($existing) {
            return;
        }

        $items = $order->items;
        if ($items->isEmpty() && $order->product) {
            $items = collect([
                OrderItem::query()->create([
                    'order_id' => $order->id,
                    'product_id' => $order->product_id,
                    'qty' => $order->qty,
                    'unit_price' => $order->qty > 0 ? $order->total_amount / $order->qty : $order->total_amount,
                    'line_total' => $order->total_amount,
                ]),
            ]);
        }

        foreach ($items as $item) {
            $product = $item->product ?? $order->product;
            if (! $product) {
                continue;
            }

            $rate = $this->resolveRate($order->affiliate, $product);
            $amount = $this->calculateAmount($item, $rate['type'], (float) $rate['value']);

            Commission::query()->create([
                'order_id' => $order->id,
                'order_item_id' => $item->id,
                'affiliate_id' => $order->affiliate_id,
                'commission_type' => $rate['type'],
                'rate_value' => $rate['value'],
                'amount' => $amount,
                'status' => Commission::STATUS_APPROVED,
                'notes' => "Commission source: {$rate['source']}",
            ]);
        }
    }

    public function reverseForOrder(Order $order, string $note = 'Order reversed'): void
    {
        Commission::query()
            ->where('order_id', $order->id)
            ->whereNot('status', Commission::STATUS_REVERSED)
            ->update([
                'status' => Commission::STATUS_REVERSED,
                'notes' => $note,
            ]);
    }

    private function calculateAmount(OrderItem $item, string $type, float $value): float
    {
        if ($type === 'fixed') {
            return round($value * max($item->qty, 1), 2);
        }

        return round(((float) $item->line_total) * ($value / 100), 2);
    }
}
