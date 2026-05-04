<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\CameraLens;
use App\Models\Favorite;
use App\Models\Order;
use App\Models\OrderItem;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Display the admin dashboard
     */
    public function index()
    {
        $stats = $this->getDashboardStats();
        $salesStats = $this->getSalesStats();

        return view('admin.dashboard', compact('stats', 'salesStats'));
    }

    /**
     * Get main dashboard statistics
     */
    private function getDashboardStats()
    {
        $now = now();
        
        return [
            // Users stats
            'total_users' => User::count(),
            'total_customers' => User::where('is_admin', false)->count(),
            'total_admins' => User::where('is_admin', true)->count(),
            'new_users_today' => User::whereDate('created_at', $now->toDateString())->count(),
            'new_users_this_month' => User::whereMonth('created_at', $now->month)
                                         ->whereYear('created_at', $now->year)
                                         ->count(),

            // Products stats
            'total_products' => CameraLens::count(),
            'active_products' => CameraLens::where('is_active', true)->count(),
            'out_of_stock' => CameraLens::where('stock_quantity', 0)->count(),

            // Engagement stats
            'total_favorites' => Favorite::count(),

            // Popular brands
            'popular_brands' => CameraLens::selectRaw('brand, COUNT(*) as count')
                                        ->groupBy('brand')
                                        ->orderByDesc('count')
                                        ->take(5)
                                        ->get(),
        ];
    }

    /**
     * Get sales statistics
     */
    private function getSalesStats()
    {
        $today = Carbon::today();
        $yesterday = Carbon::yesterday();
        $thisMonth = Carbon::now()->startOfMonth();
        $lastMonth = Carbon::now()->subMonth()->startOfMonth();
        $thisYear = Carbon::now()->startOfYear();

        $todayRevenue = Order::whereDate('created_at', $today)
                           ->where('payment_status', 'paid')
                           ->sum('total_amount');

        $yesterdayRevenue = Order::whereDate('created_at', $yesterday)
                                ->where('payment_status', 'paid')
                                ->sum('total_amount');

        $thisMonthRevenue = Order::where('created_at', '>=', $thisMonth)
                                ->where('payment_status', 'paid')
                                ->sum('total_amount');

        $lastMonthRevenue = Order::whereBetween('created_at', [$lastMonth, $thisMonth])
                                ->where('payment_status', 'paid')
                                ->sum('total_amount');

        $thisYearRevenue = Order::where('created_at', '>=', $thisYear)
                               ->where('payment_status', 'paid')
                               ->sum('total_amount');

        return [
            'today_revenue' => $todayRevenue,
            'yesterday_revenue' => $yesterdayRevenue,
            'today_growth' => $yesterdayRevenue > 0 ? round((($todayRevenue - $yesterdayRevenue) / $yesterdayRevenue) * 100, 1) : 0,
            
            'this_month_revenue' => $thisMonthRevenue,
            'last_month_revenue' => $lastMonthRevenue,
            'month_growth' => $lastMonthRevenue > 0 ? round((($thisMonthRevenue - $lastMonthRevenue) / $lastMonthRevenue) * 100, 1) : 0,
            
            'this_year_revenue' => $thisYearRevenue,
            
            'total_orders' => Order::count(),
            'pending_orders' => Order::where('status', 'pending')->count(),
            'completed_orders' => Order::where('status', 'delivered')->count(),
            'cancelled_orders' => Order::where('status', 'cancelled')->count(),
            
            'average_order_value' => Order::where('payment_status', 'paid')->avg('total_amount') ?: 0,
            'conversion_rate' => $this->getConversionRate(),
            
            'top_selling_products' => $this->getTopSellingProducts(),
            'low_stock_products' => CameraLens::where('stock_quantity', '<=', 5)->where('stock_quantity', '>', 0)->count(),
        ];
    }

    /**
     * Get sales charts data
     */
    private function getSalesCharts()
    {
        return [
            'revenue_trend' => $this->getRevenueTrend(),
            'orders_trend' => $this->getOrdersTrend(),
            'top_products' => $this->getTopProductsChart(),
            'sales_by_status' => $this->getSalesByStatus(),
        ];
    }

    /**
     * Get revenue trend for last 30 days
     */
    private function getRevenueTrend()
    {
        $data = [];
        
        for ($i = 29; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $revenue = Order::whereDate('created_at', $date)
                          ->where('payment_status', 'paid')
                          ->sum('total_amount');
            
            $data[] = [
                'date' => $date->format('d/m'),
                'revenue' => $revenue / 1000000, // Convert to millions
            ];
        }

        return $data;
    }

    /**
     * Get orders trend for last 30 days
     */
    private function getOrdersTrend()
    {
        $data = [];
        
        for ($i = 29; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $orders = Order::whereDate('created_at', $date)->count();
            
            $data[] = [
                'date' => $date->format('d/m'),
                'orders' => $orders,
            ];
        }

        return $data;
    }

    /**
     * Get top selling products
     */
    private function getTopSellingProducts()
    {
        return OrderItem::with('cameraLens')
                       ->selectRaw('camera_lens_id, SUM(quantity) as total_sold, SUM(final_price) as total_revenue')
                       ->groupBy('camera_lens_id')
                       ->orderByDesc('total_sold')
                       ->take(5)
                       ->get();
    }

    /**
     * Get top products for chart
     */
    private function getTopProductsChart()
    {
        $products = $this->getTopSellingProducts();
        
        return $products->map(function($item) {
            return [
                'name' => $item->cameraLens ? substr($item->cameraLens->name, 0, 20) . '...' : 'N/A',
                'sold' => $item->total_sold,
                'revenue' => $item->total_revenue / 1000000, // Convert to millions
            ];
        });
    }

    /**
     * Get sales by order status
     */
    private function getSalesByStatus()
    {
        $statuses = ['pending', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled'];
        $data = [];

        foreach ($statuses as $status) {
            $count = Order::where('status', $status)->count();
            $data[] = [
                'status' => ucfirst($status),
                'count' => $count,
            ];
        }

        return $data;
    }

    /**
     * Calculate conversion rate
     */
    private function getConversionRate()
    {
        $totalVisitors = 1000; // This would come from analytics
        $totalOrders = Order::count();
        
        return $totalVisitors > 0 ? round(($totalOrders / $totalVisitors) * 100, 2) : 0;
    }
}
