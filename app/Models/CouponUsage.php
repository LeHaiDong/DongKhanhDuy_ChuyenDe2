<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CouponUsage extends Model
{
    use HasFactory;

    protected $fillable = [
        'coupon_id',
        'user_id',
        'order_id',
        'discount_amount',
        'order_total',
    ];

    protected $casts = [
        'discount_amount' => 'decimal:2',
        'order_total' => 'decimal:2',
    ];

    /**
     * Relationships
     */
    public function coupon()
    {
        return $this->belongsTo(Coupon::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Accessors
     */
    public function getFormattedDiscountAmountAttribute()
    {
        return number_format($this->discount_amount, 0, ',', '.') . ' VNĐ';
    }

    public function getFormattedOrderTotalAttribute()
    {
        return number_format($this->order_total, 0, ',', '.') . ' VNĐ';
    }

    public function getDiscountPercentageAttribute()
    {
        if ($this->order_total <= 0) {
            return 0;
        }
        return round(($this->discount_amount / $this->order_total) * 100, 2);
    }
}