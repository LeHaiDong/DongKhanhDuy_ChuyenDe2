<?php

namespace App\Http\Controllers;

use App\Models\Review;
use App\Models\CameraLens;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ReviewController extends Controller
{
    /**
     * Store a new review
     */
    public function store(Request $request, CameraLens $cameraLens)
    {
        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'title' => 'nullable|string|max:255',
            'content' => 'required|string|min:10|max:2000',
            'pros' => 'nullable|array|max:5',
            'pros.*' => 'string|max:100',
            'cons' => 'nullable|array|max:5',
            'cons.*' => 'string|max:100',
            'images' => 'nullable|array|max:5',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
            'order_id' => 'nullable|exists:orders,id',
        ]);

        // Check if user already reviewed this product
        $existingReview = Review::where('user_id', Auth::id())
                               ->where('camera_lens_id', $cameraLens->id)
                               ->first();

        if ($existingReview) {
            return back()->withErrors(['review' => 'Bạn đã đánh giá sản phẩm này rồi.']);
        }

        // Check if this is a verified purchase
        $isVerifiedPurchase = false;
        if ($request->order_id) {
            $order = Order::where('id', $request->order_id)
                         ->where('user_id', Auth::id())
                         ->where('status', 'delivered')
                         ->whereHas('items', function($query) use ($cameraLens) {
                             $query->where('camera_lens_id', $cameraLens->id);
                         })
                         ->first();
            
            $isVerifiedPurchase = $order !== null;
        }

        // Handle image uploads
        $imagePaths = [];
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $path = $image->store('reviews', 'public');
                $imagePaths[] = $path;
            }
        }

        // Create review
        $review = Review::create([
            'user_id' => Auth::id(),
            'camera_lens_id' => $cameraLens->id,
            'order_id' => $request->order_id,
            'rating' => $request->rating,
            'title' => $request->title,
            'content' => $request->content,
            'pros' => $request->pros ? array_filter($request->pros) : null,
            'cons' => $request->cons ? array_filter($request->cons) : null,
            'images' => $imagePaths ?: null,
            'is_verified_purchase' => $isVerifiedPurchase,
            'is_approved' => true, // Auto-approve for now
            'approved_at' => now(),
        ]);

        return back()->with('success', 'Đánh giá của bạn đã được gửi thành công!');
    }

    /**
     * Update an existing review
     */
    public function update(Request $request, Review $review)
    {
        // Check if user owns this review
        if ($review->user_id !== Auth::id()) {
            abort(403, 'Unauthorized');
        }

        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'title' => 'nullable|string|max:255',
            'content' => 'required|string|min:10|max:2000',
            'pros' => 'nullable|array|max:5',
            'pros.*' => 'string|max:100',
            'cons' => 'nullable|array|max:5',
            'cons.*' => 'string|max:100',
            'images' => 'nullable|array|max:5',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
            'remove_images' => 'nullable|array',
            'remove_images.*' => 'string',
        ]);

        // Handle image uploads
        $currentImages = $review->images ?: [];
        
        // Remove specified images
        if ($request->remove_images) {
            foreach ($request->remove_images as $imageToRemove) {
                if (in_array($imageToRemove, $currentImages)) {
                    Storage::disk('public')->delete($imageToRemove);
                    $currentImages = array_values(array_diff($currentImages, [$imageToRemove]));
                }
            }
        }

        // Add new images
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                if (count($currentImages) < 5) {
                    $path = $image->store('reviews', 'public');
                    $currentImages[] = $path;
                }
            }
        }

        // Update review
        $review->update([
            'rating' => $request->rating,
            'title' => $request->title,
            'content' => $request->content,
            'pros' => $request->pros ? array_filter($request->pros) : null,
            'cons' => $request->cons ? array_filter($request->cons) : null,
            'images' => $currentImages ?: null,
            'is_approved' => true, // Re-approve after edit
            'approved_at' => now(),
        ]);

        return back()->with('success', 'Đánh giá đã được cập nhật thành công!');
    }

    /**
     * Delete a review
     */
    public function destroy(Review $review)
    {
        // Check if user owns this review or is admin
        if ($review->user_id !== Auth::id() && !Auth::user()->isAdmin()) {
            abort(403, 'Unauthorized');
        }

        // Delete review images
        if ($review->images) {
            foreach ($review->images as $image) {
                Storage::disk('public')->delete($image);
            }
        }

        $review->delete();

        return back()->with('success', 'Đánh giá đã được xóa thành công!');
    }

    /**
     * Vote helpful on a review
     */
    public function voteHelpful(Review $review)
    {
        if (!Auth::check()) {
            return response()->json(['error' => 'Bạn cần đăng nhập để vote'], 401);
        }

        if ($review->user_id === Auth::id()) {
            return response()->json(['error' => 'Không thể vote cho đánh giá của chính mình'], 403);
        }

        $currentVote = $review->getUserVoteType(Auth::id());

        if ($currentVote === 'helpful') {
            // Remove vote
            $review->removeVote(Auth::id());
            $action = 'removed';
        } else {
            // Add or change vote
            $review->addHelpfulVote(Auth::id());
            $action = 'voted_helpful';
        }

        return response()->json([
            'success' => true,
            'action' => $action,
            'helpful_votes' => $review->fresh()->helpful_votes,
            'unhelpful_votes' => $review->fresh()->unhelpful_votes,
            'user_vote' => $review->fresh()->getUserVoteType(Auth::id()),
        ]);
    }

    /**
     * Vote unhelpful on a review
     */
    public function voteUnhelpful(Review $review)
    {
        if (!Auth::check()) {
            return response()->json(['error' => 'Bạn cần đăng nhập để vote'], 401);
        }

        if ($review->user_id === Auth::id()) {
            return response()->json(['error' => 'Không thể vote cho đánh giá của chính mình'], 403);
        }

        $currentVote = $review->getUserVoteType(Auth::id());

        if ($currentVote === 'unhelpful') {
            // Remove vote
            $review->removeVote(Auth::id());
            $action = 'removed';
        } else {
            // Add or change vote
            $review->addUnhelpfulVote(Auth::id());
            $action = 'voted_unhelpful';
        }

        return response()->json([
            'success' => true,
            'action' => $action,
            'helpful_votes' => $review->fresh()->helpful_votes,
            'unhelpful_votes' => $review->fresh()->unhelpful_votes,
            'user_vote' => $review->fresh()->getUserVoteType(Auth::id()),
        ]);
    }

    /**
     * Get reviews for a specific product (AJAX)
     */
    public function getReviews(Request $request, CameraLens $cameraLens)
    {
        $query = $cameraLens->approvedReviews()->with('user');

        // Filter by rating
        if ($request->filled('rating')) {
            $query->where('rating', $request->rating);
        }

        // Filter by verified purchase
        if ($request->filled('verified')) {
            $query->where('is_verified_purchase', true);
        }

        // Filter by images
        if ($request->filled('with_images')) {
            $query->whereNotNull('images')->where('images', '!=', '[]');
        }

        // Sort
        $sortBy = $request->get('sort', 'helpful');
        switch ($sortBy) {
            case 'newest':
                $query->orderBy('created_at', 'desc');
                break;
            case 'oldest':
                $query->orderBy('created_at', 'asc');
                break;
            case 'rating_high':
                $query->orderBy('rating', 'desc');
                break;
            case 'rating_low':
                $query->orderBy('rating', 'asc');
                break;
            case 'helpful':
            default:
                $query->orderByRaw('(helpful_votes - unhelpful_votes) DESC');
                break;
        }

        $reviews = $query->paginate(10);

        return response()->json([
            'reviews' => $reviews->items(),
            'pagination' => [
                'current_page' => $reviews->currentPage(),
                'last_page' => $reviews->lastPage(),
                'per_page' => $reviews->perPage(),
                'total' => $reviews->total(),
            ]
        ]);
    }

    /**
     * Show review form for a specific product
     */
    public function create(CameraLens $cameraLens)
    {
        if (!Auth::check()) {
            return redirect()->route('customer.login')
                           ->with('message', 'Bạn cần đăng nhập để đánh giá sản phẩm');
        }

        // Check if user already reviewed this product
        $existingReview = Review::where('user_id', Auth::id())
                               ->where('camera_lens_id', $cameraLens->id)
                               ->first();

        if ($existingReview) {
            return redirect()->route('products.show', $cameraLens)
                           ->with('info', 'Bạn đã đánh giá sản phẩm này rồi.');
        }

        // Get user's orders that contain this product and are delivered
        $orders = Order::where('user_id', Auth::id())
                      ->where('status', 'delivered')
                      ->whereHas('items', function($query) use ($cameraLens) {
                          $query->where('camera_lens_id', $cameraLens->id);
                      })
                      ->get();

        return view('reviews.create', compact('cameraLens', 'orders'));
    }

    /**
     * Show edit form for a review
     */
    public function edit(Review $review)
    {
        if ($review->user_id !== Auth::id()) {
            abort(403, 'Unauthorized');
        }

        return view('reviews.edit', compact('review'));
    }
}