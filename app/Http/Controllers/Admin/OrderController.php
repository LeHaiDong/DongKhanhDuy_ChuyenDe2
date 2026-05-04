<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use App\Models\CameraLens;
use Illuminate\Http\Request;
use Carbon\Carbon;

class OrderController extends Controller
{
    /**
     * Display a listing of orders
     */
    public function index(Request $request)
    {
        $query = Order::with(['user', 'items.cameraLens'])
                     ->withCount('items');

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                  ->orWhere('shipping_name', 'like', "%{$search}%")
                  ->orWhere('shipping_phone', 'like', "%{$search}%")
                  ->orWhereHas('user', function($userQ) use ($search) {
                      $userQ->where('name', 'like', "%{$search}%")
                           ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by payment status
        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        // Sort
        $sortBy = $request->get('sort', 'created_at');
        $sortOrder = $request->get('order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $orders = $query->paginate(20);

        // Get statistics
        $stats = $this->getOrderStats();

        return view('admin.orders.index', compact('orders', 'stats'));
    }

    /**
     * Display the specified order
     */
    public function show(Order $order)
    {
        $order->load(['user', 'items.cameraLens']);
        
        // Order timeline
        $timeline = $this->getOrderTimeline($order);
        
        return view('admin.orders.show', compact('order', 'timeline'));
    }

    /**
     * Confirm order
     */
    public function confirm(Order $order)
    {
        if ($order->confirm()) {
            // Reduce stock when order is confirmed
            $order->reduceStock();
            
            return back()->with('success', 'Đơn hàng đã được xác nhận thành công!');
        }

        return back()->withErrors(['action' => 'Không thể xác nhận đơn hàng này.']);
    }

    /**
     * Ship order
     */
    public function ship(Request $request, Order $order)
    {
        $request->validate([
            'tracking_number' => 'nullable|string|max:255',
            'carrier' => 'nullable|string|max:255',
        ]);

        $trackingInfo = [];
        if ($request->tracking_number) {
            $trackingInfo['tracking_number'] = $request->tracking_number;
        }
        if ($request->carrier) {
            $trackingInfo['carrier'] = $request->carrier;
        }

        if ($order->ship($trackingInfo)) {
            return back()->with('success', 'Đơn hàng đã được gửi đi thành công!');
        }

        return back()->withErrors(['action' => 'Không thể gửi đơn hàng này.']);
    }

    /**
     * Deliver order
     */
    public function deliver(Order $order)
    {
        if ($order->deliver()) {
            return back()->with('success', 'Đơn hàng đã được giao thành công!');
        }

        return back()->withErrors(['action' => 'Không thể đánh dấu đơn hàng là đã giao.']);
    }

    /**
     * Cancel order
     */
    public function cancel(Request $request, Order $order)
    {
        $request->validate([
            'cancel_reason' => 'required|string|max:500'
        ]);

        if ($order->cancel($request->cancel_reason)) {
            return back()->with('success', 'Đơn hàng đã được hủy thành công!');
        }

        return back()->withErrors(['action' => 'Không thể hủy đơn hàng này.']);
    }

    /**
     * Mark payment as paid
     */
    public function markAsPaid(Request $request, Order $order)
    {
        $request->validate([
            'payment_reference' => 'nullable|string|max:255'
        ]);

        $order->markAsPaid($request->payment_reference);

        return back()->with('success', 'Đơn hàng đã được đánh dấu là đã thanh toán!');
    }

    /**
     * Get order statistics
     */
    private function getOrderStats()
    {
        $today = Carbon::today();
        
        return [
            'total_orders' => Order::count(),
            'pending_orders' => Order::where('status', 'pending')->count(),
            'processing_orders' => Order::whereIn('status', ['confirmed', 'processing'])->count(),
            'delivered_orders' => Order::where('status', 'delivered')->count(),
            'today_orders' => Order::whereDate('created_at', $today)->count(),
            'today_revenue' => Order::whereDate('created_at', $today)->where('payment_status', 'paid')->sum('total_amount'),
            'unpaid_orders' => Order::where('payment_status', 'pending')->count(),
            'average_order_value' => Order::where('payment_status', 'paid')->avg('total_amount') ?: 0,
        ];
    }

    /**
     * Get order timeline
     */
    private function getOrderTimeline(Order $order)
    {
        $timeline = [];

        $timeline[] = [
            'event' => 'order_created',
            'title' => 'Đơn hàng được tạo',
            'description' => "Đơn hàng {$order->order_number} được tạo",
            'timestamp' => $order->created_at,
            'icon' => 'fas fa-plus-circle',
            'color' => 'primary'
        ];

        if ($order->confirmed_at) {
            $timeline[] = [
                'event' => 'order_confirmed',
                'title' => 'Đơn hàng được xác nhận',
                'description' => 'Đơn hàng đã được xác nhận và đang chuẩn bị',
                'timestamp' => $order->confirmed_at,
                'icon' => 'fas fa-check-circle',
                'color' => 'success'
            ];
        }

        if ($order->shipped_at) {
            $timeline[] = [
                'event' => 'order_shipped',
                'title' => 'Đơn hàng được gửi đi',
                'description' => 'Đơn hàng đã được gửi đi và đang trên đường giao',
                'timestamp' => $order->shipped_at,
                'icon' => 'fas fa-shipping-fast',
                'color' => 'info'
            ];
        }

        if ($order->delivered_at) {
            $timeline[] = [
                'event' => 'order_delivered',
                'title' => 'Đơn hàng được giao thành công',
                'description' => 'Đơn hàng đã được giao thành công đến khách hàng',
                'timestamp' => $order->delivered_at,
                'icon' => 'fas fa-check-double',
                'color' => 'success'
            ];
        }

        if ($order->cancelled_at) {
            $timeline[] = [
                'event' => 'order_cancelled',
                'title' => 'Đơn hàng bị hủy',
                'description' => 'Đơn hàng đã bị hủy',
                'timestamp' => $order->cancelled_at,
                'icon' => 'fas fa-times-circle',
                'color' => 'danger'
            ];
        }

        return collect($timeline)->sortBy('timestamp');
    }

    /**
     * Not implemented methods for resource controller
     */
    public function create() { return redirect()->route('admin.orders.index'); }
    public function store(Request $request) { return redirect()->route('admin.orders.index'); }
    public function edit($id) { return redirect()->route('admin.orders.index'); }
    public function update(Request $request, $id) { return redirect()->route('admin.orders.index'); }
    public function destroy($id) { return redirect()->route('admin.orders.index'); }
}