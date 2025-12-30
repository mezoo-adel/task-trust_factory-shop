<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\DashboardService;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function __construct(
        protected DashboardService $dashboardService
    ) {}

    public function index()
    {
        $stats = $this->dashboardService->getTodayStatistics();
        $ordersByStatus = $this->dashboardService->getOrdersByStatus();
        $recentOrders = $this->dashboardService->getRecentOrders(10);
        $lowStockProducts = $this->dashboardService->getLowStockProducts(10);

        return Inertia::render('Admin/Dashboard', [
            'stats' => $stats,
            'orders_by_status' => $ordersByStatus,
            'recent_orders' => $recentOrders,
            'low_stock_products' => $lowStockProducts,
        ]);
    }
}
