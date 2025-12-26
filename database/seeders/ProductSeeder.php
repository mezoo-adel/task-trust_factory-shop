<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $products = [
            [
                'name' => 'Hydrating Face Serum',
                'description' => 'A lightweight, fast-absorbing serum that deeply hydrates and plumps the skin with hyaluronic acid.',
                'price' => 45.99,
                'stock_quantity' => 50,
                'stock_threshold' => 10,
            ],
            [
                'name' => 'Vitamin C Brightening Cream',
                'description' => 'Brightens and evens skin tone with powerful vitamin C and natural extracts.',
                'price' => 38.5,
                'stock_quantity' => 30,
                'stock_threshold' => 5,
            ],
            [
                'name' => 'Retinol Night Treatment',
                'description' => 'Advanced anti-aging night treatment with retinol to reduce fine lines and wrinkles.',
                'price' => 52.0,
                'stock_quantity' => 25,
                'stock_threshold' => 8,
            ],
            [
                'name' => 'Gentle Cleansing Foam',
                'description' => 'Soft, gentle foam cleanser that removes makeup and impurities without stripping skin.',
                'price' => 24.99,
                'stock_quantity' => 100,
                'stock_threshold' => 15,
            ],
            [
                'name' => 'Nourishing Eye Cream',
                'description' => 'Rich eye cream that reduces dark circles, puffiness, and fine lines around the eyes.',
                'price' => 42.0,
                'stock_quantity' => 40,
                'stock_threshold' => 10,
            ],
            [
                'name' => 'SPF 50 Sunscreen',
                'description' => 'Broad-spectrum sunscreen with SPF 50 for daily protection against UV rays.',
                'price' => 28.75,
                'stock_quantity' => 75,
                'stock_threshold' => 20,
            ],
            [
                'name' => 'Exfoliating Toner',
                'description' => 'Gentle exfoliating toner with AHA/BHA to refine pores and improve skin texture.',
                'price' => 32.5,
                'stock_quantity' => 60,
                'stock_threshold' => 12,
            ],
            [
                'name' => 'Moisturizing Face Mask',
                'description' => 'Intensive hydrating mask with natural ingredients for soft, supple skin.',
                'price' => 19.99,
                'stock_quantity' => 80,
                'stock_threshold' => 15,
            ],
            [
                'name' => 'Anti-Aging Serum',
                'description' => 'Powerful anti-aging serum with peptides and antioxidants for youthful-looking skin.',
                'price' => 58.0,
                'stock_quantity' => 2,
                'stock_threshold' => 3,
            ],
            [
                'name' => 'Lip Treatment Balm',
                'description' => 'Nourishing lip balm with natural oils and vitamins for soft, smooth lips.',
                'price' => 12.99,
                'stock_quantity' => 120,
                'stock_threshold' => 25,
            ],
        ];

        foreach ($products as $product) {
            Product::create($product);
        }
    }
}
