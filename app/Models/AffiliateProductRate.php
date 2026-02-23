<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AffiliateProductRate extends Model
{
    use HasFactory;

    protected $fillable = [
        'affiliate_id',
        'product_id',
        'commission_type',
        'commission_value',
    ];

    protected function casts(): array
    {
        return [
            'commission_value' => 'decimal:2',
        ];
    }

    public function affiliate(): BelongsTo
    {
        return $this->belongsTo(Affiliate::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
