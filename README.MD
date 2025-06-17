# E-commerce - Prueba Técnica OQO

Este proyecto implementa una aplicación e-commerce con un listado de productos filtrable y ordenable, desarrollada como parte de una prueba técnica para OQO.

## Tecnologías Utilizadas

- **Backend**: Laravel 12
- **Frontend**: Vue.js 3 con TypeScript
- **CSS**: Tailwind CSS
- **Base de datos**: SQLite
- **Paquetes adicionales**: axios, lodash (debounce)

## Decisiones Técnicas Tomadas

### Backend
- **Patrón Invocable Controller**: Implementé un controlador invocable (`ProductListController`) para manejar la única acción de listar productos, siguiendo el principio de responsabilidad única.
- **Eager Loading**: Utilicé `with('category')` para cargar relaciones anticipadamente y evitar el problema N+1 en consultas.
- **API RESTful**: Diseñé endpoints que siguen las convenciones REST, devolviendo respuestas JSON estructuradas con metadatos de paginación.
- **Validación de Parámetros**: Implementé validación de parámetros de consulta para evitar ordenamiento por campos no permitidos.

### Frontend
- **Composition API con `<script setup>`**: Utilicé la sintaxis más moderna de Vue 3 para un código más limpio y mejor soporte TypeScript.
- **TypeScript**: Definí interfaces para garantizar la seguridad de tipos y mejorar la mantenibilidad.
- **Diseño Responsivo**: Implementé un diseño adaptable a diferentes tamaños de pantalla usando Tailwind CSS.
- **Soporte para Dark Mode**: Añadí compatibilidad con el modo oscuro usando las clases `dark:` de Tailwind.
- **Optimización de Rendimiento**: Implementé debounce para la búsqueda y transformé los datos en el servidor para minimizar la transferencia de datos.

### Base de Datos
- **Relaciones Eloquent**: Utilicé relaciones `hasMany` y `belongsTo` para vincular productos y categorías.
- **Seeders**: Creé seeders para generar datos de prueba realistas con precios en formato chileno.
- **Migraciones**: Diseñé esquemas de tabla con los campos necesarios y restricciones de integridad referencial.

## Uso de IA

Se utilizó GitHub Copilot durante el desarrollo para:
- Generar la estructura inicial de los modelos y migraciones
- Asistencia en la implementación del controlador y componente Vue
- Optimización del diseño con Tailwind CSS
- Generación de datos de prueba (seeders)
- Depuración y resolución de errores en la configuración de rutas API

En todos los casos, el código generado fue revisado, adaptado y probado manualmente para asegurar su correcto funcionamiento.

## Consideraciones Especiales

- **Formato de Precios**: Implementé una función especial para mostrar precios en formato chileno (sin decimales y con símbolo $).
- **Compatibilidad con Dark Mode**: El diseño se adapta automáticamente al modo oscuro del sistema.
- **Filtros Combinados**: Es posible aplicar simultáneamente filtros por categoría y búsqueda por texto.
- **Experiencia de Usuario**: Añadí indicadores de carga, mensajes de error claros y animaciones sutiles para mejorar la experiencia.

## Configuración del Proyecto

```bash
# Clonar el repositorio
git clone <repositorio>
cd ecommerce

# Instalar dependencias
composer install
npm install

# Configurar .env para usar SQLite
# DB_CONNECTION=sqlite
# DB_DATABASE=ruta_absoluta_a/database/database.sqlite

# Ejecutar migraciones y sembrar datos
php artisan migrate:fresh --seed

# Ejecutar el servidor de desarrollo
php artisan serve
composer run dev

