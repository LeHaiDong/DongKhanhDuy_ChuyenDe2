<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_number',
        'user_id',
        'status',
        'payment_status',
        'payment_method',
        'payment_reference',
        'shipping_name',
        'shipping_phone',
        'shipping_email',
        'shipping_address',
        'shipping_province',
        'shipping_district',
        'shipping_ward',
        'shipping_method',
        'shipping_fee',
        'subtotal',
        'tax_amount',
        'discount_amount',
        'total_amount',
        'notes',
        'coupon_code',
        'tracking_info',
        'confirmed_at',
        'shipped_at',
        'delivered_at',
        'cancelled_at'
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'shipping_fee' => 'decimal:2',
        'tracking_info' => 'array',
        'confirmed_at' => 'datetime',
        'shipped_at' => 'datetime',
        'delivered_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    /**
     * Boot the model
     */
    protected static function boot()
    {
        parent::boot();

        // Tự động tạo order number khi tạo đơn hàng mới
        static::creating(function ($order) {
            if (empty($order->order_number)) {
                $order->order_number = self::generateOrderNumber();
            }
        });
    }

    /**
     * Relationship: Đơn hàng thuộc về user
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relationship: Đơn hàng có nhiều items
     */
    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Relationship: Đơn hàng có nhiều sản phẩm thông qua items
     */
    public function cameraLenses()
    {
        return $this->belongsToMany(CameraLens::class, 'order_items')
                    ->withPivot(['quantity', 'unit_price', 'total_price', 'final_price'])
                    ->withTimestamps();
    }

    /**
     * Scopes
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeConfirmed($query)
    {
        return $query->where('status', 'confirmed');
    }

    public function scopeProcessing($query)
    {
        return $query->where('status', 'processing');
    }

    public function scopeShipped($query)
    {
        return $query->where('status', 'shipped');
    }

    public function scopeDelivered($query)
    {
        return $query->where('status', 'delivered');
    }

    public function scopeCancelled($query)
    {
        return $query->where('status', 'cancelled');
    }

    public function scopePaid($query)
    {
        return $query->where('payment_status', 'paid');
    }

    public function scopeUnpaid($query)
    {
        return $query->where('payment_status', 'pending');
    }

    /**
     * Accessors
     */
    public function getFormattedTotalAttribute()
    {
        return number_format($this->total_amount, 0, ',', '.') . ' VNĐ';
    }

    public function getFormattedSubtotalAttribute()
    {
        return number_format($this->subtotal, 0, ',', '.') . ' VNĐ';
    }

    public function getFormattedShippingFeeAttribute()
    {
        return number_format($this->shipping_fee, 0, ',', '.') . ' VNĐ';
    }

    public function getFormattedDiscountAmountAttribute()
    {
        return number_format($this->discount_amount, 0, ',', '.') . ' VNĐ';
    }

    public function getStatusBadgeAttribute()
    {
        $badges = [
            'pending' => '<span class="badge bg-warning">Chờ xử lý</span>',
            'confirmed' => '<span class="badge bg-info">Đã xác nhận</span>',
            'processing' => '<span class="badge bg-primary">Đang xử lý</span>',
            'shipped' => '<span class="badge bg-secondary">Đã gửi</span>',
            'delivered' => '<span class="badge bg-success">Đã giao</span>',
            'cancelled' => '<span class="badge bg-danger">Đã hủy</span>',
            'refunded' => '<span class="badge bg-dark">Đã hoàn tiền</span>',
        ];

        return $badges[$this->status] ?? '<span class="badge bg-light">Unknown</span>';
    }

    public function getPaymentStatusBadgeAttribute()
    {
        $badges = [
            'pending' => '<span class="badge bg-warning">Chờ thanh toán</span>',
            'paid' => '<span class="badge bg-success">Đã thanh toán</span>',
            'failed' => '<span class="badge bg-danger">Thanh toán thất bại</span>',
            'refunded' => '<span class="badge bg-dark">Đã hoàn tiền</span>',
        ];

        return $badges[$this->payment_status] ?? '<span class="badge bg-light">Unknown</span>';
    }

    public function getPaymentMethodNameAttribute()
    {
        $methods = [
            'cod' => 'Thanh toán khi nhận hàng',
            'bank_transfer' => 'Chuyển khoản ngân hàng',
            'vnpay' => 'VNPay',
            'momo' => 'MoMo',
            'credit_card' => 'Thẻ tín dụng',
        ];

        return $methods[$this->payment_method] ?? 'Không xác định';
    }

    public function getFullShippingAddressAttribute()
    {
        $parts = array_filter([
            $this->shipping_address,
            $this->shipping_ward,
            $this->shipping_district,
            $this->shipping_province
        ]);

        return implode(', ', $parts);
    }

    public function getTotalItemsAttribute()
    {
        return $this->items->sum('quantity');
    }

    /**
     * Methods
     */
    public function canBeCancelled()
    {
        return in_array($this->status, ['pending', 'confirmed']);
    }

    public function canBeConfirmed()
    {
        return $this->status === 'pending';
    }

    public function canBeShipped()
    {
        return in_array($this->status, ['confirmed', 'processing']);
    }

    public function canBeDelivered()
    {
        return $this->status === 'shipped';
    }

    public function isCompleted()
    {
        return $this->status === 'delivered';
    }

    public function isCancelled()
    {
        return $this->status === 'cancelled';
    }

    public function isPaid()
    {
        return $this->payment_status === 'paid';
    }

    /**
     * Status transitions
     */
    public function confirm()
    {
        if ($this->canBeConfirmed()) {
            $this->update([
                'status' => 'confirmed',
                'confirmed_at' => now()
            ]);
            return true;
        }
        return false;
    }

    public function markAsProcessing()
    {
        if ($this->status === 'confirmed') {
            $this->update(['status' => 'processing']);
            return true;
        }
        return false;
    }

    public function ship($trackingInfo = null)
    {
        if ($this->canBeShipped()) {
            $updateData = [
                'status' => 'shipped',
                'shipped_at' => now()
            ];

            if ($trackingInfo) {
                $updateData['tracking_info'] = $trackingInfo;
            }

            $this->update($updateData);
            return true;
        }
        return false;
    }

    public function deliver()
    {
        if ($this->canBeDelivered()) {
            $this->update([
                'status' => 'delivered',
                'delivered_at' => now(),
                'payment_status' => 'paid' // Tự động đánh dấu đã thanh toán khi giao hàng thành công
            ]);
            return true;
        }
        return false;
    }

    public function cancel($reason = null)
    {
        if ($this->canBeCancelled()) {
            $updateData = [
                'status' => 'cancelled',
                'cancelled_at' => now()
            ];

            if ($reason) {
                $updateData['notes'] = ($this->notes ? $this->notes . "\n" : '') . "Lý do hủy: " . $reason;
            }

            $this->update($updateData);

            // Hoàn lại stock cho các sản phẩm
            $this->restoreStock();

            return true;
        }
        return false;
    }

    public function markAsPaid($paymentReference = null)
    {
        $updateData = ['payment_status' => 'paid'];
        
        if ($paymentReference) {
            $updateData['payment_reference'] = $paymentReference;
        }

        $this->update($updateData);
    }

    /**
     * Stock management
     */
    public function reduceStock()
    {
        foreach ($this->items as $item) {
            $cameraLens = $item->cameraLens;
            if ($cameraLens && $cameraLens->stock_quantity >= $item->quantity) {
                $cameraLens->decrement('stock_quantity', $item->quantity);
            }
        }
    }

    public function restoreStock()
    {
        foreach ($this->items as $item) {
            $cameraLens = $item->cameraLens;
            if ($cameraLens) {
                $cameraLens->increment('stock_quantity', $item->quantity);
            }
        }
    }

    /**
     * Generate unique order number
     */
    public static function generateOrderNumber()
    {
        $date = now()->format('Ymd');
        $lastOrder = self::where('order_number', 'like', "ORD-{$date}-%")->latest()->first();
        
        if ($lastOrder) {
            $lastNumber = intval(substr($lastOrder->order_number, -3));
            $newNumber = str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '001';
        }

        return "ORD-{$date}-{$newNumber}";
    }

    /**
     * Calculate totals
     */
    public function calculateTotals()
    {
        $subtotal = $this->items->sum('final_price');
        $total = $subtotal + $this->shipping_fee - $this->discount_amount + $this->tax_amount;

        $this->update([
            'subtotal' => $subtotal,
            'total_amount' => $total
        ]);
    }

    /**
     * Get orders for dashboard stats
     */
    public static function getStatsForPeriod($startDate, $endDate = null)
    {
        $endDate = $endDate ?: now();
        
        return self::whereBetween('created_at', [$startDate, $endDate])
                  ->selectRaw('
                      COUNT(*) as total_orders,
                      SUM(total_amount) as total_revenue,
                      AVG(total_amount) as average_order_value,
                      COUNT(CASE WHEN status = "delivered" THEN 1 END) as completed_orders,
                      COUNT(CASE WHEN status = "cancelled" THEN 1 END) as cancelled_orders
                  ')
                  ->first();
    }
}