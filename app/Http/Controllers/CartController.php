<?php

namespace App\Http\Controllers;

use App\Http\Requests\AddToCartRequest;
use App\Http\Requests\UpdateCartItemRequest;
use App\Models\CartItem;
use App\Models\Product;
use App\Services\CartService;
use App\Services\VisitorService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class CartController extends Controller
{
    public function __construct(
        private CartService $cartService,
        private VisitorService $visitorService
    ) {}

    public function index(): Response
    {
        $cart = $this->getOrCreateCart();

        return Inertia::render('Cart/Index', [
            'cart' => $cart ? $cart->load('items.product') : null,
        ]);
    }

    public function add(AddToCartRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $product = Product::findOrFail($validated['product_id']);

        if ($product->stock_quantity < $validated['quantity']) {
            return back()->withErrors(['message' => 'Insufficient stock available.']);
        }

        $cart = $this->getOrCreateCart();

        // Check if adding this quantity would exceed stock
        $existingItem = $cart->items()->where('product_id', $product->id)->first();
        if ($existingItem) {
            $newQuantity = $existingItem->quantity + $validated['quantity'];
            if ($newQuantity > $product->stock_quantity) {
                return back()->withErrors(['message' => 'Cannot add more items than available in stock.']);
            }
        }

        $this->cartService->addItem($cart, $product, $validated['quantity']);

        return back()->with('success', 'Product added to cart successfully.');
    }

    public function update(UpdateCartItemRequest $request, CartItem $item): RedirectResponse
    {
        $validated = $request->validated(); 
        if ($item->product->stock_quantity < $validated['quantity']) {
            return back()->withErrors(['message' => 'Insufficient stock available.']);
        }

        $this->cartService->updateItemQuantity($item, $validated['quantity']);

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
        $fingerprint = !$userId ? $this->visitorService->getOrCreateFingerprint() : null;

        return $this->cartService->getOrCreateCart($userId, $fingerprint);
    }
}
