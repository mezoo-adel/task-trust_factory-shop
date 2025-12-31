<?php

namespace App\Services;

use App\Enums\StockOperationEnum;
use App\Jobs\StockNotificationJob;
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
                'operation' => StockOperationEnum::REMOVE,
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
                'operation' => StockOperationEnum::ADD,
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

    /**
     * Update product stock and create transaction automatically
     * Determines operation (add/remove) based on new vs current stock
     */
    public function updateStock(
        Product $product,
        int $newStockQuantity,
        ?int $newStockThreshold = null,
        ?string $reason = null,
    ): ?StockTransaction {
        return DB::transaction(function () use ($product, $newStockQuantity, $newStockThreshold, $reason) {
            $previousStock = $product->stock_quantity;
            $quantityDifference = abs($newStockQuantity - $previousStock);

            // Determine operation based on stock change
            if ($newStockQuantity > $previousStock) {
                $operation = StockOperationEnum::ADD;
                $defaultReason = 'Stock increased';
            } elseif ($newStockQuantity < $previousStock) {
                $operation = StockOperationEnum::REMOVE;
                $defaultReason = 'Stock decreased';
            } else {
                // No quantity change, skip transaction creation
                return null;
            }

            // Update product stock and threshold
            $updateData = ['stock_quantity' => $newStockQuantity];
            if ($newStockThreshold !== null) {
                $updateData['stock_threshold'] = $newStockThreshold;
            }
            $product->update($updateData);

            // Create stock transaction
            $transaction = StockTransaction::create([
                'product_id' => $product->id,
                'operation' => $operation,
                'quantity' => $quantityDifference,
                'previous_stock' => $previousStock,
                'new_stock' => $newStockQuantity,
                'reason' => $reason ?? $defaultReason,
                'performed_by' => auth()->id(),
            ]);

            return $transaction;
        });
    }
}
