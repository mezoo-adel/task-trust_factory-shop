<?php

namespace App\Jobs;

use App\Models\Product;
use App\Models\User;
use App\Services\NotificationService;
use App\Services\SettingService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class StockNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Execute the job.
     */
    public function handle(NotificationService $notificationService, SettingService $settingService): void
    {
        // Get all products with low stock (stock_quantity <= stock_threshold)
        $lowStockProducts = Product::whereRaw('stock_quantity <= stock_threshold')
            ->where('is_active', true)
            ->get();

        if ($lowStockProducts->isEmpty()) {
            Log::info('No low stock products found');
            return;
        }

        // Get all admin users
        $admins = User::admin()->get();

        if ($admins->isEmpty()) {
            Log::warning('No admin users found to send low stock notifications');
            return;
        }

        // Prepare email content
        $productList = $lowStockProducts->map(function ($product) {
            return "- {$product->name}: {$product->stock_quantity} units (Threshold: {$product->stock_threshold})";
        })->implode("\n");

        $appName = $settingService->get('app_name');
        $subject = 'Low Stock Alert - ' . $appName;
        $content = "The following products have low stock levels:\n\n{$productList}\n\n" .
            "Please review and restock these items as soon as possible.";

        // Send notification to all admins
        $sentCount = $notificationService->sendBulkEmail(
            users: $admins,
            subject: $subject,
            content: $content,
            ctaText: 'View Stock Management',
            ctaUrl: route('admin.stock.index')
        );

        Log::info("Low stock notification sent to {$sentCount} admin(s) for " . $lowStockProducts->count() . " product(s)");
    }
}
