<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\Cart\IndexCartController;
use App\Http\Controllers\Api\Cart\AddToCartController;
use App\Http\Controllers\Api\Cart\RemoveFromCartController;
use App\Http\Controllers\Api\Cart\UpdateCartItemController;
use App\Http\Controllers\Api\Cart\ClearCartController;
use App\Http\Controllers\Api\Cart\GetCartCountController;
use App\Http\Controllers\Api\Cart\ToggleCartController;
use App\Http\Controllers\Api\Cart\CheckCartController;
use App\Http\Controllers\Api\Order\CreateOrderController;
use App\Http\Controllers\Api\Order\IndexOrdersController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::prefix('v1')->group(function () {
    // Public routes
    Route::controller(AuthController::class)->prefix('auth')->group(function () {
        Route::post('register', 'register')->name('auth.register');
        Route::post('login', 'login')->name('auth.login');
    });

    Route::controller(ProductController::class)->prefix('products')->group(function () {
        Route::get('/', 'index')->name('products.index');
        Route::get('{id}', 'show')->name('products.show');
    });

    // Protected routes
    Route::middleware('auth')->group(function () {
        Route::controller(AuthController::class)->prefix('auth')->group(function () {
            Route::post('logout', 'logout')->name('auth.logout');
            Route::get('user', 'user')->name('auth.user');
        });

        // Cart routes
        Route::prefix('cart')->group(function () {
            Route::get('/', IndexCartController::class)->name('cart.index');
            Route::post('/', AddToCartController::class)->name('cart.store');
            Route::put('/', UpdateCartItemController::class)->name('cart.update');
            Route::delete('/', RemoveFromCartController::class)->name('cart.destroy');
            Route::post('toggle', ToggleCartController::class)->name('cart.toggle');
            Route::get('check/{productId}', CheckCartController::class)->name('cart.check');
            Route::get('count', GetCartCountController::class)->name('cart.count');
            Route::delete('clear', ClearCartController::class)->name('cart.clear');
        });

        // Order routes
        Route::prefix('orders')->group(function () {
            Route::get('/', IndexOrdersController::class)->name('orders.index');
            Route::post('/', CreateOrderController::class)->name('orders.store');
        });
    });
});
