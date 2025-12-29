<?php

namespace App\Http\Controllers;

use App\Http\Filters\ProductFilter;
use App\Models\Product;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProductController extends Controller
{
    public function index(Request $request, ProductFilter $filter): Response
    {
        $query = Product::filter($filter)
            ->with('uploads')
            ->where('is_active', true);

        switch ($request->input('sort', 'name')) {
            case 'price_asc':
                $query->orderBy('price', 'asc');
                break;
            case 'price_desc':
                $query->orderBy('price', 'desc');
                break;
            case 'newest':
                $query->orderBy('created_at', 'desc');
                break;
            default:
                $query->orderBy('name', 'asc');
        }

        $products = $query->paginate($this->perPage);

        return Inertia::render('Products/Index', [
            'products' => $products,
            'filters' => [
                'search' => $request->input('search'),
                'sort' => $request->input('sort', 'name'),
            ],
        ]);
    }

    public function show(Product $product): Response
    {
        $product->load('uploads');

        return Inertia::render('Products/Show', [
            'product' => $product,
        ]);
    }
}
