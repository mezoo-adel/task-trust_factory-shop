<?php
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\StockController;

// Admin Routes
Route::prefix('admin')->name('admin.')->group(function () {
    // Protected Admin Routes
    Route::middleware(['auth', 'admin'])->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])
            ->name('dashboard');

        // Products Management
        Route::resource('products', ProductController::class);

        // Orders Management
        Route::controller(OrderController::class)->prefix('orders')->name('orders.')->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/{order}', 'show')->name('show');
            Route::patch('/{order}/status', 'updateStatus')->name('updateStatus');
            Route::patch('/{order}/notes', 'updateNotes')->name('updateNotes');
        });

        // Stock Management
        Route::controller(StockController::class)->prefix('stock')->name('stock.')->group(function () {
            Route::get('/', 'index')->name('index');
            Route::post('/{product}/adjust', 'adjust')->name('adjust');
            Route::get('/transactions', 'transactions')->name('transactions');
        });

        // Admin Users Management
        Route::controller(AdminUserController::class)->prefix('admins')->name('admins.')->group(function () {
            Route::get('/', 'index')->name('index');
            Route::post('/', 'store')->name('store');
        });

        // Settings Management
        Route::controller(SettingController::class)->prefix('settings')->name('settings.')->group(function () {
            Route::get('/', 'index')->name('index');
            Route::post('/', 'update')->name('update');
        });
    });
});
