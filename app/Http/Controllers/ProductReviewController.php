<?php

namespace App\Http\Controllers;

use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProductReviewController extends Controller
{
    public function store(Request $request, Product $product)
    {
        $data = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'title' => 'nullable|string|max:120',
            'content' => 'required|string|min:5|max:1000',
        ]);

        $verifiedPurchase = OrderItem::where('camera_lens_id', $product->id)
            ->whereHas('order', fn ($query) => $query->where('user_id', Auth::id()))
            ->exists();

        Review::updateOrCreate(
            [
                'user_id' => Auth::id(),
                'camera_lens_id' => $product->id,
            ],
            [
                'rating' => $data['rating'],
                'title' => $data['title'] ?? null,
                'content' => $data['content'],
                'is_verified_purchase' => $verifiedPurchase,
                'is_approved' => true,
                'approved_at' => now(),
            ]
        );

        return back()->with('success', 'Cảm ơn bạn, đánh giá sản phẩm đã được lưu.');
    }
}
