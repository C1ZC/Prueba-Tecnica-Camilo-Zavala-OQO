<?php
<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

/**
 * Controlador para gestionar la lista de productos del e-commerce.
 * 
 * Esta clase es responsable de obtener productos filtrados, ordenados y paginados
 * según los parámetros de la solicitud. Se implementa como un controlador de acción única
 * (invokable controller) que maneja solicitudes GET a la ruta /api/products.
 */
class ProductListController extends Controller
{
    /**
     * Maneja la solicitud entrante para obtener productos.
     * 
     * Procesa los parámetros de filtrado, ordenamiento y paginación para devolver
     * un conjunto de productos que cumplan con los criterios especificados.
     *
     * @param  \Illuminate\Http\Request  $request  La solicitud HTTP con los parámetros
     * @return \Illuminate\Http\JsonResponse      Respuesta JSON con productos y metadatos
     */
    public function __invoke(Request $request)
    {
        // Obtener y validar parámetros de consulta
        $sort = $request->input('sort', 'created_at'); // Campo de ordenamiento (default: fecha creación)
        $direction = $request->input('direction', 'desc'); // Dirección de ordenamiento (default: descendente)
        $category = $request->input('category'); // ID de categoría para filtrar (opcional)
        $search = $request->input('search'); // Término de búsqueda por nombre (opcional)
        
        // Iniciar la consulta base incluyendo solo productos activos y su relación con categoría
        $query = Product::where('is_active', true)->with('category');
        
        // Aplicar filtro por categoría si se especificó
        if ($category) {
            $query->where('category_id', $category);
        }
        
        // Aplicar búsqueda por nombre si se especificó un término
        if ($search) {
            $query->where('name', 'like', "%{$search}%");
        }
        
        // Validar que el campo de ordenamiento sea permitido (seguridad contra SQL injection)
        $validSortFields = ['name', 'price', 'created_at'];
        if (!in_array($sort, $validSortFields)) {
            $sort = 'created_at'; // Valor seguro por defecto
        }
        
        // Validar que la dirección de ordenamiento sea permitida
        $validDirections = ['asc', 'desc'];
        if (!in_array($direction, $validDirections)) {
            $direction = 'desc'; // Valor seguro por defecto
        }
        
        // Aplicar el ordenamiento a la consulta
        $query->orderBy($sort, $direction);
        
        // Ejecutar la consulta con paginación (15 productos por página)
        $products = $query->paginate(15);
        
        // Transformar los resultados para adaptar el formato de respuesta
        // Esto asegura que solo se devuelvan los campos necesarios y con la estructura esperada
        $transformedProducts = $products->through(function ($product) {
            return [
                'id' => $product->id,
                'name' => $product->name,
                'description' => $product->description,
                'price' => $product->price,
                'stock' => $product->stock,
                'category' => [
                    'id' => $product->category->id,
                    'name' => $product->category->name,
                    'slug' => $product->category->slug,
                ],
            ];
        });
        
        // Estructurar y devolver la respuesta JSON con datos y metadatos de paginación
        return response()->json([
            'data' => $transformedProducts->items(), // Productos transformados
            'meta' => [
                'current_page' => $products->currentPage(), // Página actual
                'total' => $products->total(),              // Total de productos
                'per_page' => $products->perPage(),         // Productos por página
            ],
        ]);
    }
}