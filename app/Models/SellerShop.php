<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SellerShop extends Model
{
    use HasFactory;

    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';

    protected $fillable = [
        'user_id',
        'shop_name',
        'slug',
        'brand_name',
        'primary_category_id',
        'phone',
        'address',
        'description',
        'document_type',
        'document_number',
        'document_note',
        'document_image',
        'shop_image',
        'status',
        'admin_note',
        'approved_at',
        'approved_by',
    ];

    protected $casts = [
        'approved_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function category()
    {
        return $this->belongsTo(Category::class, 'primary_category_id');
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeApproved($query)
    {
        return $query->where('status', self::STATUS_APPROVED);
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    public function isRejected(): bool
    {
        return $this->status === self::STATUS_REJECTED;
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_APPROVED => 'Đã duyệt',
            self::STATUS_REJECTED => 'Từ chối',
            default => 'Chờ duyệt',
        };
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_APPROVED => 'status-active',
            self::STATUS_REJECTED => 'status-inactive',
            default => 'status-low-stock',
        };
    }

    public function getDocumentTypeLabelAttribute(): string
    {
        return match ($this->document_type) {
            'business_registration' => 'Giấy đăng ký kinh doanh',
            'citizen_id' => 'Căn cước công dân',
            'household_business' => 'Giấy hộ kinh doanh',
            'brand_authorization' => 'Giấy ủy quyền thương hiệu',
            default => 'Chưa cập nhật',
        };
    }

    public function getShopImageUrlAttribute(): ?string
    {
        return $this->publicUploadUrl($this->shop_image);
    }

    public function getDocumentImageUrlAttribute(): ?string
    {
        return $this->publicUploadUrl($this->document_image);
    }

    private function publicUploadUrl(?string $path): ?string
    {
        if (!$path) {
            return null;
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        return asset(ltrim($path, '/'));
    }
}
