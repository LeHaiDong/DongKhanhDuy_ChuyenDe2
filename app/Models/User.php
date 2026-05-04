<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'address',
        'is_admin',
        'email_verification_code',
        'verification_code_expires_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'last_login_at' => 'datetime',
        'is_admin' => 'boolean',
        'verification_code_expires_at' => 'datetime',
    ];

    /**
     * Check if user is admin
     */
    public function isAdmin(): bool
    {
        return $this->is_admin === true;
    }

    /**
     * Check if user has verified email
     */
    public function hasVerifiedEmail(): bool
    {
        return !is_null($this->email_verified_at);
    }

    /**
     * Generate email verification code
     */
    public function generateEmailVerificationCode(): string
    {
        $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        $this->update([
            'email_verification_code' => $code,
            'verification_code_expires_at' => now()->addMinutes(15), // Hết hạn sau 15 phút
        ]);

        return $code;
    }

    /**
     * Verify email with code
     */
    public function verifyEmail(string $code): bool
    {
        if ($this->email_verification_code === $code &&
            $this->verification_code_expires_at &&
            $this->verification_code_expires_at->isFuture()) {

            $this->update([
                'email_verified_at' => now(),
                'email_verification_code' => null,
                'verification_code_expires_at' => null,
            ]);

            return true;
        }

        return false;
    }

    /**
     * Check if verification code is expired
     */
    public function isVerificationCodeExpired(): bool
    {
        return $this->verification_code_expires_at && $this->verification_code_expires_at->isPast();
    }

    /**
     * Get the user's favorites.
     */
    public function favorites()
    {
        return $this->hasMany(Favorite::class);
    }

    /**
     * Get the camera lenses that the user has favorited.
     */
    public function favoriteCameraLenses()
    {
        return $this->belongsToMany(CameraLens::class, 'favorites')->withTimestamps();
    }

    /**
     * Get the reviews written by this user
     */
    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function sellerShop()
    {
        return $this->hasOne(SellerShop::class);
    }

    /**
     * Get review votes by this user
     */
    public function reviewVotes()
    {
        return $this->hasMany(ReviewVote::class);
    }

    /**
     * Check if user has favorited a specific camera lens.
     */
    public function hasFavorited($cameraLensId): bool
    {
        return $this->favorites()->where('camera_lens_id', $cameraLensId)->exists();
    }

    /**
     * Get the orders for the user.
     */
    public function orders()
    {
        return $this->hasMany(Order::class)->orderBy('created_at', 'desc');
    }

    /**
     * Get recent orders
     */
    public function recentOrders($limit = 5)
    {
        return $this->orders()->limit($limit);
    }

    /**
     * Get total spent by user
     */
    public function getTotalSpentAttribute()
    {
        return $this->orders()->where('payment_status', 'paid')->sum('total_amount');
    }

    /**
     * Get total orders count
     */
    public function getTotalOrdersAttribute()
    {
        return $this->orders()->count();
    }

    /**
     * Get completed orders count
     */
    public function getCompletedOrdersAttribute()
    {
        return $this->orders()->where('status', 'delivered')->count();
    }

    /**
     * Toggle favorite status for a camera lens.
     */
    public function toggleFavorite($cameraLensId): bool
    {
        $favorite = $this->favorites()->where('camera_lens_id', $cameraLensId)->first();
        
        if ($favorite) {
            $favorite->delete();
            return false; // Removed from favorites
        } else {
            $this->favorites()->create(['camera_lens_id' => $cameraLensId]);
            return true; // Added to favorites
        }
    }
}
