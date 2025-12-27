<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\StockTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class StockService
{
    /**
     * Decrease stock for an order and create stock transactions
     */
    public function decreaseStockForOrder(Order $order): void
    {
        DB::transaction(function () use ($order) {
            $order->load('items.product');

            foreach ($order->items as $orderItem) {
                $this->decreaseStock(
                    product: $orderItem->product,
                    quantity: $orderItem->quantity,
                    orderId: $order->id,
                    reason: 'Sale'
                );
            }
        });
    }

    /**
     * Decrease stock for a product
     */
    public function decreaseStock(
        Product $product,
        int $quantity,
        ?int $orderId = null,
        string $reason = 'Manual adjustment'
    ): StockTransaction {
        return DB::transaction(function () use ($product, $quantity, $orderId, $reason) {
            $previousStock = $product->stock_quantity;
            $newStock = max(0, $previousStock - $quantity);

            $product->update(['stock_quantity' => $newStock]);

            $transaction = StockTransaction::create([
                'product_id' => $product->id,
                'order_id' => $orderId,
                'operation' => 'remove',
                'quantity' => $quantity,
                'previous_stock' => $previousStock,
                'new_stock' => $newStock,
                'reason' => $reason,
            ]);

            // Check for low stock
            if ($newStock <= $product->stock_threshold) {
                Log::info('Low stock detected', [
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'current_stock' => $newStock,
                    'threshold' => $product->stock_threshold,
                ]);
                // TODO: Dispatch LowStockNotificationJob
            }

            return $transaction;
        });
    }

    /**
     * Increase stock for a product
     */
    public function increaseStock(
        Product $product,
        int $quantity,
        ?int $orderId = null,
        string $reason = 'Manual adjustment'
    ): StockTransaction {
        return DB::transaction(function () use ($product, $quantity, $orderId, $reason) {
            $previousStock = $product->stock_quantity;
            $newStock = $previousStock + $quantity;

            $product->update(['stock_quantity' => $newStock]);

            return StockTransaction::create([
                'product_id' => $product->id,
                'order_id' => $orderId,
                'operation' => 'add',
                'quantity' => $quantity,
                'previous_stock' => $previousStock,
                'new_stock' => $newStock,
                'reason' => $reason,
            ]);
        });
    }

    /**
     * Restore stock for a cancelled order
     */
    public function restoreStockForOrder(Order $order): void
    {
        DB::transaction(function () use ($order) {
            $order->load('items.product');

            foreach ($order->items as $orderItem) {
                if ($orderItem->product) {
                    $this->increaseStock(
                        product: $orderItem->product,
                        quantity: $orderItem->quantity,
                        orderId: $order->id,
                        reason: 'Order cancelled'
                    );
                }
            }
        });
    }

    /**
     * Get stock transactions for a product
     */
    public function getProductStockTransactions(int $productId)
    {
        return StockTransaction::where('product_id', $productId)
            ->with('order')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get stock transactions for an order
     */
    public function getOrderStockTransactions(int $orderId)
    {
        return StockTransaction::where('order_id', $orderId)
            ->with('product')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Check if product has sufficient stock
     */
    public function hasSufficientStock(Product $product, int $quantity): bool
    {
        return $product->stock_quantity >= $quantity;
    }
}



