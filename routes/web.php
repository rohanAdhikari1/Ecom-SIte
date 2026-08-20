<?php

use App\Http\Controllers\EsewaPaymentController;
use Illuminate\Support\Facades\Route;

Route::livewire('/', 'pages::home')->name('home');
Route::livewire('/shop', 'pages::shop')->name('shop');
Route::livewire('/shop/{category:slug}', 'pages::shop')->name('shop.category');
Route::livewire('/product/{product:slug}', 'pages::product.show')->name('product.show');

Route::livewire('/cart', 'pages::cart')->name('cart');
Route::livewire('/checkout', 'pages::checkout')->name('checkout');
Route::livewire('/order/confirmation/{order:tracking_token}', 'pages::order.confirmation')->name('order.confirmation');
Route::livewire('/track-order', 'pages::order.track')->name('order.track');

Route::get('/payment/esewa/{order:tracking_token}/redirect', [EsewaPaymentController::class, 'redirect'])->name('payment.esewa.redirect');
Route::get('/payment/esewa/{order:tracking_token}/retry', [EsewaPaymentController::class, 'retry'])->name('payment.esewa.retry');
Route::get('/payment/esewa/{order:tracking_token}/success', [EsewaPaymentController::class, 'success'])->name('payment.esewa.success');
Route::get('/payment/esewa/{order:tracking_token}/failure', [EsewaPaymentController::class, 'failure'])->name('payment.esewa.failure');

Route::livewire('/login', 'pages::auth.login')->name('login');
Route::livewire('/register', 'pages::auth.register')->name('register');
Route::post('/logout', function () {
    auth()->logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();

    return redirect()->route('home');
})->name('logout');

Route::livewire('/account/orders', 'pages::account.orders')
    ->middleware('auth')
    ->name('account.orders');

Route::livewire('/account/profile', 'pages::account.profile')
    ->middleware('auth')
    ->name('account.profile');
