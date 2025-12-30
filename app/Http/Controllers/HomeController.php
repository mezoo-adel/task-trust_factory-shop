<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    public function index(): Response
    {
        $featuredProducts = Product::active()
            ->orderBy('created_at', 'desc')
            ->limit(8)
            ->get();

        return Inertia::render('Home', [
            'featuredProducts' => $featuredProducts,
        ]);
    }
}
