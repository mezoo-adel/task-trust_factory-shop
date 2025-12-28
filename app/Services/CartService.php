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
     * 
     * For authenticated users: Retrieves existing cart by user_id or creates new one
     * For guests: Retrieves existing cart by visitor fingerprint or creates new one
     * 
     * Cart persistence is guaranteed:
     * - User carts: Linked to user_id, persist across sessions
     * - Guest carts: Linked to visitor fingerprint (stored in session), persist across page visits
     * 
     * @param int|null $userId Authenticated user ID
     * @param string|null $fingerprint Visitor fingerprint (from session)
     * @return Cart
     */
    public function getOrCreateCart(?int $userId = null, ?string $fingerprint = null): Cart
    {
        if ($userId) {
            // Authenticated user: get or create cart by user_id
            return Cart::firstOrCreate(
                ['user_id' => $userId],
                ['discount' => 0, 'tax' => 0]
            );
        }

        if ($fingerprint) {
            // Guest user: get or create visitor, then get or create cart
            // This ensures cart is retrieved if it already exists for this fingerprint
            $visitor = Visitor::firstOrCreate(['fingerprint' => $fingerprint]);
            
            // firstOrCreate will retrieve existing cart if visitor already has one
            return Cart::firstOrCreate(
                ['visitor_id' => $visitor->id],
                ['discount' => 0, 'tax' => 0]
            );
        }

        throw new \InvalidArgumentException('Either userId or fingerprint must be provided');
    }

    /**
     * Get existing cart by visitor fingerprint (without creating if not exists)
     * Useful for checking if cart exists before operations
     * 
     * @param string $fingerprint Visitor fingerprint
     * @return Cart|null
     */
    public function getCartByFingerprint(string $fingerprint): ?Cart
    {
        $visitor = Visitor::where('fingerprint', $fingerprint)->first();
        
        if (!$visitor) {
            return null;
        }

        return Cart::where('visitor_id', $visitor->id)->first();
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
     * Clear the cart and all items within
     */
    public function clearCart(Cart $cart): void
    {
        $cart->items()->delete();
        $cart->delete();
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



