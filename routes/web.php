<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\InertiaAuthController;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', [InertiaAuthController::class, 'login'])->name('login');
Route::get('/register', [InertiaAuthController::class, 'register'])->name('register');


//Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

Route::view('/home', 'home')->middleware('auth')->name('home');

Route::get('/products', function () {
    return Inertia::render('Products');
})->middleware('auth')->name('products');

Route::get('/cart', function () {
    return Inertia::render('Cart');
})->middleware('auth')->name('cart');

Route::get('/orders', function () {
    return Inertia::render('Orders');
})->middleware('auth')->name('orders');

Route::get('/orders/success', function () {
    return Inertia::render('OrderSuccess');
})->middleware('auth')->name('orders.success');
