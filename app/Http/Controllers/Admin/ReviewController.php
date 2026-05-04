<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Review;
use App\Models\CameraLens;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class ReviewController extends Controller
{
    /**
     * Display a listing of reviews
     */
    public function index(Request $request)
    {
        $query = Review::with(['user', 'cameraLens']);

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('content', 'like', "%{$search}%")
                  ->orWhereHas('user', function($userQ) use ($search) {
                      $userQ->where('name', 'like', "%{$search}%");
                  });
            });
        }

        // Filter by rating
        if ($request->filled('rating')) {
            $query->where('rating', $request->rating);
        }

        // Filter by approval status
        if ($request->filled('approval_status')) {
            if ($request->approval_status === 'approved') {
                $query->where('is_approved', true);
            } elseif ($request->approval_status === 'pending') {
                $query->where('is_approved', false);
            }
        }

        // Sort
        $sortBy = $request->get('sort', 'created_at');
        $sortOrder = $request->get('order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $reviews = $query->paginate(20);

        // Get statistics
        $stats = $this->getReviewStats();

        return view('admin.reviews.index', compact('reviews', 'stats'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Review $review)
    {
        // Delete review images
        if ($review->images) {
            foreach ($review->images as $image) {
                Storage::disk('public')->delete($image);
            }
        }

        $review->delete();

        return redirect()->route('admin.reviews.index')
                        ->with('success', 'Đánh giá đã được xóa thành công!');
    }

    /**
     * Approve a review
     */
    public function approve(Review $review)
    {
        $review->approve();
        return back()->with('success', 'Đánh giá đã được phê duyệt!');
    }

    /**
     * Reject a review
     */
    public function reject(Review $review)
    {
        $review->reject();
        return back()->with('success', 'Đánh giá đã bị từ chối!');
    }

    /**
     * Feature a review
     */
    public function feature(Review $review)
    {
        $review->feature();
        return back()->with('success', 'Đánh giá đã được đánh dấu nổi bật!');
    }

    /**
     * Get review statistics
     */
    private function getReviewStats()
    {
        $today = Carbon::today();
        
        return [
            'total_reviews' => Review::count(),
            'approved_reviews' => Review::where('is_approved', true)->count(),
            'pending_reviews' => Review::where('is_approved', false)->count(),
            'featured_reviews' => Review::where('is_featured', true)->count(),
            'verified_reviews' => Review::where('is_verified_purchase', true)->count(),
            'today_reviews' => Review::whereDate('created_at', $today)->count(),
            'average_rating' => Review::approved()->avg('rating') ?: 0,
        ];
    }
}
