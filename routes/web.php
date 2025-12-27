<?php

use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::controller(ProductController::class)->prefix('products')->name('products.')->group(function () {
    Route::get('/', 'index')->name('index');
    Route::get('/{product}', 'show')->name('show');
});

Route::controller(CartController::class)->prefix('cart')->name('cart.')->group(function () {
    Route::get('/', 'index')->name('index');
    Route::post('/add', 'add')->name('add');
    Route::put('/items/{item}', 'update')->name('update');
    Route::delete('/items/{item}', 'destroy')->name('destroy');
    Route::delete('/', 'clear')->name('clear');
});

Route::controller(CheckoutController::class)->prefix('checkout')->name('checkout.')->group(function () {
    Route::get('/', 'index')->name('index');
    Route::post('/', 'store')->name('store');
    Route::get('/success', 'success')->name('success');
});




Route::middleware(['auth'])->group(function () {
    Route::controller(OrderController::class)->prefix('orders')->name('orders.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/{uuid}', 'show')->name('show');
    });
});

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

require __DIR__ . '/settings.php';
