<?php

namespace App\Http\Controllers;

use App\Http\Requests\CheckoutRequest;
use App\Models\Address;
use App\Services\CartService;
use App\Services\CheckoutService;
use App\Services\VisitorService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class CheckoutController extends Controller
{
    public function __construct(
        private CartService $cartService,
        private CheckoutService $checkoutService,
        private VisitorService $visitorService
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

    public function store(CheckoutRequest $request) 
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

        try {
            $result = $this->checkoutService->processCheckout($cart, $request->validated());

            // Return checkout URL for client-side redirect
            if (request()->expectsJson() || request()->wantsJson()) {
                return response()->json($result);
            }

            // Fallback: redirect directly to Stripe checkout
            return redirect($result['checkout_url']);
        } catch (\Exception $e) {
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
        $fingerprint = !$userId ? $this->visitorService->getOrCreateFingerprint() : null;

        return $this->cartService->getOrCreateCart($userId, $fingerprint);
    }
}
