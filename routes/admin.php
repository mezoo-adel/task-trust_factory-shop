<?php
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\StockController;

// Admin Routes
Route::prefix('admin')->name('admin.')->group(function () {
    // Admin Authentication (no middleware)
    Route::get('/login', [AuthController::class, 'showLogin'])
        ->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/logout', [AuthController::class, 'logout'])
        ->name('logout');

    // Protected Admin Routes
    Route::middleware(['auth', 'admin'])->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])
            ->name('dashboard');

        // Products Management
        Route::resource('products', ProductController::class);
        Route::post('products/{product}/images', [ProductController::class, 'uploadImage'])
            ->name('products.images');

        // Orders Management
        Route::get('orders', [OrderController::class, 'index'])
            ->name('orders.index');
        Route::get('orders/{order}', [OrderController::class, 'show'])
            ->name('orders.show');
        Route::patch('orders/{order}/status', [OrderController::class, 'updateStatus'])
            ->name('orders.updateStatus');
        Route::patch('orders/{order}/notes', [OrderController::class, 'updateNotes'])
            ->name('orders.updateNotes');

        // Stock Management
        Route::get('stock', [StockController::class, 'index'])
            ->name('stock.index');
        Route::post('stock/{product}/adjust', [StockController::class, 'adjust'])
            ->name('stock.adjust');
        Route::get('stock/transactions', [StockController::class, 'transactions'])
            ->name('stock.transactions');

        // Admin Users Management
        Route::get('admins', [AdminUserController::class, 'index'])
            ->name('admins.index');
        Route::post('admins', [AdminUserController::class, 'store'])
            ->name('admins.store');
    });
});

