<?php

namespace App\Http\Controllers;

use App\Models\Address;
use App\Services\CartService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class CheckoutController extends Controller
{
    public function __construct(
        private CartService $cartService
    ) {}

    public function index()
    {
        $cart = $this->getOrCreateCart();
        
        if (!$cart || $cart->items()->count() === 0) {
            return redirect()->route('cart.index')
                ->withErrors(['message' => 'Your cart is empty.']);
        }
        
        $cart->load('items.product');

        $addresses = [];
        if (auth()->check()) {
            $addresses = Address::where('user_id', auth()->id())->get();
        }

        return Inertia::render('Checkout/Index', [
            'cart' => $cart,
            'addresses' => $addresses,
        ]);
    }

    public function store(Request $request) 
    {
        $cart = $this->getOrCreateCart();
        
        if (!$cart || $cart->items()->count() === 0) {
            if (request()->expectsJson() || request()->wantsJson()) {
                return response()->json([
                    'message' => 'Your cart is empty.',
                ], 422);
            }
            return redirect()->route('cart.index')
                ->withErrors(['message' => 'Your cart is empty.']);
        }
        
        $cart->load('items.product');

        // Validate request
        try {
            $validated = $request->validate([
                'full_name' => 'required|string|max:255',
                'email' => 'required|email|max:255',
                'phone' => 'required|string|max:20',
                'address' => 'required|string|max:500',
                'password' => auth()->check() ? 'nullable' : 'required|string|min:8|confirmed',
                'name' => auth()->check() ? 'nullable' : 'required|string|max:255',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            if (request()->expectsJson() || request()->wantsJson()) {
                return response()->json([
                    'message' => 'Validation failed.',
                    'errors' => $e->errors(),
                ], 422);
            }
            throw $e;
        }

        try {
            DB::beginTransaction();

            // Create or get user
            $user = auth()->user();
            if (!$user) {
                // Create new user account for guest checkout
                $user = \App\Models\User::create([
                    'name' => $validated['name'],
                    'email' => $validated['email'],
                    'password' => bcrypt($validated['password']),
                ]);

                // Transfer cart from visitor to user
                if ($cart->visitor_id) {
                    $this->cartService->transferCartToUser($cart, $user->id);
                    $cart->refresh();
                }

                // Log in the new user
                auth()->login($user);
            }

            // Create or get address
            $address = Address::create([
                'user_id' => $user->id,
                'full_name' => $validated['full_name'],
                'phone' => $validated['phone'],
                'address' => $validated['address'],
                'is_default' => true,
            ]);

            // Calculate totals using CartService
            $totals = $this->cartService->calculateTotals($cart);

            // Prepare cart items data for metadata using CartService
            $cartItemsData = $this->cartService->prepareCartItemsForOrder($cart);

            // Build line items for Cashier checkout
            $lineItems = [];
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

            // Use Cashier's checkout method for single charge
            $checkoutSession = $user->checkout($lineItems, [
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

            DB::commit();

            // Return checkout URL for client-side redirect (to avoid CORS issues with Inertia)
            if (request()->expectsJson() || request()->wantsJson()) {
                return response()->json([
                    'checkout_url' => $checkoutSession->url,
                ]);
            }

            // Fallback: redirect directly to Stripe checkout
            return redirect($checkoutSession->url);
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Checkout failed', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            if (request()->expectsJson() || request()->wantsJson()) {
                return response()->json([
                    'message' => 'An error occurred during checkout. Please try again.',
                    'error' => $e->getMessage(),
                ], 500);
            }

            return back()->withErrors(['message' => 'An error occurred during checkout. Please try again.']);
        }
    }

    public function success(Request $request): RedirectResponse
    {
        $sessionId = $request->query('session_id');

        if (!$sessionId) {
            return redirect()->route('checkout.index')
                ->withErrors(['message' => 'Invalid payment session.']);
        }

        // Verify the session with Stripe using Cashier
        try {
            $user = auth()->user();
            if (!$user) {
                return redirect()->route('checkout.index')
                    ->withErrors(['message' => 'User not authenticated.']);
            }

            // Use Cashier's Stripe client to retrieve session
            $session = $user->stripe()->checkout->sessions->retrieve($sessionId);
            
            if ($session->payment_status === 'paid') {
                // Payment was successful - webhook will handle order creation
                return redirect()->route('orders.index')
                    ->with('success', 'Payment completed successfully! Your order is being processed.');
            }
        } catch (\Exception $e) {
            Log::error('Stripe session verification failed', [
                'error' => $e->getMessage(),
                'session_id' => $sessionId,
            ]);
        }

        // If payment not confirmed yet or error, redirect to checkout
        return redirect()->route('checkout.index')
            ->with('info', 'Payment is being processed. Please check your orders page shortly.');
    }

    private function getOrCreateCart()
    {
        $userId = auth()->check() ? auth()->id() : null;
        $fingerprint = !$userId ? $this->getOrCreateVisitorFingerprint() : null;

        return $this->cartService->getOrCreateCart($userId, $fingerprint);
    }

    private function getOrCreateVisitorFingerprint(): string
    {
        if (!session()->has('visitor_fingerprint')) {
            $fingerprint = md5(session()->getId() . request()->ip() . request()->userAgent());
            session()->put('visitor_fingerprint', $fingerprint);
        }

        return session()->get('visitor_fingerprint');
    }
}
