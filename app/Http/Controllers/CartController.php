<?php

namespace App\Http\Controllers;

use App\Models\CartItem;
use App\Models\Product;
use App\Services\CartService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CartController extends Controller
{
    public function __construct(
        private CartService $cartService
    ) {}

    public function index(): Response
    {
        $cart = $this->getOrCreateCart();

        return Inertia::render('Cart/Index', [
            'cart' => $cart ? $cart->load('items.product') : null,
        ]);
    }

    public function add(Request $request): RedirectResponse
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
        ]);

        $product = Product::findOrFail($request->product_id);

        if ($product->stock_quantity < $request->quantity) {
            return back()->withErrors(['message' => 'Insufficient stock available.']);
        }

        $cart = $this->getOrCreateCart();

        // Check if adding this quantity would exceed stock
        $existingItem = $cart->items()->where('product_id', $product->id)->first();
        if ($existingItem) {
            $newQuantity = $existingItem->quantity + $request->quantity;
            if ($newQuantity > $product->stock_quantity) {
                return back()->withErrors(['message' => 'Cannot add more items than available in stock.']);
            }
        }

        $this->cartService->addItem($cart, $product, $request->quantity);

        return back()->with('success', 'Product added to cart successfully.');
    }

    public function update(Request $request, CartItem $item): RedirectResponse
    {
        $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);

        if ($item->product->stock_quantity < $request->quantity) {
            return back()->withErrors(['message' => 'Insufficient stock available.']);
        }

        $this->cartService->updateItemQuantity($item, $request->quantity);

        return back()->with('success', 'Cart updated successfully.');
    }

    public function destroy(CartItem $item): RedirectResponse
    {
        $this->cartService->removeItem($item);

        return back()->with('success', 'Item removed from cart.');
    }

    public function clear(): RedirectResponse
    {
        $cart = $this->getOrCreateCart();

        if ($cart) {
            $this->cartService->clearCart($cart);
        }

        return back()->with('success', 'Cart cleared successfully.');
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
