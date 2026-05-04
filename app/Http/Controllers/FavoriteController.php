<?php

namespace App\Http\Controllers;

use App\Models\CameraLens;
use App\Models\Favorite;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FavoriteController extends Controller
{
    /**
     * Display a listing of user's favorite products.
     */
    public function index()
    {
        $user = Auth::user();
        $favorites = $user->favoriteCameraLenses()->with('favorites')->paginate(12);

        return view('favorites.index', compact('favorites'));
    }

    /**
     * Toggle favorite status for a camera lens.
     */
    public function toggle(Request $request, CameraLens $cameraLens)
    {
        // Debug logging
        \Log::info('Favorite toggle request', [
            'user_id' => Auth::id(),
            'lens_id' => $cameraLens->id,
            'request_data' => $request->all(),
            'is_ajax' => $request->ajax(),
            'headers' => $request->headers->all(),
            'route_name' => $request->route()->getName()
        ]);
        
        // Since we have auth middleware, this check is redundant but keep for safety
        if (!Auth::check()) {
            \Log::warning('Unauthenticated favorites toggle request', [
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vui lòng đăng nhập để sử dụng tính năng yêu thích.',
                    'redirect' => route('auth.customer.login')
                ], 401);
            }
            
            return redirect()->route('auth.customer.login')
                ->with('warning', 'Vui lòng đăng nhập để sử dụng tính năng yêu thích.');
        }

        try {
            $user = Auth::user();
            $isFavorited = $user->toggleFavorite($cameraLens->id);

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'is_favorited' => $isFavorited,
                    'message' => $isFavorited 
                        ? 'Đã thêm vào danh sách yêu thích!' 
                        : 'Đã xóa khỏi danh sách yêu thích!',
                    'favorites_count' => $cameraLens->fresh()->favorites_count
                ]);
            }

            $message = $isFavorited 
                ? 'Đã thêm "' . $cameraLens->name . '" vào danh sách yêu thích!'
                : 'Đã xóa "' . $cameraLens->name . '" khỏi danh sách yêu thích!';

            return back()->with('success', $message);
            
        } catch (\Exception $e) {
            \Log::error('Favorite toggle error', [
                'user_id' => Auth::id(),
                'lens_id' => $cameraLens->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Có lỗi xảy ra khi cập nhật yêu thích: ' . $e->getMessage()
                ], 500);
            }
            
            return back()->with('error', 'Có lỗi xảy ra khi cập nhật yêu thích.');
        }
    }

    /**
     * Remove a specific favorite.
     */
    public function destroy(CameraLens $cameraLens)
    {
        $user = Auth::user();
        $favorite = $user->favorites()->where('camera_lens_id', $cameraLens->id)->first();

        if ($favorite) {
            $favorite->delete();
            return back()->with('success', 'Đã xóa "' . $cameraLens->name . '" khỏi danh sách yêu thích!');
        }

        return back()->with('error', 'Sản phẩm không có trong danh sách yêu thích của bạn.');
    }

    /**
     * Clear all favorites for the authenticated user.
     */
    public function clear()
    {
        $user = Auth::user();
        $count = $user->favorites()->count();
        
        if ($count > 0) {
            $user->favorites()->delete();
            return back()->with('success', "Đã xóa tất cả {$count} sản phẩm khỏi danh sách yêu thích!");
        }

        return back()->with('info', 'Danh sách yêu thích của bạn đã trống.');
    }

    /**
     * Get favorites count for the authenticated user.
     */
    public function count()
    {
        if (!Auth::check()) {
            return response()->json(['count' => 0]);
        }

        $count = Auth::user()->favorites()->count();
        return response()->json(['count' => $count]);
    }
}