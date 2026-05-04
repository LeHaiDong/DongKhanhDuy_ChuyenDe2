<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class StockMovement extends Model
{
    use HasFactory;

    protected $fillable = [
        'camera_lens_id',
        'user_id',
        'order_id',
        'type',
        'quantity',
        'quantity_before',
        'quantity_after',
        'unit_cost',
        'reference_number',
        'location',
        'notes',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
        'unit_cost' => 'decimal:2',
    ];

    /**
     * Movement types
     */
    const TYPE_INITIAL_STOCK = 'initial_stock';
    const TYPE_PURCHASE = 'purchase';
    const TYPE_SALE = 'sale';
    const TYPE_ADJUSTMENT = 'adjustment';
    const TYPE_RETURN = 'return';
    const TYPE_DAMAGE = 'damage';
    const TYPE_TRANSFER = 'transfer';
    const TYPE_RESERVE = 'reserve';
    const TYPE_UNRESERVE = 'unreserve';

    /**
     * Relationships
     */
    public function cameraLens()
    {
        return $this->belongsTo(CameraLens::class);
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
     * Scopes
     */
    public function scopeIncoming(Builder $query)
    {
        return $query->where('quantity', '>', 0);
    }

    public function scopeOutgoing(Builder $query)
    {
        return $query->where('quantity', '<', 0);
    }

    public function scopeByType(Builder $query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeForProduct(Builder $query, $cameraLensId)
    {
        return $query->where('camera_lens_id', $cameraLensId);
    }

    public function scopeInDateRange(Builder $query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    /**
     * Accessors
     */
    public function getIsIncomingAttribute()
    {
        return $this->quantity > 0;
    }

    public function getIsOutgoingAttribute()
    {
        return $this->quantity < 0;
    }

    public function getAbsoluteQuantityAttribute()
    {
        return abs($this->quantity);
    }

    public function getTypeDisplayAttribute()
    {
        $types = [
            self::TYPE_INITIAL_STOCK => 'Nhập kho ban đầu',
            self::TYPE_PURCHASE => 'Mua hàng',
            self::TYPE_SALE => 'Bán hàng',
            self::TYPE_ADJUSTMENT => 'Điều chỉnh',
            self::TYPE_RETURN => 'Trả hàng',
            self::TYPE_DAMAGE => 'Hàng hỏng',
            self::TYPE_TRANSFER => 'Chuyển kho',
            self::TYPE_RESERVE => 'Đặt cọc',
            self::TYPE_UNRESERVE => 'Hủy đặt cọc',
        ];

        return $types[$this->type] ?? ucfirst($this->type);
    }

    public function getFormattedQuantityAttribute()
    {
        $prefix = $this->quantity > 0 ? '+' : '';
        return $prefix . number_format($this->quantity);
    }

    public function getFormattedUnitCostAttribute()
    {
        if (!$this->unit_cost) {
            return null;
        }
        return number_format($this->unit_cost, 0, ',', '.') . ' VNĐ';
    }

    public function getTotalValueAttribute()
    {
        if (!$this->unit_cost) {
            return null;
        }
        return $this->absolute_quantity * $this->unit_cost;
    }

    public function getFormattedTotalValueAttribute()
    {
        if (!$this->total_value) {
            return null;
        }
        return number_format($this->total_value, 0, ',', '.') . ' VNĐ';
    }

    /**
     * Static methods for creating movements
     */
    public static function createMovement($cameraLens, $type, $quantity, $options = [])
    {
        $movement = new self();
        $movement->camera_lens_id = $cameraLens->id;
        $movement->type = $type;
        $movement->quantity = $quantity;
        $movement->quantity_before = $cameraLens->stock_quantity;
        $movement->quantity_after = $cameraLens->stock_quantity + $quantity;
        
        // Optional fields
        if (isset($options['user_id'])) {
            $movement->user_id = $options['user_id'];
        }
        if (isset($options['order_id'])) {
            $movement->order_id = $options['order_id'];
        }
        if (isset($options['unit_cost'])) {
            $movement->unit_cost = $options['unit_cost'];
        }
        if (isset($options['reference_number'])) {
            $movement->reference_number = $options['reference_number'];
        }
        if (isset($options['location'])) {
            $movement->location = $options['location'];
        }
        if (isset($options['notes'])) {
            $movement->notes = $options['notes'];
        }
        if (isset($options['metadata'])) {
            $movement->metadata = $options['metadata'];
        }

        $movement->save();

        // Update product stock
        $cameraLens->update(['stock_quantity' => $movement->quantity_after]);

        // Update stock status
        $cameraLens->updateStockStatus();

        return $movement;
    }

    public static function purchase($cameraLens, $quantity, $unitCost, $options = [])
    {
        return self::createMovement($cameraLens, self::TYPE_PURCHASE, $quantity, array_merge($options, [
            'unit_cost' => $unitCost,
        ]));
    }

    public static function sale($cameraLens, $quantity, $orderId = null, $options = [])
    {
        return self::createMovement($cameraLens, self::TYPE_SALE, -$quantity, array_merge($options, [
            'order_id' => $orderId,
        ]));
    }

    public static function adjustment($cameraLens, $quantity, $reason, $options = [])
    {
        return self::createMovement($cameraLens, self::TYPE_ADJUSTMENT, $quantity, array_merge($options, [
            'notes' => $reason,
        ]));
    }

    public static function damage($cameraLens, $quantity, $reason, $options = [])
    {
        return self::createMovement($cameraLens, self::TYPE_DAMAGE, -$quantity, array_merge($options, [
            'notes' => $reason,
        ]));
    }

    public static function customerReturn($cameraLens, $quantity, $orderId = null, $options = [])
    {
        return self::createMovement($cameraLens, self::TYPE_RETURN, $quantity, array_merge($options, [
            'order_id' => $orderId,
        ]));
    }
}