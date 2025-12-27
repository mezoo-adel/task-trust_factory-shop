<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\Visitor;
use Illuminate\Support\Facades\DB;

class CartService
{
    /**
     * Get or create a cart for the current user or guest
     */
    public function getOrCreateCart(?int $userId = null, ?string $fingerprint = null): Cart
    {
        if ($userId) {
            return Cart::firstOrCreate(
                ['user_id' => $userId],
                ['discount' => 0, 'tax' => 0]
            );
        }

        if ($fingerprint) {
            $visitor = Visitor::firstOrCreate(['fingerprint' => $fingerprint]);
            return Cart::firstOrCreate(
                ['visitor_id' => $visitor->id],
                ['discount' => 0, 'tax' => 0]
            );
        }

        throw new \InvalidArgumentException('Either userId or fingerprint must be provided');
    }

    /**
     * Add a product to the cart
     */
    public function addItem(Cart $cart, Product $product, int $quantity = 1): CartItem
    {
        $existingItem = $cart->items()->where('product_id', $product->id)->first();

        if ($existingItem) {
            $existingItem->update([
                'quantity' => $existingItem->quantity + $quantity,
            ]);
            return $existingItem;
        }

        return $cart->items()->create([
            'product_id' => $product->id,
            'quantity' => $quantity,
            'discount' => 0,
        ]);
    }

    /**
     * Update cart item quantity
     */
    public function updateItemQuantity(CartItem $cartItem, int $quantity): CartItem
    {
        $cartItem->update(['quantity' => $quantity]);
        return $cartItem->fresh();
    }

    /**
     * Remove an item from the cart
     */
    public function removeItem(CartItem $cartItem): void
    {
        $cartItem->delete();
    }

    /**
     * Clear all items from a cart
     */
    public function clearCart(Cart $cart): void
    {
        $cart->items()->delete();
    }

    /**
     * Transfer cart from visitor to user
     */
    public function transferCartToUser(Cart $cart, int $userId): Cart
    {
        $cart->update([
            'visitor_id' => null,
            'user_id' => $userId,
        ]);

        return $cart->fresh();
    }

    /**
     * Calculate cart totals
     */
    public function calculateTotals(Cart $cart): array
    {
        $cart->load('items.product');
        
        $subtotal = 0;
        foreach ($cart->items as $item) {
            $subtotal += $item->product->price * $item->quantity;
        }

        $tax = $subtotal * 0.1; // 10% tax
        $shipping = 0; // Free shipping
        $total = $subtotal + $tax + $shipping;

        return [
            'subtotal' => $subtotal,
            'tax' => $tax,
            'shipping' => $shipping,
            'total' => $total,
        ];
    }

    /**
     * Prepare cart items data for order creation
     */
    public function prepareCartItemsForOrder(Cart $cart): array
    {
        $cart->load('items.product');
        $cartItemsData = [];

        foreach ($cart->items as $cartItem) {
            $cartItemsData[] = [
                'product_id' => $cartItem->product_id,
                'product_name' => $cartItem->product->name,
                'price' => $cartItem->product->price,
                'quantity' => $cartItem->quantity,
                'subtotal' => $cartItem->total,
                'discount' => $cartItem->discount,
                'total' => $cartItem->total,
            ];
        }

        return $cartItemsData;
    }
}



