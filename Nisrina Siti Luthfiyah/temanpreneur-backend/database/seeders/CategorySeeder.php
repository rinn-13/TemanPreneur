<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Fashion', 'icon' => '👗', 'description' => 'Pakaian, aksesoris fashion'],
            ['name' => 'Kuliner', 'icon' => '🍱', 'description' => 'Makanan, minuman, snack'],
            ['name' => 'Kerajinan', 'icon' => '🎨', 'description' => 'Handmade, kerajinan tangan'],
            ['name' => 'Digital', 'icon' => '💻', 'description' => 'Template, desain digital'],
            ['name' => 'Aksesoris', 'icon' => '💍', 'description' => 'Perhiasan, aksesoris'],
            ['name' => 'Lainnya', 'icon' => '📦', 'description' => 'Kategori lainnya'],
        ];

        foreach ($categories as $cat) {
            Category::firstOrCreate(
                ['slug' => \Illuminate\Support\Str::slug($cat['name'])],
                $cat
            );
        }
    }
}

