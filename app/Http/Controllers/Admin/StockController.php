<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Filters\ProductFilter;
use App\Http\Filters\StockTransactionFilter;
use App\Http\Requests\Admin\AdjustStockRequest;
use App\Models\Product;
use App\Models\StockTransaction;
use App\Services\StockService;
use DB;
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

            $this->stockService->updateStock(
                product: $product,
                newStockQuantity: $validated['stock_quantity'],
                newStockThreshold: $validated['stock_threshold'] ?? null,
                reason: $validated['reason'] ?? null,
            );

            return response()->json([
                'success' => true,
                'message' => 'Stock adjusted successfully.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
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
