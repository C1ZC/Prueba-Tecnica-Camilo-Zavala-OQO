<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class ProductListController extends Controller
{
    /**
     * Maneja la solicitud entrante.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function __invoke(Request $request)
    {
        // Obtener parámetros de consulta
        $sort = $request->input('sort', 'created_at'); // Ordenar por defecto por fecha de creación
        $direction = $request->input('direction', 'desc'); // Dirección descendente por defecto
        $category = $request->input('category');
        $search = $request->input('search');
        
        // Iniciar la consulta para productos activos
        $query = Product::where('is_active', true)->with('category');
        
        // Aplicar filtro por categoría si existe
        if ($category) {
            $query->where('category_id', $category);
        }
        
        // Aplicar búsqueda por nombre si existe
        if ($search) {
            $query->where('name', 'like', "%{$search}%");
        }
        
        // Verificar que el campo de ordenamiento sea válido
        $validSortFields = ['name', 'price', 'created_at'];
        if (!in_array($sort, $validSortFields)) {
            $sort = 'created_at'; // Valor por defecto si no es válido
        }
        
        // Verificar que la dirección sea válida
        $validDirections = ['asc', 'desc'];
        if (!in_array($direction, $validDirections)) {
            $direction = 'desc'; // Valor por defecto si no es válido
        }
        
        // Aplicar ordenamiento
        $query->orderBy($sort, $direction);
        
        // Obtener resultados paginados
        $products = $query->paginate(15);
        
        // Transformar los datos para el formato de respuesta esperado
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
        
        // Retornar respuesta JSON estructurada
        return response()->json([
            'data' => $transformedProducts->items(),
            'meta' => [
                'current_page' => $products->currentPage(),
                'total' => $products->total(),
                'per_page' => $products->perPage(),
            ],
        ]);
    }
}
