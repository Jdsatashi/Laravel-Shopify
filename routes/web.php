<?php

use App\Http\Controllers\InstallationController as InstallCtrl;
use App\Http\Controllers\Shopify\ProductController;
use Illuminate\Support\Facades\Route;

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
    return view('home');
});
Route::get('/dashboard', [ProductController::class, 'Dashboard'])->name('shopify.dashboard');
Route::put('/dashboard', [ProductController::class, 'DiscountVariantPrice'])->name('shopify.RestDiscount');
Route::patch('/dashboard', [ProductController::class, 'RevertVariantPrice'])->name('shopify.RestRevert');


Route::get('/dashboard2', [ProductController::class, 'DashboardGraph'])->name('shopify.dashboard2');
Route::put('/dashboard2', [ProductController::class, 'GraphDiscount'])->name('shopify.GraphDiscount');
Route::patch('/dashboard2', [ProductController::class, 'GraphRevert'])->name('shopify.GraphRevert');

Route::prefix('shopify')->group(function(){
   Route::get('auth', [InstallCtrl::class, 'startInstallation']);
   Route::get('auth/redirect', [InstallCtrl::class, 'handleRedirect'])->name('app_install_redirect');
   Route::get('auth/complete', [InstallCtrl::class, 'completeInstallation'])->name('app_install_complete');
});

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

