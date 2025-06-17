<?php

use App\Http\Controllers\ProductListController;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


// Rutas para el e-commerce
Route::get('/products', ProductListController::class);

Route::get('/categories', function () {
    return response()->json([
        'data' => Category::select('id', 'name', 'slug')->get()
    ]);
});

