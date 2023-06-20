<?php

use App\Http\Controllers\Shopify\ProductController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::prefix('shopify/')->group(function(){
    Route::get('products/{store}', [ProductController::class, 'Index']);
    Route::post('products/{store}/create', [ProductController::class, 'Create']);
    Route::get('product/{store}/{id}', [ProductController::class, 'Show']);
    Route::put('product/{store}/{id}', [ProductController::class, 'Update']);
    Route::delete('product/{store}/{id}', [ProductController::class, 'Delete']);
});