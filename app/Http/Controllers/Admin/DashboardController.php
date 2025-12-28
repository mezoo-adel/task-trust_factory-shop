<?php

namespace App\Http\Controllers\Admin;

use App\Enums\OrderStatusEnum;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        // Today's statistics
        $todayStart = now()->startOfDay();
        $todayEnd = now()->endOfDay();

        $todayOrders = Order::whereBetween('created_at', [$todayStart, $todayEnd])->count();
        $todayRevenue = Order::whereBetween('created_at', [$todayStart, $todayEnd])
            ->where('status', OrderStatusEnum::DELIVERED)
            ->sum('total');

        // This week's statistics
        $weekStart = now()->startOfWeek();
        $weekOrders = Order::whereBetween('created_at', [$weekStart, now()])->count();

        // This month's statistics
        $monthStart = now()->startOfMonth();
        $monthOrders = Order::whereBetween('created_at', [$monthStart, now()])->count();

        // Total delivered revenue
        $totalRevenue = Order::where('status', OrderStatusEnum::DELIVERED)->sum('total');

        // Total users (non-admins)
        $totalUsers = User::where('is_admin', false)->count();

        // Total products
        $totalProducts = Product::count();

        // Low stock products
        $lowStockProducts = Product::whereRaw('stock_quantity <= stock_threshold')
            ->where('is_active', true)
            ->count();

        // Orders by status
        $ordersByStatus = [
            'paid' => Order::where('status', OrderStatusEnum::PAID)->count(),
            'processing' => Order::where('status', OrderStatusEnum::PROCESSING)->count(),
            'shipped' => Order::where('status', OrderStatusEnum::SHIPPED)->count(),
            'delivered' => Order::where('status', OrderStatusEnum::DELIVERED)->count(),
            'cancelled' => Order::where('status', OrderStatusEnum::CANCELLED)->count(),
        ];

        // Recent orders (last 10)
        $recentOrders = Order::with(['user', 'items'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($order) {
                return [
                    'id' => $order->id,
                    'uuid' => $order->uuid,
                    'user_name' => $order->user->name,
                    'user_email' => $order->user->email,
                    'status' => $order->status->value,
                    'total' => $order->total,
                    'items_count' => $order->items->count(),
                    'created_at' => $order->created_at->format('M d, Y H:i'),
                ];
            });

        // Low stock products details
        $lowStockProductsList = Product::whereRaw('stock_quantity <= stock_threshold')
            ->where('is_active', true)
            ->orderBy('stock_quantity', 'asc')
            ->limit(10)
            ->get()
            ->map(function ($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'stock_quantity' => $product->stock_quantity,
                    'stock_threshold' => $product->stock_threshold,
                    'price' => $product->price,
                ];
            });

        return Inertia::render('Admin/Dashboard', [
            'stats' => [
                'today_orders' => $todayOrders,
                'today_revenue' => $todayRevenue,
                'week_orders' => $weekOrders,
                'month_orders' => $monthOrders,
                'total_revenue' => $totalRevenue,
                'total_users' => $totalUsers,
                'total_products' => $totalProducts,
                'low_stock_count' => $lowStockProducts,
            ],
            'orders_by_status' => $ordersByStatus,
            'recent_orders' => $recentOrders,
            'low_stock_products' => $lowStockProductsList,
        ]);
    }
}
