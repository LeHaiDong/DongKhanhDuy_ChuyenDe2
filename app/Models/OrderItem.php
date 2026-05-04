<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'camera_lens_id',
        'product_name',
        'product_brand',
        'product_sku',
        'product_description',
        'product_image',
        'quantity',
        'unit_price',
        'total_price',
        'discount_amount',
        'discount_type',
        'final_price'
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'total_price' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'final_price' => 'decimal:2',
    ];

    /**
     * Boot the model
     */
    protected static function boot()
    {
        parent::boot();

        // Tự động tính toán giá khi tạo hoặc cập nhật
        static::saving(function ($orderItem) {
            $orderItem->calculatePrices();
        });
    }

    /**
     * Relationship: OrderItem thuộc về Order
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Relationship: OrderItem thuộc về CameraLens
     */
    public function cameraLens()
    {
        return $this->belongsTo(CameraLens::class);
    }

    /**
     * Accessors
     */
    public function getFormattedUnitPriceAttribute()
    {
        return number_format($this->unit_price, 0, ',', '.') . ' VNĐ';
    }

    public function getFormattedTotalPriceAttribute()
    {
        return number_format($this->total_price, 0, ',', '.') . ' VNĐ';
    }

    public function getFormattedFinalPriceAttribute()
    {
        return number_format($this->final_price, 0, ',', '.') . ' VNĐ';
    }

    public function getFormattedDiscountAmountAttribute()
    {
        return number_format($this->discount_amount, 0, ',', '.') . ' VNĐ';
    }

    public function getHasDiscountAttribute()
    {
        return $this->discount_amount > 0;
    }

    public function getDiscountPercentageAttribute()
    {
        if ($this->discount_type === 'percentage') {
            return $this->discount_amount;
        }
        
        if ($this->total_price > 0) {
            return round(($this->discount_amount / $this->total_price) * 100, 2);
        }
        
        return 0;
    }

    /**
     * Methods
     */
    public function calculatePrices()
    {
        // Tính tổng giá trước giảm giá
        $this->total_price = $this->quantity * $this->unit_price;
        
        // Tính giảm giá
        $discountAmount = 0;
        if ($this->discount_amount > 0) {
            if ($this->discount_type === 'percentage') {
                $discountAmount = ($this->total_price * $this->discount_amount) / 100;
            } else {
                $discountAmount = $this->discount_amount;
            }
        }
        
        $this->discount_amount = $discountAmount;
        $this->final_price = $this->total_price - $discountAmount;
    }

    /**
     * Apply discount to this item
     */
    public function applyDiscount($amount, $type = 'fixed')
    {
        $this->discount_amount = $amount;
        $this->discount_type = $type;
        $this->calculatePrices();
        $this->save();
    }

    /**
     * Remove discount from this item
     */
    public function removeDiscount()
    {
        $this->discount_amount = 0;
        $this->discount_type = null;
        $this->calculatePrices();
        $this->save();
    }

    /**
     * Create OrderItem from CameraLens
     */
    public static function createFromCameraLens(Order $order, Product $cameraLens, $quantity, $unitPrice = null)
    {
        $unitPrice = $unitPrice ?: $cameraLens->price;
        
        return self::create([
            'order_id' => $order->id,
            'camera_lens_id' => $cameraLens->id,
            'product_name' => $cameraLens->name,
            'product_brand' => $cameraLens->brand,
            'product_sku' => $cameraLens->id, // Có thể thay bằng SKU thực tế nếu có
            'product_description' => $cameraLens->description,
            'product_image' => $cameraLens->image ?: $cameraLens->image_url,
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'total_price' => $quantity * $unitPrice,
            'final_price' => $quantity * $unitPrice,
        ]);
    }

    /**
     * Check if product is still available
     */
    public function isProductAvailable()
    {
        return $this->cameraLens && 
               $this->cameraLens->is_active && 
               $this->cameraLens->stock_quantity >= $this->quantity;
    }

    /**
     * Get current product price (for comparison)
     */
    public function getCurrentProductPrice()
    {
        return $this->cameraLens ? $this->cameraLens->price : null;
    }

    /**
     * Check if price has changed since order
     */
    public function hasPriceChanged()
    {
        $currentPrice = $this->getCurrentProductPrice();
        return $currentPrice && $currentPrice != $this->unit_price;
    }

    /**
     * Get price difference
     */
    public function getPriceDifference()
    {
        $currentPrice = $this->getCurrentProductPrice();
        if (!$currentPrice) return 0;
        
        return $currentPrice - $this->unit_price;
    }

    /**
     * Update quantity and recalculate
     */
    public function updateQuantity($newQuantity)
    {
        $this->quantity = $newQuantity;
        $this->calculatePrices();
        $this->save();
        
        // Recalculate order totals
        $this->order->calculateTotals();
    }

    /**
     * Scopes
     */
    public function scopeForOrder($query, $orderId)
    {
        return $query->where('order_id', $orderId);
    }

    public function scopeForProduct($query, $cameraLensId)
    {
        return $query->where('camera_lens_id', $cameraLensId);
    }

    public function scopeWithDiscount($query)
    {
        return $query->where('discount_amount', '>', 0);
    }

    /**
     * Get best selling products from order items
     */
    public static function getBestSellingProducts($limit = 10, $startDate = null, $endDate = null)
    {
        $query = self::with('cameraLens')
                    ->selectRaw('camera_lens_id, SUM(quantity) as total_sold, COUNT(*) as order_count')
                    ->groupBy('camera_lens_id')
                    ->orderByDesc('total_sold');

        if ($startDate && $endDate) {
            $query->whereBetween('created_at', [$startDate, $endDate]);
        }

        return $query->limit($limit)->get();
    }

    /**
     * Get revenue by product
     */
    public static function getRevenueByProduct($limit = 10, $startDate = null, $endDate = null)
    {
        $query = self::with('cameraLens')
                    ->selectRaw('camera_lens_id, SUM(final_price) as total_revenue, SUM(quantity) as total_sold')
                    ->groupBy('camera_lens_id')
                    ->orderByDesc('total_revenue');

        if ($startDate && $endDate) {
            $query->whereBetween('created_at', [$startDate, $endDate]);
        }

        return $query->limit($limit)->get();
    }
}
