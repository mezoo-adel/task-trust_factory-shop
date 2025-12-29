<?php

namespace App\Http\Controllers\Admin;

use App\Enums\OrderStatusEnum;
use App\Http\Controllers\Controller;
use App\Http\Filters\OrderFilter;
use App\Http\Requests\Admin\UpdateOrderNotesRequest;
use App\Http\Requests\Admin\UpdateOrderStatusRequest;
use App\Models\Order;
use Illuminate\Http\Request;
use Inertia\Inertia;

class OrderController extends Controller
{
    public function index(Request $request, OrderFilter $filter)
    {
        $orders = Order::filter($filter)
            ->with(['user', 'items', 'address'])
            ->orderByDesc('created_at')
            ->paginate($this->perPage);

        return Inertia::render('Admin/Orders/Index', [
            'orders' => $orders,
            'filters' => $request->only(['search', 'status', 'sort_by', 'sort_order']),
            'statuses' => array_map(fn($status) => $status->value, OrderStatusEnum::cases()),
        ]);
    }

    public function show(Order $order)
    {
        $order->load(['user', 'items.product', 'address', 'stockTransactions']);

        return Inertia::render('Admin/Orders/Show', [
            'order' => $order,
            'statuses' => array_map(fn($status) => $status->value, OrderStatusEnum::cases()),
        ]);
    }

    public function updateStatus(UpdateOrderStatusRequest $request, Order $order)
    {
        $order->update([
            'status' => OrderStatusEnum::from($request->validated()['status']),
        ]);

        return back()->with('success', 'Order status updated successfully.');
    }

    public function updateNotes(UpdateOrderNotesRequest $request, Order $order)
    {
        $order->update($request->validated());

        return back()->with('success', 'Order notes updated successfully.');
    }
}
