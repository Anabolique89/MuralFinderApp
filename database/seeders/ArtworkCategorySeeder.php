<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Artwork;
use App\Models\ArtworkCategory;

class ArtworkCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $categories = [
            'Street Art',
            'Photography',
            'Painting',
            'Sculpture',
            'Digital Art',
            'Mixed Media'
        ];

        // Insert categories into the database
        foreach ($categories as $category) {
            DB::table('artwork_categories')->insert([
                'name' => $category,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Fetch all categories and artworks
        $artworkCategories = ArtworkCategory::all();
        $artworks = Artwork::all();

        // Assign each artwork a random category
        foreach ($artworks as $artwork) {
            $artwork->category()->associate($artworkCategories->random());
            $artwork->save();
        }
    }
}
