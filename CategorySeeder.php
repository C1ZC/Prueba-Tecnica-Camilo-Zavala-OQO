<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Electrónicos',
                'description' => 'Productos electrónicos y gadgets tecnológicos'
            ],
            [
                'name' => 'Ropa',
                'description' => 'Vestimenta y accesorios de moda'
            ],
            [
                'name' => 'Hogar',
                'description' => 'Artículos para el hogar y decoración'
            ],
            [
                'name' => 'Deportes',
                'description' => 'Equipo deportivo y accesorios para actividades físicas'
            ],
            [
                'name' => 'Belleza',
                'description' => 'Productos de belleza y cuidado personal'
            ],
        ];

        foreach ($categories as $category) {
            Category::create([
                'name' => $category['name'],
                'slug' => Str::slug($category['name']),
                'description' => $category['description'],
            ]);
        }
    }
}
