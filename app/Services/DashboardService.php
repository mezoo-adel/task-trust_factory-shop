<?php

namespace App\Services;

use App\Enums\OrderStatusEnum;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Carbon\Carbon;

class DashboardService
{
    /**
     * Get dashboard statistics for a specific date range
     *
     * @param Carbon|null $startDate
     * @param Carbon|null $endDate
     * @return array
     */
    public function getStatistics(?Carbon $startDate = null, ?Carbon $endDate = null): array
    {
        $startDate = $startDate ?? now()->startOfDay();
        $endDate = $endDate ?? now()->endOfDay();

        return [
            'orders_count' => $this->getOrdersCount($startDate, $endDate),
            'revenue' => $this->getRevenue($startDate, $endDate),
            'total_users' => $this->getTotalUsers(),
            'total_products' => $this->getTotalProducts(),
            'low_stock_count' => $this->getLowStockCount(),
        ];
    }

    /**
     * Get today's statistics
     *
     * @return array
     */
    public function getTodayStatistics(): array
    {
        $todayStart = now()->startOfDay();
        $todayEnd = now()->endOfDay();

        return [
            'today_orders' => $this->getOrdersCount($todayStart, $todayEnd),
            'today_revenue' => $this->getRevenue($todayStart, $todayEnd),
            'week_orders' => $this->getOrdersCount(now()->startOfWeek(), now()),
            'month_orders' => $this->getOrdersCount(now()->startOfMonth(), now()),
            'total_revenue' => $this->getTotalRevenue(),
            'total_users' => $this->getTotalUsers(),
            'total_products' => $this->getTotalProducts(),
            'low_stock_count' => $this->getLowStockCount(),
        ];
    }

    /**
     * Get daily report data (for email notifications)
     *
     * @param Carbon|null $date
     * @return array
     */
    public function getDailyReport(?Carbon $date = null): array
    {
        $date = $date ?? now()->subDay();
        $startDate = $date->copy()->startOfDay();
        $endDate = $date->copy()->endOfDay();

        $orders = Order::whereBetween('created_at', [$startDate, $endDate])
            ->with(['items.product', 'user'])
            ->get();

        $revenue = $orders->where('status', OrderStatusEnum::DELIVERED)->sum('total');
        $ordersCount = $orders->count();

        // Group orders by status
        $ordersByStatus = [
            'paid' => $orders->where('status', OrderStatusEnum::PAID)->count(),
            'processing' => $orders->where('status', OrderStatusEnum::PROCESSING)->count(),
            'shipped' => $orders->where('status', OrderStatusEnum::SHIPPED)->count(),
            'delivered' => $orders->where('status', OrderStatusEnum::DELIVERED)->count(),
            'cancelled' => $orders->where('status', OrderStatusEnum::CANCELLED)->count(),
        ];

        // Get products sold with quantities
        $productsSold = [];
        foreach ($orders as $order) {
            foreach ($order->items as $item) {
                $productId = $item->product_id;
                if (!isset($productsSold[$productId])) {
                    $productsSold[$productId] = [
                        'name' => $item->product->name ?? 'Unknown Product',
                        'quantity' => 0,
                        'revenue' => 0,
                    ];
                }
                $productsSold[$productId]['quantity'] += $item->quantity;
                $productsSold[$productId]['revenue'] += $item->subtotal;
            }
        }

        // Sort by quantity sold (descending)
        uasort($productsSold, function ($a, $b) {
            return $b['quantity'] <=> $a['quantity'];
        });

        // Get top 10 selling products
        $topSellingProducts = array_slice($productsSold, 0, 10, true);

        return [
            'date' => $date->format('F d, Y'),
            'orders_count' => $ordersCount,
            'revenue' => $revenue,
            'orders_by_status' => $ordersByStatus,
            'products_sold' => $productsSold,
            'top_selling_products' => $topSellingProducts,
            'total_items_sold' => array_sum(array_column($productsSold, 'quantity')),
        ];
    }

    /**
     * Get orders count for a date range
     *
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @return int
     */
    public function getOrdersCount(Carbon $startDate, Carbon $endDate): int
    {
        return Order::whereBetween('created_at', [$startDate, $endDate])->count();
    }

    /**
     * Get revenue for a date range (only delivered orders)
     *
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @return float
     */
    public function getRevenue(Carbon $startDate, Carbon $endDate): float
    {
        return Order::whereBetween('created_at', [$startDate, $endDate])
            ->where('status', OrderStatusEnum::DELIVERED)
            ->sum('total');
    }

    /**
     * Get total revenue (all time, delivered orders only)
     *
     * @return float
     */
    public function getTotalRevenue(): float
    {
        return Order::where('status', OrderStatusEnum::DELIVERED)->sum('total');
    }

    /**
     * Get total users count (non-admins)
     *
     * @return int
     */
    public function getTotalUsers(): int
    {
        return User::where('is_admin', false)->count();
    }

    /**
     * Get total products count
     *
     * @return int
     */
    public function getTotalProducts(): int
    {
        return Product::count();
    }

    /**
     * Get low stock products count
     *
     * @return int
     */
    public function getLowStockCount(): int
    {
        return Product::whereRaw('stock_quantity <= stock_threshold')
            ->where('is_active', true)
            ->count();
    }

    /**
     * Get orders by status
     *
     * @return array
     */
    public function getOrdersByStatus(): array
    {
        return [
            'paid' => Order::where('status', OrderStatusEnum::PAID)->count(),
            'processing' => Order::where('status', OrderStatusEnum::PROCESSING)->count(),
            'shipped' => Order::where('status', OrderStatusEnum::SHIPPED)->count(),
            'delivered' => Order::where('status', OrderStatusEnum::DELIVERED)->count(),
            'cancelled' => Order::where('status', OrderStatusEnum::CANCELLED)->count(),
        ];
    }

    /**
     * Get recent orders
     *
     * @param int $limit
     * @return \Illuminate\Support\Collection
     */
    public function getRecentOrders(int $limit = 10)
    {
        return Order::with(['user', 'items'])
            ->orderBy('created_at', 'desc')
            ->limit($limit)
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
    }

    /**
     * Get low stock products list
     *
     * @param int $limit
     * @return \Illuminate\Support\Collection
     */
    public function getLowStockProducts(int $limit = 10)
    {
        return Product::whereRaw('stock_quantity <= stock_threshold')
            ->where('is_active', true)
            ->orderBy('stock_quantity', 'asc')
            ->limit($limit)
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
    }
}
