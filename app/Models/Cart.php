<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    use HasFactory;

    protected $fillable = [
        'session_id',
        'user_id',
        'camera_lens_id',
        'quantity',
        'unit_price',
        'is_direct_checkout',
    ];

    protected $casts = [
        'unit_price' => 'integer',
        'quantity' => 'integer',
        'is_direct_checkout' => 'boolean',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function cameraLens()
    {
        return $this->belongsTo(CameraLens::class);
    }

    // Scopes
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeForSession($query, $sessionId)
    {
        return $query->where('session_id', $sessionId);
    }

    public function scopeActive($query)
    {
        return $query->whereHas('cameraLens', function ($q) {
            $q->where('is_active', true);
        });
    }

    // Accessors
    public function getTotalPriceAttribute()
    {
        return $this->quantity * $this->unit_price;
    }

    public function getFormattedTotalPriceAttribute()
    {
        return number_format($this->total_price, 0, ',', '.') . ' VNĐ';
    }

    public function getFormattedUnitPriceAttribute()
    {
        return number_format($this->unit_price, 0, ',', '.') . ' VNĐ';
    }

    // Static methods
    public static function getCartItems($userId = null, $sessionId = null, bool $includeDirectCheckout = false, ?int $directCheckoutId = null)
    {
        $query = self::with('cameraLens')->active();

        if ($userId) {
            $query->forUser($userId);
        } elseif ($sessionId) {
            $query->forSession($sessionId);
        }

        if ($directCheckoutId) {
            $query->whereKey($directCheckoutId)->where('is_direct_checkout', true);
        } elseif (!$includeDirectCheckout) {
            $query->where('is_direct_checkout', false);
        }

        return $query->get();
    }

    public static function getCartTotal($userId = null, $sessionId = null)
    {
        $items = self::getCartItems($userId, $sessionId);
        return $items->sum('total_price');
    }

    public static function getCartCount($userId = null, $sessionId = null)
    {
        $query = self::active();

        if ($userId) {
            $query->forUser($userId);
        } elseif ($sessionId) {
            $query->forSession($sessionId);
        }

        $query->where('is_direct_checkout', false);

        return $query->sum('quantity');
    }
}
