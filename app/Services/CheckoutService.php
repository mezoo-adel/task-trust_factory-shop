<?php

namespace App\Services;

use App\Models\Address;
use App\Models\Cart;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class CheckoutService
{
    public function __construct(
        private CartService $cartService
    ) {
    }

    /**
     * Create or get user for checkout
     * If guest, creates new user account
     */
    public function createOrGetUser(array $validated): User
    {
        $user = auth()->user();

        if (!$user) {
            // Create new user account for guest checkout
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
            ]);

            // Log in the new user
            auth()->login($user);
        }

        return $user;
    }

    /**
     * Transfer cart from visitor to user if needed
     */
    public function transferCartIfNeeded(Cart $cart, User $user): void
    {
        if ($cart->visitor_id) {
            $this->cartService->transferCartToUser($cart, $user->id);
            $cart->refresh();
        }
    }

    /**
     * Get or create address for checkout
     */
    public function getOrCreateAddress(User $user, array $validated): Address
    {
        if (isset($validated['address_id']) && $validated['address_id']) {
            // Use existing address
            return Address::where('id', $validated['address_id'])
                ->where('user_id', $user->id)
                ->firstOrFail();
        }

        // Create new address
        return Address::create([
            'user_id' => $user->id,
            'full_name' => $validated['full_name'] ?? null,
            'phone' => $validated['phone'],
            'address' => $validated['address'],
            'is_default' => Address::where('user_id', $user->id)->count() === 0, // Set as default if first address
        ]);
    }

    /**
     * Build Stripe line items from cart
     */
    public function buildLineItems(Cart $cart, array $totals): array
    {
        $lineItems = [];

        // Add cart items
        foreach ($cart->items as $cartItem) {
            $lineItems[] = [
                'price_data' => [
                    'currency' => 'usd',
                    'product_data' => [
                        'name' => $cartItem->product->name,
                    ],
                    'unit_amount' => (int) ($cartItem->product->price * 100), // Convert to cents
                ],
                'quantity' => $cartItem->quantity,
            ];
        }

        // Add tax as a separate line item
        if ($totals['tax'] > 0) {
            $lineItems[] = [
                'price_data' => [
                    'currency' => 'usd',
                    'product_data' => [
                        'name' => 'Tax',
                    ],
                    'unit_amount' => (int) ($totals['tax'] * 100), // Convert to cents
                ],
                'quantity' => 1,
            ];
        }

        return $lineItems;
    }

    /**
     * Create Stripe checkout session
     */
    public function createStripeCheckout(
        User $user,
        Cart $cart,
        Address $address,
        array $lineItems,
        array $totals,
        array $cartItemsData
    ) {
        return $user->checkout($lineItems, [
            'success_url' => route('checkout.success') . '?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => route('checkout.index'),
            'metadata' => [
                'user_id' => (string) $user->id,
                'address_id' => (string) $address->id,
                'cart_id' => (string) $cart->id,
                'cart_items' => json_encode($cartItemsData),
                'subtotal' => (string) $totals['subtotal'],
                'tax' => (string) $totals['tax'],
                'shipping' => (string) $totals['shipping'],
                'total' => (string) $totals['total'],
            ],
        ]);
    }

    /**
     * Process checkout - main method that orchestrates the checkout flow
     *
     * NOTE: Cart is preserved during checkout and only deleted after successful payment
     * via Stripe webhook (see StripeEventListener). This ensures cart remains intact
     * if payment fails or user cancels.
     */
    public function processCheckout(Cart $cart, array $validated): array
    {
        return DB::transaction(function () use ($cart, $validated) {
            // Create or get user
            $user = $this->createOrGetUser($validated);

            // Transfer cart if needed (from visitor to user)
            // Cart is NOT deleted here - it remains until payment succeeds
            $this->transferCartIfNeeded($cart, $user);
            $cart->refresh();
            $cart->load('items.product');

            // Get or create address
            $address = $this->getOrCreateAddress($user, $validated);

            // Calculate totals
            $totals = $this->cartService->calculateTotals($cart);

            // Prepare cart items data
            $cartItemsData = $this->cartService->prepareCartItemsForOrder($cart);

            // Build line items
            $lineItems = $this->buildLineItems($cart, $totals);

            // Create Stripe checkout session
            $checkoutSession = $this->createStripeCheckout(
                $user,
                $cart,
                $address,
                $lineItems,
                $totals,
                $cartItemsData
            );

            return [
                'checkout_url' => $checkoutSession->url,
            ];
        });
    }
}

