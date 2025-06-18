<?php

use App\Http\Controllers\ProductListController;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/**
 * API Routes para el E-commerce
 * 
 * Este archivo contiene las definiciones de rutas para la API del e-commerce,
 * permitiendo acceder a productos y categorías.
 */

/**
 * Ruta para obtener productos
 * 
 * Devuelve una lista paginada de productos que puede ser filtrada y ordenada.
 * Acepta parámetros de consulta:
 * - search: Para filtrar por nombre
 * - category: Para filtrar por ID de categoría
 * - sort: Para ordenar (name, price, created_at)
 * - direction: Dirección del ordenamiento (asc, desc)
 * - page: Número de página para la paginación
 */
Route::get('/products', ProductListController::class);

/**
 * Ruta para obtener categorías
 * 
 * Devuelve la lista completa de categorías disponibles para el filtrado.
 * El formato de respuesta incluye id, name y slug de cada categoría.
 */
Route::get('/categories', function () {
    return response()->json([
        'data' => Category::select('id', 'name', 'slug')->get()
    ]);
});
