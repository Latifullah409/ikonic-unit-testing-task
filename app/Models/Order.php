<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property Merchant $merchant
 * @property Affiliate $affiliate
 * @property float $subtotal
 * @property float $commission_owed
 * @property string $payout_status
 */
class Order extends Model
{
    use HasFactory;

    const STATUS_UNPAID = 'unpaid';
    const STATUS_PAID = 'paid';

    protected $fillable = [
        'external_order_id',
        'merchant_id',
        'affiliate_id',
        'subtotal',
        'commission_owed',
        'payout_status',
        'customer_email',
        'created_at'
    ];

    public function merchant()
    {
        return $this->belongsTo(Merchant::class);
    }

    public function affiliate()
    {
        return $this->belongsTo(Affiliate::class);
    }

    public function scopeWhereExternalOrderId($query, $externalOrderId)
    {
        return $query->where('external_order_id', $externalOrderId);
    }

    public function scopeCalculateCommissionOwedBetweenDates($query, $from, $to)
    {
        return $query->where('affiliate_id', '!=', null)
            ->whereBetween('created_at', [$from, $to])
            ->sum('commission_owed');
    }

    public function scopeCalculateTotalRevenueBetweenDates($query, $from, $to)
    {
        return $query->whereBetween('created_at', [$from, $to])->sum('subtotal');
    }
}
