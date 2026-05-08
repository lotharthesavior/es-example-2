<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DemoController;
use Illuminate\Support\Facades\Route;

Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
Route::view('/presentation', 'presentation')->name('presentation');
Route::get('/api/stats', [DashboardController::class, 'stats'])->name('dashboard.stats');
Route::get('/api/state', [DashboardController::class, 'state'])->name('dashboard.state');

// Demo trigger endpoints
Route::prefix('demo')->name('demo.')->group(function (): void {
    Route::post('/add-to-cart', [DemoController::class, 'addToCart'])->name('add-to-cart');
    Route::post('/remove-from-cart', [DemoController::class, 'removeFromCart'])->name('remove-from-cart');
    Route::post('/checkout', [DemoController::class, 'checkout'])->name('checkout');
    Route::post('/mark-as-paid', [DemoController::class, 'markAsPaid'])->name('mark-as-paid');
    Route::post('/mark-as-shipped', [DemoController::class, 'markAsShipped'])->name('mark-as-shipped');
    Route::post('/mark-as-delivered', [DemoController::class, 'markAsDelivered'])->name('mark-as-delivered');
    Route::post('/cancel-order', [DemoController::class, 'cancelOrder'])->name('cancel-order');
    Route::post('/refund-order', [DemoController::class, 'refundOrder'])->name('refund-order');
    Route::post('/replay-events', [DemoController::class, 'replayEvents'])->name('replay-events');
    Route::post('/rewind-to', [DemoController::class, 'rewindTo'])->name('rewind-to');
});
