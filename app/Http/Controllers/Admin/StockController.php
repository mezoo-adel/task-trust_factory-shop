<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Filters\ProductFilter;
use App\Http\Filters\StockTransactionFilter;
use App\Http\Requests\Admin\AdjustStockRequest;
use App\Models\Product;
use App\Models\StockTransaction;
use App\Services\StockService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class StockController extends Controller
{
    public function __construct(
        private StockService $stockService
    ) {}

    public function index(Request $request, ProductFilter $filter)
    {
        $products = Product::filter($filter)
            ->orderBy('stock_quantity', 'asc')
            ->paginate($this->perPage);

        return Inertia::render('Admin/Stock/Index', [
            'products' => $products,
            'filters' => $request->only(['filter', 'search']),
        ]);
    }

    public function adjust(AdjustStockRequest $request, Product $product)
    {
        try {
            $validated = $request->validated();

            if ($validated['operation'] === 'add') {
                $this->stockService->addStock(
                    $product,
                    $validated['quantity'],
                    $validated['reason'],
                    auth()->id()
                );
            } else {
                $this->stockService->removeStock(
                    $product,
                    $validated['quantity'],
                    $validated['reason'],
                    auth()->id()
                );
            }

            return back()->with('success', 'Stock adjusted successfully.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function transactions(Request $request, StockTransactionFilter $filter)
    {
        $transactions = StockTransaction::filter($filter)
            ->with(['product', 'order', 'performedBy'])
            ->orderBy('created_at', 'desc')
            ->paginate($this->perPage);

        return Inertia::render('Admin/Stock/Transactions', [
            'transactions' => $transactions,
            'filters' => $request->only(['product_id', 'operation']),
        ]);
    }
}
