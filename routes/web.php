<?php

use App\Http\Controllers\InstallationController as InstallCtrl;
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
    return view('welcome');
})->name('home');

Route::prefix('shopify')->group(function(){
   Route::get('auth', [InstallCtrl::class, 'startInstallation']);
   Route::get('auth/redirect', [InstallCtrl::class, 'handleRedirect'])->name('app_install_redirect');
   Route::get('auth/complete', [InstallCtrl::class, 'completeInstallation'])->name('app_install_complete');
});
