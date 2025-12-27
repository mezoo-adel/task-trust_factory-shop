<?php

namespace App\Listeners;

use App\Enums\OrderStatusEnum;
use App\Models\Address;
use App\Models\User;
use App\Services\CartService;
use App\Services\OrderService;
use App\Services\StockService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Laravel\Cashier\Events\WebhookReceived;

class StripeEventListener
{
    public function __construct(
        private OrderService $orderService,
        private StockService $stockService,
        private CartService $cartService
    ) {}

    /**
     * Handle the event.
     */
    public function handle(WebhookReceived $event): void
    {
        Log::info('Stripe webhook event received', [
            'type' => $event->payload['type'],
            'id' => $event->payload['id'] ?? null,
        ]);

        if ($event->payload['type'] === 'checkout.session.completed') {
            $this->handleCheckoutSessionCompleted($event->payload['data']['object']);
        }
    }

    private function handleCheckoutSessionCompleted(array $session): void
    {
        Log::info('Processing checkout.session.completed', [
            'session_id' => $session['id'],
            'payment_status' => $session['payment_status'],
        ]);

        if (($session['payment_status'] ?? null) !== 'paid') {
            Log::info('Session not paid, skipping', [
                'payment_status' => $session['payment_status'] ?? 'unknown',
            ]);
            return;
        }

        // Extract checkout data from metadata
        $metadata = $session['metadata'] ?? [];
        $userId = $metadata['user_id'] ?? null;
        $addressId = $metadata['address_id'] ?? null;
        $cartId = $metadata['cart_id'] ?? null;
        $cartItemsJson = $metadata['cart_items'] ?? null;
        $subtotal = $metadata['subtotal'] ?? null;
        $tax = $metadata['tax'] ?? null;
        $shipping = $metadata['shipping'] ?? null;
        $total = $metadata['total'] ?? null;

        if (!$userId || !$addressId || !$cartItemsJson) {
            Log::error('Missing required metadata in session', [
                'user_id' => $userId,
                'address_id' => $addressId,
                'has_cart_items' => !empty($cartItemsJson),
            ]);
            return;
        }

        try {
            DB::beginTransaction();

            // Verify user and address exist
            $user = User::find($userId);
            $address = Address::find($addressId);

            if (!$user || !$address) {
                Log::error('User or address not found', [
                    'user_id' => $userId,
                    'address_id' => $addressId,
                    'user_exists' => $user !== null,
                    'address_exists' => $address !== null,
                ]);
                DB::rollBack();
                return;
            }

            // Check if order already exists for this session (idempotency)
            $sessionId = $session['id'];
            if ($this->orderService->orderExistsForStripeSession($sessionId)) {
                $existingOrder = $this->orderService->getOrderByStripeSession($sessionId);
                Log::info('Order already exists for this session', [
                    'order_id' => $existingOrder->id,
                    'order_uuid' => $existingOrder->uuid,
                ]);
                DB::rollBack();
                return;
            }

            // Deserialize cart items
            $cartItems = json_decode($cartItemsJson, true);
            if (!is_array($cartItems)) {
                Log::error('Invalid cart items JSON in metadata');
                DB::rollBack();
                return;
            }

            // Create order with items using OrderService
            $order = $this->orderService->createOrder(
                userId: $user->id,
                addressId: $address->id,
                cartItemsData: $cartItems,
                totals: [
                    'subtotal' => $subtotal,
                    'tax' => $tax,
                    'shipping' => $shipping,
                    'total' => $total,
                ],
                stripePaymentIntentId: $session['payment_intent'] ?? $sessionId,
                status: OrderStatusEnum::PAID
            );

            Log::info('Order created from webhook', [
                'order_id' => $order->id,
                'order_uuid' => $order->uuid,
                'user_id' => $user->id,
            ]);

            // Decrease stock for order using StockService
            $this->stockService->decreaseStockForOrder($order);

            // Clear cart items (not the cart itself) using CartService
            if ($cartId) {
                $cart = \App\Models\Cart::find($cartId);
                if ($cart) {
                    $this->cartService->clearCart($cart);
                    Log::info('Cart items cleared', ['cart_id' => $cart->id]);
                }
            }

            DB::commit();

            Log::info('Order payment processed successfully', [
                'order_id' => $order->id,
                'order_uuid' => $order->uuid,
            ]);

            // TODO: Send order confirmation email
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Error processing checkout session', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            throw $e;
        }
    }
}
