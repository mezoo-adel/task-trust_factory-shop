<?php

namespace App\Services;

use App\Enums\OrderStatusEnum;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Facades\DB;

class OrderService
{
    /**
     * Create an order with items
     *
     * @param int $userId
     * @param int $addressId
     * @param array $cartItemsData Array of cart items with product_id, product_name, price, quantity, etc.
     * @param array $totals Array with subtotal, tax, shipping, total
     * @param string|null $stripePaymentIntentId
     * @param OrderStatusEnum $status
     * @return Order
     */
    public function createOrder(
        int $userId,
        int $addressId,
        array $cartItemsData,
        array $totals,
        ?string $stripePaymentIntentId = null,
        OrderStatusEnum $status = OrderStatusEnum::PENDING
    ): Order {
        return DB::transaction(function () use ($userId, $addressId, $cartItemsData, $totals, $stripePaymentIntentId, $status) {
            // Create the order
            $order = Order::create([
                'user_id' => $userId,
                'address_id' => $addressId,
                'status' => $status,
                'subtotal' => $totals['subtotal'],
                'tax' => $totals['tax'],
                'shipping' => $totals['shipping'],
                'total' => $totals['total'],
                'stripe_payment_intent_id' => $stripePaymentIntentId,
            ]);

            // Create order items
            foreach ($cartItemsData as $itemData) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $itemData['product_id'],
                    'product_name' => $itemData['product_name'] ?? null,
                    'price' => $itemData['price'],
                    'quantity' => $itemData['quantity'],
                    'subtotal' => $itemData['subtotal'],
                    'discount' => $itemData['discount'] ?? 0,
                    'tax' => $itemData['tax'] ?? 0,
                    'total' => $itemData['total'],
                ]);
            }

            return $order->fresh(['items']);
        });
    }

    /**
     * Check if an order already exists for a given Stripe session/payment intent
     */
    public function orderExistsForStripeSession(string $stripeSessionId): bool
    {
        return Order::where('stripe_payment_intent_id', $stripeSessionId)->exists();
    }

    /**
     * Get an order by Stripe session ID
     */
    public function getOrderByStripeSession(string $stripeSessionId): ?Order
    {
        return Order::where('stripe_payment_intent_id', $stripeSessionId)->first();
    }

    /**
     * Update order status
     */
    public function updateOrderStatus(Order $order, OrderStatusEnum $status): Order
    {
        $order->update(['status' => $status]);
        return $order->fresh();
    }

    /**
     * Get order with all relationships
     */
    public function getOrderWithRelations(string $uuid): ?Order
    {
        return Order::with(['items.product', 'address', 'user'])
            ->where('uuid', $uuid)
            ->first();
    }

    /**
     * Get user's orders (paginated)
     */
    public function getUserOrders(int $userId, int $perPage = null)
    {
        $perPage ??= request()->input('per_page', 3);
        return Order::with(['items.product', 'address'])
            ->where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }
}

