<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Productos de Electrónicos
        $electronicosId = Category::where('slug', 'electronicos')->first()->id;
        $electronicos = [
            [
                'name' => 'Smartphone Galaxy S22',
                'description' => 'Último modelo con cámara de alta resolución y batería de larga duración',
                'price' => 679990,
                'stock' => 25,
            ],
            [
                'name' => 'Laptop ProBook 15"',
                'description' => 'Procesador i7, 16GB RAM, 512GB SSD',
                'price' => 1099990,
                'stock' => 10,
            ],
            [
                'name' => 'Tablet UltraSlim 10.5"',
                'description' => 'Pantalla HD, 64GB almacenamiento, perfecta para entretenimiento',
                'price' => 299990,
                'stock' => 15,
            ],
            [
                'name' => 'Audífonos Bluetooth NoiseCancel',
                'description' => 'Cancelación de ruido activa, 30h de batería',
                'price' => 169990,
                'stock' => 30,
            ],
        ];
        
        // Productos de Ropa
        $ropaId = Category::where('slug', 'ropa')->first()->id;
        $ropa = [
            [
                'name' => 'Chaqueta de Cuero Premium',
                'description' => 'Cuero genuino, forro interior térmico, estilo clásico',
                'price' => 219990,
                'stock' => 8,
            ],
            [
                'name' => 'Jeans Slim Fit',
                'description' => 'Tejido elástico, múltiples tallas disponibles',
                'price' => 49990,
                'stock' => 35,
            ],
            [
                'name' => 'Camisa Formal Elegance',
                'description' => 'Algodón 100%, corte ajustado, fácil planchado',
                'price' => 39990,
                'stock' => 20,
            ],
            [
                'name' => 'Zapatos de Cuero Urban',
                'description' => 'Suela antideslizante, interior acolchado',
                'price' => 75990,
                'stock' => 12,
            ],
        ];
        
        // Productos de Hogar
        $hogarId = Category::where('slug', 'hogar')->first()->id;
        $hogar = [
            [
                'name' => 'Juego de Sábanas Premium',
                'description' => '100% algodón egipcio, 400 hilos, juego completo',
                'price' => 69990,
                'stock' => 18,
            ],
            [
                'name' => 'Set de Cocina Profesional',
                'description' => '10 piezas, acero inoxidable, mangos ergonómicos',
                'price' => 109990,
                'stock' => 7,
            ],
            [
                'name' => 'Lámpara de Pie Moderna',
                'description' => 'Luz LED ajustable, diseño minimalista',
                'price' => 59990,
                'stock' => 15,
            ],
            [
                'name' => 'Alfombra Luxury',
                'description' => 'Tejido suave, antialérgica, 2x3 metros',
                'price' => 99990,
                'stock' => 5,
            ],
        ];
        
        // Productos de Deportes
        $deportesId = Category::where('slug', 'deportes')->first()->id;
        $deportes = [
            [
                'name' => 'Bicicleta Montaña Pro',
                'description' => '21 velocidades, suspensión delantera, frenos de disco',
                'price' => 429990,
                'stock' => 6,
            ],
            [
                'name' => 'Zapatillas Running Performance',
                'description' => 'Amortiguación avanzada, transpirables, ligeras',
                'price' => 109990,
                'stock' => 22,
            ],
            [
                'name' => 'Set de Pesas Ajustables',
                'description' => 'Ajuste rápido de peso, incluye soporte',
                'price' => 149990,
                'stock' => 8,
            ],
            [
                'name' => 'Balón de Fútbol Oficial',
                'description' => 'Tamaño reglamentario, cosido a mano',
                'price' => 34990,
                'stock' => 25,
            ],
        ];
        
        // Productos de Belleza
        $bellezaId = Category::where('slug', 'belleza')->first()->id;
        $belleza = [
            [
                'name' => 'Set de Maquillaje Profesional',
                'description' => '24 tonos de sombras, base, corrector y más',
                'price' => 75990,
                'stock' => 12,
            ],
            [
                'name' => 'Perfume Elegance Night',
                'description' => 'Fragancia duradera con notas florales y amaderadas',
                'price' => 59990,
                'stock' => 15,
            ],
            [
                'name' => 'Crema Hidratante Anti-edad',
                'description' => 'Fórmula con retinol y vitamina C, uso diario',
                'price' => 49990,
                'stock' => 20,
            ],
            [
                'name' => 'Secadora de Cabello Profesional',
                'description' => '2000W, tecnología iónica, 3 temperaturas',
                'price' => 67990,
                'stock' => 9,
            ],
        ];
        
        // Insertar todos los productos
        $this->insertProducts($electronicos, $electronicosId);
        $this->insertProducts($ropa, $ropaId);
        $this->insertProducts($hogar, $hogarId);
        $this->insertProducts($deportes, $deportesId);
        $this->insertProducts($belleza, $bellezaId);
    }
    
    private function insertProducts($products, $categoryId)
    {
        foreach ($products as $product) {
            Product::create([
                'name' => $product['name'],
                'description' => $product['description'],
                'price' => $product['price'],
                'stock' => $product['stock'],
                'category_id' => $categoryId,
                'is_active' => true,
            ]);
        }
    }
}
