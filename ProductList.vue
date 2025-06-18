<template>
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Header con controles de filtro y búsqueda -->
    <div class="flex flex-wrap gap-4 mb-6 items-center justify-between">
      <!-- Búsqueda: Campo para filtrar productos por nombre -->
      <div class="relative w-full md:w-auto">
        <input 
          type="text" 
          v-model="search" 
          placeholder="Buscar productos..." 
          @input="debouncedSearch"
          class="w-full md:w-80 px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-colors dark:bg-gray-800 dark:border-gray-700 dark:text-white"
        />
      </div>
      
      <!-- Filtro por categoría: Desplegable para filtrar productos por categoría -->
      <div class="w-full md:w-auto">
        <select 
          v-model="selectedCategory"
          class="w-full md:w-auto px-4 py-2 rounded-lg border border-gray-300 bg-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-colors dark:bg-gray-800 dark:border-gray-700 dark:text-white"
        >
          <option :value="null">Todas las categorías</option>
          <option 
            v-for="category in categories" 
            :key="category.id" 
            :value="category.id"
          >
            {{ category.name }}
          </option>
        </select>
      </div>
      
      <!-- Controles de ordenamiento: Permiten ordenar por diferentes campos y dirección -->
      <div class="flex items-center gap-2 w-full md:w-auto">
        <select 
          v-model="sort"
          class="px-4 py-2 rounded-lg border border-gray-300 bg-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-colors dark:bg-gray-800 dark:border-gray-700 dark:text-white"
        >
          <option value="name">Nombre</option>
          <option value="price">Precio</option>
          <option value="created_at">Fecha</option>
        </select>
        <button 
          @click="toggleDirection"
          class="p-2 rounded-lg border border-gray-300 bg-gray-100 hover:bg-gray-200 transition-colors dark:bg-gray-700 dark:border-gray-600 dark:hover:bg-gray-600 dark:text-white"
        >
          {{ direction === 'asc' ? '↑' : '↓' }}
        </button>
      </div>
    </div>

    <!-- Mensaje de error: Se muestra cuando hay problemas al cargar los datos -->
    <div v-if="error" class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded dark:bg-red-900 dark:text-red-200">
      {{ error }}
    </div>

    <!-- Estado de carga: Indicador visual mientras se cargan los datos -->
    <div v-if="loading" class="flex justify-center items-center py-12">
      <div class="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-blue-500"></div>
    </div>

    <!-- Grid de productos: Muestra los productos en un layout responsivo -->
    <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
      <!-- Tarjeta de producto individual -->
      <div 
        v-for="product in products" 
        :key="product.id" 
        class="border border-gray-200 rounded-lg overflow-hidden shadow-sm hover:shadow-md transition-shadow bg-white dark:bg-gray-800 dark:border-gray-700"
      >
        <div class="p-5">
          <h3 class="text-lg font-semibold mb-2 text-gray-800 dark:text-white">{{ product.name }}</h3>
          <div class="inline-block px-2 py-1 text-xs font-medium bg-blue-500 text-white rounded-full mb-3">
            {{ product.category.name }}
          </div>
          <p class="text-sm text-gray-600 mb-4 line-clamp-3 dark:text-gray-300">{{ product.description }}</p>
          <div class="flex justify-between items-center">
            <div class="text-lg font-bold text-green-600 dark:text-green-400">{{ formatPrecioChileno(product.price) }}</div>
            <div class="text-xs text-gray-500 dark:text-gray-400">Stock: {{ product.stock }}</div>
          </div>
        </div>
      </div>
      
      <!-- Mensaje cuando no hay productos que mostrar -->
      <div 
        v-if="products.length === 0 && !loading" 
        class="col-span-full text-center py-12 text-gray-500 dark:text-gray-400"
      >
        No se encontraron productos.
      </div>
    </div>

    <!-- Controles de paginación: Permiten navegar entre páginas de resultados -->
    <div 
      v-if="meta && meta.total > 0" 
      class="flex justify-center items-center gap-4 mt-8"
    >
      <button 
        :disabled="meta.current_page === 1" 
        @click="page = meta.current_page - 1"
        class="px-4 py-2 bg-blue-500 text-white rounded-lg disabled:bg-gray-300 disabled:cursor-not-allowed hover:bg-blue-600 transition-colors dark:disabled:bg-gray-700"
      >
        Anterior
      </button>
      
      <span class="text-gray-700 dark:text-gray-300">
        Página {{ meta.current_page }} de {{ Math.ceil(meta.total / meta.per_page) }}
      </span>
      
      <button 
        :disabled="meta.current_page >= Math.ceil(meta.total / meta.per_page)" 
        @click="page = meta.current_page + 1"
        class="px-4 py-2 bg-blue-500 text-white rounded-lg disabled:bg-gray-300 disabled:cursor-not-allowed hover:bg-blue-600 transition-colors dark:disabled:bg-gray-700"
      >
        Siguiente
      </button>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, watch, onMounted } from 'vue';
import axios from 'axios';
import { debounce } from 'lodash';

/**
 * Definición de tipos e interfaces
 */

// Interfaz para el modelo de Categoría
interface Category {
  id: number;         // Identificador único de la categoría
  name: string;       // Nombre mostrado de la categoría
  slug: string;       // Slug para URLs amigables
}

// Interfaz para el modelo de Producto
interface Product {
  id: number;                    // Identificador único del producto
  name: string;                  // Nombre del producto
  description: string | null;    // Descripción del producto (puede ser nula)
  price: string;                 // Precio (como string para preservar decimales)
  stock: number;                 // Cantidad disponible en inventario
  category: Category;            // Categoría a la que pertenece el producto
}

// Interfaz para la información de paginación
interface Meta {
  current_page: number;    // Página actual
  total: number;           // Número total de productos
  per_page: number;        // Cantidad de productos por página
}

/**
 * Estado reactivo del componente
 */
const products = ref<Product[]>([]);                   // Lista de productos
const categories = ref<Category[]>([]);                // Lista de categorías disponibles
const loading = ref<boolean>(false);                   // Indicador de carga
const error = ref<string | null>(null);                // Mensaje de error (si existe)
const search = ref<string>('');                        // Término de búsqueda
const selectedCategory = ref<number | null>(null);     // Categoría seleccionada para filtrar
const sort = ref<string>('created_at');                // Campo por el que ordenar
const direction = ref<'asc' | 'desc'>('desc');         // Dirección del ordenamiento
const page = ref<number>(1);                           // Página actual
const meta = ref<Meta | null>(null);                   // Información de paginación

/**
 * Formatea un precio al formato de moneda chilena (CLP)
 * @param precio - El precio a formatear (string o número)
 * @returns String formateado con el precio en formato $X.XXX
 */
const formatPrecioChileno = (precio: string | number): string => {
  // Convertir a número si es string
  const precioNumero = typeof precio === 'string' ? parseFloat(precio) : precio;
  
  // Formatear sin decimales y con separador de miles
  return '$' + Math.round(precioNumero).toLocaleString('es-CL');
};

/**
 * Alterna la dirección de ordenamiento entre ascendente y descendente
 */
const toggleDirection = () => {
  direction.value = direction.value === 'asc' ? 'desc' : 'asc';
};

/**
 * Función con debounce para manejar la búsqueda de productos
 * Espera 300ms después de que el usuario deja de escribir para realizar la búsqueda
 * y reinicia la paginación al buscar
 */
const debouncedSearch = debounce(() => {
  page.value = 1; // Resetear a primera página en búsqueda
}, 300);

/**
 * Obtiene la lista de categorías desde la API
 */
const fetchCategories = async () => {
  try {
    const res = await axios.get('/api/categories');
    categories.value = res.data.data;
  } catch (e) {
    console.error('Error al cargar categorías:', e);
    categories.value = [];
  }
};

/**
 * Obtiene la lista de productos desde la API aplicando
 * los filtros, ordenamiento y paginación actuales
 */
const fetchProducts = async () => {
  loading.value = true;
  error.value = null;
  try {
    // Construir parámetros de consulta
    const params = {
      search: search.value || undefined,       // Término de búsqueda
      category: selectedCategory.value || undefined, // ID de categoría seleccionada
      sort: sort.value,                        // Campo de ordenamiento
      direction: direction.value,              // Dirección del ordenamiento
      page: page.value,                        // Número de página
    };
    
    // Realizar petición a la API
    const res = await axios.get('/api/products', { params });
    
    // Actualizar estado con los datos recibidos
    products.value = res.data.data;
    meta.value = res.data.meta;
  } catch (e: any) {
    error.value = 'Error al cargar productos';
    console.error(e);
  } finally {
    loading.value = false;
  }
};

/**
 * Observador que activa la carga de productos cuando cambia
 * cualquiera de los parámetros de filtrado, ordenamiento o paginación
 */
watch([search, selectedCategory, sort, direction, page], fetchProducts);

/**
 * Inicialización del componente: carga categorías y productos
 */
onMounted(() => {
  fetchCategories();
  fetchProducts();
});
</script>