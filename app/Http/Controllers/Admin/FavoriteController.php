<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CameraLens;
use App\Models\Favorite;
use App\Models\User;
use Illuminate\Http\Request;

class FavoriteController extends Controller
{
    public function index(Request $request)
    {
        $timeRange = $request->get('time_range', '30_days');
        $dateRange = $this->getDateRange($timeRange);

        $favoriteProducts = CameraLens::query()
            ->whereHas('favorites', function ($query) use ($dateRange) {
                if ($dateRange) {
                    $query->whereBetween('created_at', $dateRange);
                }
            })
            ->withCount(['favorites' => function ($query) use ($dateRange) {
                if ($dateRange) {
                    $query->whereBetween('created_at', $dateRange);
                }
            }])
            ->orderByDesc('favorites_count')
            ->paginate(12, ['*'], 'products_page');

        $recentFavorites = Favorite::query()
            ->with(['user', 'cameraLens'])
            ->whereHas('user')
            ->whereHas('cameraLens')
            ->when($dateRange, function ($query) use ($dateRange) {
                $query->whereBetween('created_at', $dateRange);
            })
            ->latest()
            ->paginate(12, ['*'], 'recent_page');

        $stats = $this->getFavoriteStats($dateRange);
        $topUsers = $this->getTopUsers($dateRange);
        $brandStats = $this->getBrandFavoriteStats($dateRange);

        return view('admin.favorites.index', compact(
            'favoriteProducts',
            'recentFavorites',
            'stats',
            'topUsers',
            'brandStats',
            'timeRange'
        ));
    }

    public function destroy(Favorite $favorite)
    {
        $userName = $favorite->user?->name ?? 'người dùng';
        $productName = $favorite->cameraLens?->name ?? 'sản phẩm';

        $favorite->delete();

        return back()->with('success', "Đã xóa tương tác của {$userName} với {$productName}.");
    }

    public function analytics(Request $request)
    {
        $timeRange = $request->get('time_range', '30_days');
        $dateRange = $this->getDateRange($timeRange);
        $days = match ($timeRange) {
            '7_days' => 7,
            '30_days' => 30,
            default => 90,
        };

        $trendData = [];

        for ($i = $days - 1; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $trendData[] = [
                'date' => $date->format('d/m'),
                'count' => Favorite::whereDate('created_at', $date->toDateString())->count(),
            ];
        }

        return response()->json([
            'trend_data' => $trendData,
            'stats' => $this->getFavoriteStats($dateRange),
            'brand_stats' => $this->getBrandFavoriteStats($dateRange),
        ]);
    }

    public function export(Request $request)
    {
        $timeRange = $request->get('time_range', 'all_time');
        $dateRange = $this->getDateRange($timeRange);

        $favorites = Favorite::query()
            ->with(['user', 'cameraLens'])
            ->whereHas('user')
            ->whereHas('cameraLens')
            ->when($dateRange, function ($query) use ($dateRange) {
                $query->whereBetween('created_at', $dateRange);
            })
            ->latest()
            ->get();

        $rows = [];
        $rows[] = ['Người dùng', 'Email', 'Sản phẩm', 'Thương hiệu', 'Giá', 'Ngày tương tác'];

        foreach ($favorites as $favorite) {
            $rows[] = [
                $favorite->user?->name,
                $favorite->user?->email,
                $favorite->cameraLens?->name,
                $favorite->cameraLens?->brand,
                $favorite->cameraLens?->formatted_price,
                optional($favorite->created_at)->format('d/m/Y H:i'),
            ];
        }

        $filename = 'tuong_tac_san_pham_' . now()->format('Y_m_d_H_i_s') . '.csv';

        return response()->stream(function () use ($rows) {
            $file = fopen('php://output', 'w');

            foreach ($rows as $row) {
                fputcsv($file, $row);
            }

            fclose($file);
        }, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    private function getTopUsers($dateRange)
    {
        return User::query()
            ->whereHas('favorites', function ($query) use ($dateRange) {
                if ($dateRange) {
                    $query->whereBetween('created_at', $dateRange);
                }
            })
            ->withCount(['favorites' => function ($query) use ($dateRange) {
                if ($dateRange) {
                    $query->whereBetween('created_at', $dateRange);
                }
            }])
            ->orderByDesc('favorites_count')
            ->take(8)
            ->get();
    }

    private function getDateRange($timeRange): ?array
    {
        return match ($timeRange) {
            '7_days' => [now()->subDays(7), now()],
            '30_days' => [now()->subDays(30), now()],
            '3_months' => [now()->subMonths(3), now()],
            '6_months' => [now()->subMonths(6), now()],
            '1_year' => [now()->subYear(), now()],
            default => null,
        };
    }

    private function getFavoriteStats(?array $dateRange): array
    {
        $query = Favorite::query();

        if ($dateRange) {
            $query->whereBetween('created_at', $dateRange);
        }

        $totalFavorites = $query->count();
        $uniqueProducts = (clone $query)->distinct('camera_lens_id')->count('camera_lens_id');
        $uniqueUsers = (clone $query)->distinct('user_id')->count('user_id');

        $previousTotal = 0;

        if ($dateRange) {
            $periodLength = $dateRange[0]->diffInDays($dateRange[1]);
            $previousStart = $dateRange[0]->copy()->subDays($periodLength);
            $previousEnd = $dateRange[0]->copy();

            $previousTotal = Favorite::whereBetween('created_at', [$previousStart, $previousEnd])->count();
        }

        $growth = $previousTotal > 0
            ? round((($totalFavorites - $previousTotal) / $previousTotal) * 100, 1)
            : 0;

        return [
            'total_favorites' => $totalFavorites,
            'unique_products' => $uniqueProducts,
            'unique_users' => $uniqueUsers,
            'growth_percentage' => $growth,
            'average_per_user' => $uniqueUsers > 0 ? round($totalFavorites / $uniqueUsers, 1) : 0,
            'today_favorites' => Favorite::whereDate('created_at', today())->count(),
        ];
    }

    private function getBrandFavoriteStats(?array $dateRange)
    {
        return Favorite::query()
            ->with('cameraLens')
            ->whereHas('cameraLens')
            ->when($dateRange, function ($query) use ($dateRange) {
                $query->whereBetween('created_at', $dateRange);
            })
            ->get()
            ->filter(fn ($favorite) => filled($favorite->cameraLens?->brand))
            ->groupBy(fn ($favorite) => $favorite->cameraLens->brand)
            ->map(function ($favorites, $brand) {
                return [
                    'brand' => $brand,
                    'count' => $favorites->count(),
                    'unique_products' => $favorites->pluck('camera_lens_id')->unique()->count(),
                ];
            })
            ->sortByDesc('count')
            ->take(8)
            ->values();
    }
}
