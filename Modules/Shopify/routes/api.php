<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Modules\Shopify\Http\Controllers\Shopify\ProductController;

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
    Route::get('products/', [ProductController::class, 'Index']);
    Route::post('products/create', [ProductController::class, 'Create']);
    Route::get('product/{id}', [ProductController::class, 'Show']);
    Route::put('product/{id}', [ProductController::class, 'Update']);
    Route::delete('product/{id}', [ProductController::class, 'Delete']);

    Route::get('graphql/', [ProductController::class, 'GraphqlIndex']);
});

