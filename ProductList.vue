<template>
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Header con controles de filtro y búsqueda -->
    <div class="flex flex-wrap gap-4 mb-6 items-center justify-between">
      <!-- Búsqueda -->
      <div class="relative w-full md:w-auto">
        <input 
          type="text" 
          v-model="search" 
          placeholder="Buscar productos..." 
          @input="debouncedSearch"
          class="w-full md:w-80 px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-colors dark:bg-gray-800 dark:border-gray-700 dark:text-white"
        />
      </div>
      
      <!-- Filtro por categoría -->
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
      
      <!-- Controles de ordenamiento -->
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

    <!-- Mensaje de error -->
    <div v-if="error" class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded dark:bg-red-900 dark:text-red-200">
      {{ error }}
    </div>

    <!-- Estado de carga -->
    <div v-if="loading" class="flex justify-center items-center py-12">
      <div class="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-blue-500"></div>
    </div>

    <!-- Grid de productos -->
    <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
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
      
      <!-- Mensaje si no hay productos -->
      <div 
        v-if="products.length === 0 && !loading" 
        class="col-span-full text-center py-12 text-gray-500 dark:text-gray-400"
      >
        No se encontraron productos.
      </div>
    </div>

    <!-- Paginación -->
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

// Interfaces/Types
interface Category {
  id: number;
  name: string;
  slug: string;
}

interface Product {
  id: number;
  name: string;
  description: string | null;
  price: string;
  stock: number;
  category: Category;
}

interface Meta {
  current_page: number;
  total: number;
  per_page: number;
}

// Estado reactivo
const products = ref<Product[]>([]);
const categories = ref<Category[]>([]);
const loading = ref<boolean>(false);
const error = ref<string | null>(null);
const search = ref<string>('');
const selectedCategory = ref<number | null>(null);
const sort = ref<string>('created_at');
const direction = ref<'asc' | 'desc'>('desc');
const page = ref<number>(1);
const meta = ref<Meta | null>(null);

// Función para formatear precio en formato chileno
const formatPrecioChileno = (precio: string | number): string => {
  // Convertir a número si es string
  const precioNumero = typeof precio === 'string' ? parseFloat(precio) : precio;
  
  // Formatear sin decimales y con separador de miles
  return '$' + Math.round(precioNumero).toLocaleString('es-CL');
};

// Método para alternar la dirección de ordenamiento
const toggleDirection = () => {
  direction.value = direction.value === 'asc' ? 'desc' : 'asc';
};

// Debounce para la búsqueda
const debouncedSearch = debounce(() => {
  page.value = 1; // Resetear a primera página en búsqueda
}, 300);

// Cargar categorías
const fetchCategories = async () => {
  try {
    const res = await axios.get('/api/categories');
    categories.value = res.data.data;
  } catch (e) {
    console.error('Error al cargar categorías:', e);
    categories.value = [];
  }
};

// Obtener productos con filtros, orden y paginación
const fetchProducts = async () => {
  loading.value = true;
  error.value = null;
  try {
    const params = {
      search: search.value || undefined,
      category: selectedCategory.value || undefined,
      sort: sort.value,
      direction: direction.value,
      page: page.value,
    };
    const res = await axios.get('/api/products', { params });
    products.value = res.data.data;
    meta.value = res.data.meta;
  } catch (e: any) {
    error.value = 'Error al cargar productos';
    console.error(e);
  } finally {
    loading.value = false;
  }
};

// Actualizar productos al cambiar filtros, orden o página
watch([search, selectedCategory, sort, direction, page], fetchProducts);

// Inicializar datos al montar el componente
onMounted(() => {
  fetchCategories();
  fetchProducts();
});
</script>