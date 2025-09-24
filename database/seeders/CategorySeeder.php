<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
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
                'name' => 'Street Art',
                'slug' => 'street-art',
                'description' => 'Urban street art and graffiti',
                'color_code' => '#FF6B6B',
                'icon' => 'spray-can',
                'sort_order' => 1,
            ],
            [
                'name' => 'Murals',
                'slug' => 'murals',
                'description' => 'Large-scale wall paintings and murals',
                'color_code' => '#4ECDC4',
                'icon' => 'paint-brush',
                'sort_order' => 2,
            ],
            [
                'name' => 'Stencil Art',
                'slug' => 'stencil-art',
                'description' => 'Stencil-based artwork and designs',
                'color_code' => '#45B7D1',
                'icon' => 'stencil',
                'sort_order' => 3,
            ],
            [
                'name' => 'Abstract',
                'slug' => 'abstract',
                'description' => 'Abstract and experimental art forms',
                'color_code' => '#96CEB4',
                'icon' => 'abstract',
                'sort_order' => 4,
            ],
            [
                'name' => 'Portrait',
                'slug' => 'portrait',
                'description' => 'Portrait and figure-based artwork',
                'color_code' => '#FFEAA7',
                'icon' => 'user',
                'sort_order' => 5,
            ],
            [
                'name' => 'Typography',
                'slug' => 'typography',
                'description' => 'Text-based and typographic art',
                'color_code' => '#DDA0DD',
                'icon' => 'font',
                'sort_order' => 6,
            ],
            [
                'name' => 'Nature',
                'slug' => 'nature',
                'description' => 'Nature and environmental themes',
                'color_code' => '#98D8C8',
                'icon' => 'leaf',
                'sort_order' => 7,
            ],
            [
                'name' => 'Political',
                'slug' => 'political',
                'description' => 'Political and social commentary',
                'color_code' => '#F7DC6F',
                'icon' => 'megaphone',
                'sort_order' => 8,
            ],
            [
                'name' => 'Pop Culture',
                'slug' => 'pop-culture',
                'description' => 'Pop culture references and characters',
                'color_code' => '#BB8FCE',
                'icon' => 'star',
                'sort_order' => 9,
            ],
            [
                'name' => 'Sculpture',
                'slug' => 'sculpture',
                'description' => '3D sculptures and installations',
                'color_code' => '#85C1E9',
                'icon' => 'cube',
                'sort_order' => 10,
            ],
        ];

        foreach ($categories as $category) {
            DB::table('categories')->insert([
                'name' => $category['name'],
                'slug' => $category['slug'],
                'description' => $category['description'],
                'color_code' => $category['color_code'],
                'icon' => $category['icon'],
                'sort_order' => $category['sort_order'],
                'is_active' => true,
                'artworks_count' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
