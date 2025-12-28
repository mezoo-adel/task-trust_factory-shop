<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
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

    public function index(Request $request)
    {
        $query = Product::query();

        // Filter by stock status
        if ($request->has('filter')) {
            $filter = $request->input('filter');
            if ($filter === 'low') {
                $query->whereRaw('stock_quantity <= stock_threshold');
            } elseif ($filter === 'out') {
                $query->where('stock_quantity', 0);
            }
        }

        // Search
        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where('name', 'like', "%{$search}%");
        }

        $products = $query->orderBy('stock_quantity', 'asc')->paginate(20);

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

    public function transactions(Request $request)
    {
        $query = StockTransaction::with(['product', 'order', 'performedBy']);

        // Filter by product
        if ($request->has('product_id')) {
            $query->where('product_id', $request->input('product_id'));
        }

        // Filter by operation
        if ($request->has('operation')) {
            $query->where('operation', $request->input('operation'));
        }

        $transactions = $query->orderBy('created_at', 'desc')->paginate(50);

        return Inertia::render('Admin/Stock/Transactions', [
            'transactions' => $transactions,
            'filters' => $request->only(['product_id', 'operation']),
        ]);
    }
}
