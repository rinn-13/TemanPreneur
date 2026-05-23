<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::select(['id', 'name', 'slug'])
            ->orderBy('name')
            ->limit(20)
            ->get();

        if ($categories->isEmpty()) {
            $defaultCategories = [
                ['name' => 'Fashion', 'slug' => 'fashion'],
                ['name' => 'Kuliner', 'slug' => 'kuliner'],
                ['name' => 'Kerajinan', 'slug' => 'kerajinan'],
                ['name' => 'Digital', 'slug' => 'digital'],
                ['name' => 'Aksesoris', 'slug' => 'aksesoris'],
                ['name' => 'Lainnya', 'slug' => 'lainnya'],
            ];

            foreach ($defaultCategories as $categoryData) {
                Category::firstOrCreate(
                    ['slug' => $categoryData['slug']],
                    $categoryData
                );
            }

            $categories = Category::select(['id', 'name', 'slug'])
                ->orderBy('name')
                ->limit(20)
                ->get();
        }

        return response()->json([
            'data' => $categories,
        ], 200);
    }
}
