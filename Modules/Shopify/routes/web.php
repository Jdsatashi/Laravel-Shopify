<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Modules\Shopify\Http\Controllers\ShopifyController;
use Modules\Shopify\Http\Controllers\Shopify\ProductController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('shopify::index');
});
Route::get('/dashboard', [ProductController::class, 'RestDashboard'])->name('shopify.dashboard');
Route::put('/dashboard', [ProductController::class, 'DiscountVariantPrice'])->name('shopify.RestDiscount');
Route::patch('/dashboard', [ProductController::class, 'RevertVariantPrice'])->name('shopify.RestRevert');


Route::get('/dashboard2', [ProductController::class, 'GraphDashboard'])->name('shopify.dashboard2');
Route::put('/dashboard2', [ProductController::class, 'GraphDiscount'])->name('shopify.GraphDiscount');
Route::patch('/dashboard2', [ProductController::class, 'GraphRevert'])->name('shopify.GraphRevert');

Route::prefix('shopify')->group(function(){
   Route::get('auth', [ShopifyController::class, 'startInstallation']);
   Route::get('auth/redirect', [ShopifyController::class, 'handleRedirect'])->name('app_install_redirect');
   Route::get('auth/complete', [ShopifyController::class, 'completeInstallation'])->name('app_install_complete');
});

Auth::routes();

