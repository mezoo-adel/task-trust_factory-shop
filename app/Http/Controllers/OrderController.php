<?php

namespace App\Http\Controllers;

use App\Services\OrderService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class OrderController extends Controller
{
    public function __construct(
        private OrderService $orderService
    ) {
    }

    public function index(): Response
    {
        $orders = $this->orderService->getUserOrders(auth()->id());

        return Inertia::render('Orders/Index', [
            'orders' => $orders,
        ]);

    }

    public function show(string $uuid): Response
    {
        $order = $this->orderService->getOrderWithRelations($uuid);

        if (!$order || $order->user_id !== auth()->id()) {
            abort(404);
        }

        return Inertia::render('Orders/Show', [
            'order' => $order,
        ]);
    }
}
