<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Review extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'camera_lens_id',
        'order_id',
        'rating',
        'title',
        'content',
        'pros',
        'cons',
        'images',
        'is_verified_purchase',
        'is_featured',
        'is_approved',
        'helpful_votes',
        'unhelpful_votes',
        'approved_at',
    ];

    protected $casts = [
        'pros' => 'array',
        'cons' => 'array',
        'images' => 'array',
        'is_verified_purchase' => 'boolean',
        'is_featured' => 'boolean',
        'is_approved' => 'boolean',
        'approved_at' => 'datetime',
    ];

    /**
     * Relationships
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function cameraLens()
    {
        return $this->belongsTo(CameraLens::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function votes()
    {
        return $this->hasMany(ReviewVote::class);
    }

    public function helpfulVotes()
    {
        return $this->hasMany(ReviewVote::class)->where('vote_type', 'helpful');
    }

    public function unhelpfulVotes()
    {
        return $this->hasMany(ReviewVote::class)->where('vote_type', 'unhelpful');
    }

    /**
     * Scopes
     */
    public function scopeApproved(Builder $query)
    {
        return $query->where('is_approved', true);
    }

    public function scopeFeatured(Builder $query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeVerifiedPurchase(Builder $query)
    {
        return $query->where('is_verified_purchase', true);
    }

    public function scopeByRating(Builder $query, $rating)
    {
        return $query->where('rating', $rating);
    }

    public function scopeWithImages(Builder $query)
    {
        return $query->whereNotNull('images')->where('images', '!=', '[]');
    }

    /**
     * Accessors
     */
    public function getStarsAttribute()
    {
        return str_repeat('★', $this->rating) . str_repeat('☆', 5 - $this->rating);
    }

    public function getRatingPercentageAttribute()
    {
        return ($this->rating / 5) * 100;
    }

    public function getIsHelpfulAttribute()
    {
        return $this->helpful_votes > $this->unhelpful_votes;
    }

    public function getTotalVotesAttribute()
    {
        return $this->helpful_votes + $this->unhelpful_votes;
    }

    public function getHelpfulnessRatioAttribute()
    {
        if ($this->total_votes === 0) {
            return 0;
        }
        return round(($this->helpful_votes / $this->total_votes) * 100, 1);
    }

    public function getFormattedCreatedAtAttribute()
    {
        return $this->created_at->format('d/m/Y');
    }

    public function getExcerptAttribute()
    {
        return \Str::limit($this->content, 100);
    }

    public function getHasImagesAttribute()
    {
        return !empty($this->images);
    }

    public function getImageUrlsAttribute()
    {
        if (!$this->has_images) {
            return [];
        }

        return collect($this->images)->map(function ($image) {
            return asset('storage/' . $image);
        })->toArray();
    }

    /**
     * Methods
     */
    public function approve()
    {
        $this->update([
            'is_approved' => true,
            'approved_at' => now(),
        ]);
    }

    public function reject()
    {
        $this->update([
            'is_approved' => false,
            'approved_at' => null,
        ]);
    }

    public function feature()
    {
        $this->update(['is_featured' => true]);
    }

    public function unfeature()
    {
        $this->update(['is_featured' => false]);
    }

    public function markAsVerifiedPurchase()
    {
        $this->update(['is_verified_purchase' => true]);
    }

    public function hasUserVoted($userId)
    {
        return $this->votes()->where('user_id', $userId)->exists();
    }

    public function getUserVoteType($userId)
    {
        $vote = $this->votes()->where('user_id', $userId)->first();
        return $vote ? $vote->vote_type : null;
    }

    public function addHelpfulVote($userId)
    {
        $existingVote = $this->votes()->where('user_id', $userId)->first();

        if ($existingVote) {
            if ($existingVote->vote_type === 'unhelpful') {
                $existingVote->update(['vote_type' => 'helpful']);
                $this->decrement('unhelpful_votes');
                $this->increment('helpful_votes');
            }
        } else {
            $this->votes()->create([
                'user_id' => $userId,
                'vote_type' => 'helpful',
            ]);
            $this->increment('helpful_votes');
        }
    }

    public function addUnhelpfulVote($userId)
    {
        $existingVote = $this->votes()->where('user_id', $userId)->first();

        if ($existingVote) {
            if ($existingVote->vote_type === 'helpful') {
                $existingVote->update(['vote_type' => 'unhelpful']);
                $this->decrement('helpful_votes');
                $this->increment('unhelpful_votes');
            }
        } else {
            $this->votes()->create([
                'user_id' => $userId,
                'vote_type' => 'unhelpful',
            ]);
            $this->increment('unhelpful_votes');
        }
    }

    public function removeVote($userId)
    {
        $vote = $this->votes()->where('user_id', $userId)->first();

        if ($vote) {
            if ($vote->vote_type === 'helpful') {
                $this->decrement('helpful_votes');
            } else {
                $this->decrement('unhelpful_votes');
            }
            $vote->delete();
        }
    }

    /**
     * Static methods
     */
    public static function averageRatingFor($cameraLensId)
    {
        return static::approved()
                    ->where('camera_lens_id', $cameraLensId)
                    ->avg('rating') ?: 0;
    }

    public static function countFor($cameraLensId)
    {
        return static::approved()
                    ->where('camera_lens_id', $cameraLensId)
                    ->count();
    }

    public static function ratingDistributionFor($cameraLensId)
    {
        $distribution = [];
        
        for ($i = 1; $i <= 5; $i++) {
            $count = static::approved()
                          ->where('camera_lens_id', $cameraLensId)
                          ->where('rating', $i)
                          ->count();
            $distribution[$i] = $count;
        }

        return $distribution;
    }
}