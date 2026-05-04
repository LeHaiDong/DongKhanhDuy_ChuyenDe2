<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

class Coupon extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'description',
        'type',
        'value',
        'minimum_amount',
        'maximum_discount',
        'usage_limit',
        'usage_limit_per_user',
        'used_count',
        'starts_at',
        'expires_at',
        'is_active',
        'applicable_products',
        'applicable_categories',
        'excluded_products',
        'user_restrictions',
        'first_order_only',
        'admin_notes',
    ];

    protected $casts = [
        'value' => 'decimal:2',
        'minimum_amount' => 'decimal:2',
        'maximum_discount' => 'decimal:2',
        'starts_at' => 'datetime',
        'expires_at' => 'datetime',
        'is_active' => 'boolean',
        'applicable_products' => 'array',
        'applicable_categories' => 'array',
        'excluded_products' => 'array',
        'user_restrictions' => 'array',
        'first_order_only' => 'boolean',
    ];

    /**
     * Coupon types
     */
    const TYPE_FIXED = 'fixed';
    const TYPE_PERCENTAGE = 'percentage';

    /**
     * Relationships
     */
    public function usages()
    {
        return $this->hasMany(CouponUsage::class);
    }

    public function orders()
    {
        return $this->belongsToMany(Order::class, 'coupon_usages')->withPivot('discount_amount', 'order_total');
    }

    /**
     * Scopes
     */
    public function scopeActive(Builder $query)
    {
        return $query->where('is_active', true);
    }

    public function scopeValid(Builder $query)
    {
        $now = now();
        return $query->active()
                    ->where('starts_at', '<=', $now)
                    ->where('expires_at', '>=', $now);
    }

    public function scopeNotExpired(Builder $query)
    {
        return $query->where('expires_at', '>=', now());
    }

    public function scopeByCode(Builder $query, $code)
    {
        return $query->where('code', strtoupper($code));
    }

    public function scopeAvailable(Builder $query)
    {
        return $query->valid()
                    ->where(function($q) {
                        $q->whereNull('usage_limit')
                          ->orWhereRaw('used_count < usage_limit');
                    });
    }

    /**
     * Accessors
     */
    public function getIsValidAttribute()
    {
        $now = now();
        return $this->is_active && 
               $this->starts_at <= $now && 
               $this->expires_at >= $now;
    }

    public function getIsExpiredAttribute()
    {
        return $this->expires_at < now();
    }

    public function getIsUsageLimitReachedAttribute()
    {
        return $this->usage_limit && $this->used_count >= $this->usage_limit;
    }

    public function getFormattedValueAttribute()
    {
        if ($this->type === self::TYPE_PERCENTAGE) {
            return $this->value . '%';
        }
        return number_format($this->value, 0, ',', '.') . ' VNĐ';
    }

    public function getFormattedMinimumAmountAttribute()
    {
        if (!$this->minimum_amount) {
            return null;
        }
        return number_format($this->minimum_amount, 0, ',', '.') . ' VNĐ';
    }

    public function getFormattedMaximumDiscountAttribute()
    {
        if (!$this->maximum_discount) {
            return null;
        }
        return number_format($this->maximum_discount, 0, ',', '.') . ' VNĐ';
    }

    public function getStatusAttribute()
    {
        if (!$this->is_active) {
            return 'inactive';
        }
        
        $now = now();
        if ($this->starts_at > $now) {
            return 'scheduled';
        }
        if ($this->expires_at < $now) {
            return 'expired';
        }
        if ($this->is_usage_limit_reached) {
            return 'exhausted';
        }
        
        return 'active';
    }

    public function getStatusBadgeAttribute()
    {
        $statuses = [
            'active' => '<span class="badge badge-success">Đang hoạt động</span>',
            'scheduled' => '<span class="badge badge-info">Đã lên lịch</span>',
            'expired' => '<span class="badge badge-danger">Đã hết hạn</span>',
            'exhausted' => '<span class="badge badge-warning">Đã hết lượt</span>',
            'inactive' => '<span class="badge badge-secondary">Không hoạt động</span>',
        ];

        return $statuses[$this->status] ?? $statuses['inactive'];
    }

    /**
     * Validation methods
     */
    public function canBeUsedBy($user, $orderTotal = 0, $cartItems = [])
    {
        // Check if coupon is valid
        if (!$this->is_valid) {
            return ['valid' => false, 'message' => 'Mã giảm giá không hợp lệ hoặc đã hết hạn.'];
        }

        // Check usage limit
        if ($this->is_usage_limit_reached) {
            return ['valid' => false, 'message' => 'Mã giảm giá đã hết lượt sử dụng.'];
        }

        // Check minimum amount
        if ($this->minimum_amount && $orderTotal < $this->minimum_amount) {
            return ['valid' => false, 'message' => "Đơn hàng tối thiểu {$this->formatted_minimum_amount} để sử dụng mã này."];
        }

        // Check user-specific usage limit
        if ($this->usage_limit_per_user) {
            $userUsageCount = $this->usages()->where('user_id', $user->id)->count();
            if ($userUsageCount >= $this->usage_limit_per_user) {
                return ['valid' => false, 'message' => 'Bạn đã sử dụng hết lượt cho mã giảm giá này.'];
            }
        }

        // Check first order only
        if ($this->first_order_only) {
            $hasOrdersBefore = Order::where('user_id', $user->id)
                                   ->where('status', '!=', 'cancelled')
                                   ->count() > 0;
            if ($hasOrdersBefore) {
                return ['valid' => false, 'message' => 'Mã giảm giá chỉ dành cho đơn hàng đầu tiên.'];
            }
        }

        // Check user restrictions
        if ($this->user_restrictions) {
            $userEmail = $user->email;
            $userId = $user->id;
            
            $allowedUsers = collect($this->user_restrictions);
            if (!$allowedUsers->contains($userEmail) && !$allowedUsers->contains($userId)) {
                return ['valid' => false, 'message' => 'Mã giảm giá không áp dụng cho tài khoản của bạn.'];
            }
        }

        // Check applicable products/categories
        if ($cartItems && ($this->applicable_products || $this->applicable_categories || $this->excluded_products)) {
            $validItems = $this->getValidCartItems($cartItems);
            if (empty($validItems)) {
                return ['valid' => false, 'message' => 'Mã giảm giá không áp dụng cho sản phẩm nào trong giỏ hàng.'];
            }
        }

        return ['valid' => true, 'message' => 'Mã giảm giá hợp lệ.'];
    }

    /**
     * Calculate discount amount
     */
    public function calculateDiscount($orderTotal, $cartItems = [])
    {
        $discountableAmount = $orderTotal;

        // If coupon applies to specific products, calculate only for those
        if ($this->applicable_products || $this->applicable_categories || $this->excluded_products) {
            $validItems = $this->getValidCartItems($cartItems);
            $discountableAmount = collect($validItems)->sum('total');
        }

        if ($this->type === self::TYPE_FIXED) {
            $discount = min($this->value, $discountableAmount);
        } else {
            $discount = ($discountableAmount * $this->value) / 100;
            
            // Apply maximum discount limit for percentage coupons
            if ($this->maximum_discount) {
                $discount = min($discount, $this->maximum_discount);
            }
        }

        return round($discount, 2);
    }

    /**
     * Get valid cart items for this coupon
     */
    private function getValidCartItems($cartItems)
    {
        $validItems = [];

        foreach ($cartItems as $item) {
            $productId = $item['product_id'] ?? $item['camera_lens_id'] ?? null;
            $categoryIds = $item['category_ids'] ?? [];

            // Skip if in excluded products
            if ($this->excluded_products && in_array($productId, $this->excluded_products)) {
                continue;
            }

            // If specific products are set, check if item is included
            if ($this->applicable_products) {
                if (in_array($productId, $this->applicable_products)) {
                    $validItems[] = $item;
                }
                continue;
            }

            // If specific categories are set, check if item belongs to any
            if ($this->applicable_categories) {
                $hasValidCategory = !empty(array_intersect($categoryIds, $this->applicable_categories));
                if ($hasValidCategory) {
                    $validItems[] = $item;
                }
                continue;
            }

            // If no specific products/categories, all items are valid (except excluded)
            $validItems[] = $item;
        }

        return $validItems;
    }

    /**
     * Use this coupon
     */
    public function use($user, $order, $discountAmount)
    {
        // Create usage record
        CouponUsage::create([
            'coupon_id' => $this->id,
            'user_id' => $user->id,
            'order_id' => $order->id,
            'discount_amount' => $discountAmount,
            'order_total' => $order->total_amount,
        ]);

        // Increment usage count
        $this->increment('used_count');
    }

    /**
     * Static method to find and validate coupon
     */
    public static function findValidCoupon($code, $user, $orderTotal = 0, $cartItems = [])
    {
        $coupon = self::byCode($code)->first();

        if (!$coupon) {
            return ['valid' => false, 'message' => 'Mã giảm giá không tồn tại.'];
        }

        $validation = $coupon->canBeUsedBy($user, $orderTotal, $cartItems);
        
        if ($validation['valid']) {
            return ['valid' => true, 'coupon' => $coupon, 'message' => $validation['message']];
        }

        return $validation;
    }

    /**
     * Generate unique coupon code
     */
    public static function generateUniqueCode($prefix = '', $length = 8)
    {
        do {
            $code = $prefix . strtoupper(\Str::random($length));
        } while (self::where('code', $code)->exists());

        return $code;
    }
}