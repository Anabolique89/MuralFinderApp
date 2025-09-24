<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\UserProfile;
use App\Models\Category;
use App\Models\Artwork;
use App\Models\Wall;
use App\Models\Post;
use App\Models\Product;
use App\Models\Like;
use App\Models\Comment;
use Carbon\Carbon;

class ComprehensiveTestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🎨 Starting comprehensive test data seeding...');

        // Clear existing data
        $this->command->info('🧹 Clearing existing data...');

        // Disable foreign key checks for SQLite
        if (DB::getDriverName() === 'sqlite') {
            DB::statement('PRAGMA foreign_keys = OFF;');
        } else {
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        }

        $tables = ['likes', 'comments', 'artworks', 'posts', 'products', 'user_profiles', 'walls', 'users', 'categories'];
        foreach ($tables as $table) {
            if (DB::getDriverName() === 'sqlite') {
                DB::statement("DELETE FROM {$table}");
                DB::statement("DELETE FROM sqlite_sequence WHERE name='{$table}'");
            } else {
                DB::table($table)->truncate();
            }
        }

        // Re-enable foreign key checks
        if (DB::getDriverName() === 'sqlite') {
            DB::statement('PRAGMA foreign_keys = ON;');
        } else {
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        }

        // Seed Categories first
        $this->seedCategories();

        // Seed Users and Profiles
        $this->seedUsers();

        // Seed Walls
        $this->seedWalls();

        // Seed Artworks
        $this->seedArtworks();

        // Seed Posts/Blogs
        $this->seedPosts();

        // Seed Products (Shop)
        $this->seedProducts();

        // Seed Interactions (Likes, Comments)
        $this->seedInteractions();

        $this->command->info('✅ Comprehensive test data seeding completed!');
    }

    private function seedCategories()
    {
        $this->command->info('📂 Seeding categories...');

        $categories = [
            ['name' => 'Street Art', 'slug' => 'street-art', 'description' => 'Urban street art and graffiti', 'color_code' => '#FF6B6B', 'icon' => 'spray-can'],
            ['name' => 'Murals', 'slug' => 'murals', 'description' => 'Large-scale wall paintings', 'color_code' => '#4ECDC4', 'icon' => 'paint-brush'],
            ['name' => 'Stencil Art', 'slug' => 'stencil-art', 'description' => 'Stencil-based artwork', 'color_code' => '#45B7D1', 'icon' => 'stencil'],
            ['name' => 'Abstract', 'slug' => 'abstract', 'description' => 'Abstract art forms', 'color_code' => '#96CEB4', 'icon' => 'abstract'],
            ['name' => 'Portrait', 'slug' => 'portrait', 'description' => 'Portrait artwork', 'color_code' => '#FFEAA7', 'icon' => 'user'],
            ['name' => 'Typography', 'slug' => 'typography', 'description' => 'Text-based art', 'color_code' => '#DDA0DD', 'icon' => 'font'],
            ['name' => 'Nature', 'slug' => 'nature', 'description' => 'Nature themes', 'color_code' => '#98D8C8', 'icon' => 'leaf'],
            ['name' => 'Political', 'slug' => 'political', 'description' => 'Political commentary', 'color_code' => '#F7DC6F', 'icon' => 'megaphone'],
        ];

        foreach ($categories as $index => $category) {
            Category::create([
                'name' => $category['name'],
                'slug' => $category['slug'],
                'description' => $category['description'],
                'color_code' => $category['color_code'],
                'icon' => $category['icon'],
                'sort_order' => $index + 1,
                'is_active' => true,
                'artworks_count' => 0,
            ]);
        }
    }

    private function seedUsers()
    {
        $this->command->info('👥 Seeding users and profiles...');

        // Create test admin user
        $admin = User::create([
            'username' => 'admin',
            'email' => 'admin@muralfinder.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'status' => 'active',
            'email_verified_at' => now(),
        ]);

        UserProfile::create([
            'user_id' => $admin->id,
            'first_name' => 'Admin',
            'last_name' => 'User',
            'bio' => 'System administrator and art enthusiast',
            'location' => 'New York, NY',
            'website' => 'https://muralfinder.com',
            'instagram' => '@muralfinder',
            'twitter' => '@muralfinder',
            'is_profile_public' => true,
        ]);

        // Create test artist users
        $artists = [
            ['username' => 'banksy_fan', 'email' => 'artist1@test.com', 'first_name' => 'Alex', 'last_name' => 'Rivera', 'bio' => 'Street artist inspired by urban culture'],
            ['username' => 'mural_master', 'email' => 'artist2@test.com', 'first_name' => 'Maya', 'last_name' => 'Chen', 'bio' => 'Large-scale mural specialist'],
            ['username' => 'stencil_king', 'email' => 'artist3@test.com', 'first_name' => 'Jordan', 'last_name' => 'Smith', 'bio' => 'Stencil art and political commentary'],
            ['username' => 'color_queen', 'email' => 'artist4@test.com', 'first_name' => 'Sofia', 'last_name' => 'Garcia', 'bio' => 'Abstract colorist and nature lover'],
        ];

        foreach ($artists as $artistData) {
            $user = User::create([
                'username' => $artistData['username'],
                'email' => $artistData['email'],
                'password' => Hash::make('password'),
                'role' => 'artist',
                'status' => 'active',
                'email_verified_at' => now(),
            ]);

            UserProfile::create([
                'user_id' => $user->id,
                'first_name' => $artistData['first_name'],
                'last_name' => $artistData['last_name'],
                'bio' => $artistData['bio'],
                'location' => fake()->city() . ', ' . fake()->country(),
                'instagram' => '@' . $artistData['username'],
                'is_profile_public' => true,
                'followers_count' => rand(50, 1000),
                'following_count' => rand(20, 200),
                'artworks_count' => 0,
            ]);
        }

        // Create art lover users
        for ($i = 1; $i <= 10; $i++) {
            $user = User::create([
                'username' => 'artlover' . $i,
                'email' => "artlover{$i}@test.com",
                'password' => Hash::make('password'),
                'role' => 'artlover',
                'status' => 'active',
                'email_verified_at' => now(),
            ]);

            UserProfile::create([
                'user_id' => $user->id,
                'first_name' => fake()->firstName(),
                'last_name' => fake()->lastName(),
                'bio' => 'Art enthusiast and street art lover',
                'location' => fake()->city() . ', ' . fake()->country(),
                'is_profile_public' => true,
                'followers_count' => rand(10, 100),
                'following_count' => rand(50, 300),
            ]);
        }
    }

    private function seedWalls()
    {
        $this->command->info('🧱 Seeding walls...');

        $locations = [
            ['name' => 'Downtown Art District', 'lat' => 40.7128, 'lng' => -74.0060, 'city' => 'New York'],
            ['name' => 'Venice Beach Boardwalk', 'lat' => 34.0522, 'lng' => -118.2437, 'city' => 'Los Angeles'],
            ['name' => 'Wynwood Walls', 'lat' => 25.7617, 'lng' => -80.1918, 'city' => 'Miami'],
            ['name' => 'Mission District', 'lat' => 37.7749, 'lng' => -122.4194, 'city' => 'San Francisco'],
            ['name' => 'Shoreditch Street Art', 'lat' => 51.5074, 'lng' => -0.1278, 'city' => 'London'],
            ['name' => 'Berlin Wall Memorial', 'lat' => 52.5200, 'lng' => 13.4050, 'city' => 'Berlin'],
            ['name' => 'Hosier Lane', 'lat' => -37.8136, 'lng' => 144.9631, 'city' => 'Melbourne'],
            ['name' => 'Valparaíso Hills', 'lat' => -33.0472, 'lng' => -71.6127, 'city' => 'Valparaíso'],
        ];

        foreach ($locations as $location) {
            Wall::create([
                'name' => $location['name'],
                'description' => "Famous street art location in {$location['city']}",
                'location_text' => $location['name'] . ', ' . $location['city'],
                'address' => $location['name'] . ', ' . $location['city'],
                'city' => $location['city'],
                'country' => 'Various',
                'latitude' => $location['lat'],
                'longitude' => $location['lng'],
                'status' => 'verified',
                'wall_type' => 'building',
                'surface_type' => 'brick',
                'height' => rand(3, 15),
                'width' => rand(5, 30),
                'is_legal' => true,
                'requires_permission' => false,
                'artworks_count' => 0,
                'added_by' => 1, // Admin user
                'verified_by' => 1,
                'verified_at' => now(),
            ]);
        }
    }

    private function seedArtworks()
    {
        $this->command->info('🎨 Seeding artworks...');

        $artworkTitles = [
            'Urban Dreams', 'City Pulse', 'Street Symphony', 'Concrete Canvas', 'Neon Nights',
            'Revolution Wall', 'Abstract Emotions', 'Portrait of Hope', 'Nature Reclaims',
            'Typography Tales', 'Political Statement', 'Color Explosion', 'Stencil Stories',
            'Mural Masterpiece', 'Graffiti Glory', 'Wall Wisdom', 'Street Sermon',
            'Urban Poetry', 'Concrete Confessions', 'Art Rebellion'
        ];

        $descriptions = [
            'A vibrant piece exploring urban life and modern society',
            'Bold colors and dynamic shapes representing city energy',
            'Intricate stencil work with powerful social commentary',
            'Large-scale mural celebrating community and diversity',
            'Abstract composition using spray paint and mixed media',
            'Portrait series highlighting local heroes and activists',
            'Nature-inspired artwork bringing green to concrete spaces',
            'Typography-based piece featuring inspirational quotes',
            'Political statement addressing current social issues',
            'Colorful explosion of creativity and artistic expression'
        ];

        $artists = User::where('role', 'artist')->get();
        $categories = Category::all();
        $walls = Wall::all();

        for ($i = 0; $i < 50; $i++) {
            $artist = $artists->random();
            $category = $categories->random();
            $wall = $walls->random();

            Artwork::create([
                'title' => $artworkTitles[array_rand($artworkTitles)],
                'description' => $descriptions[array_rand($descriptions)],
                'user_id' => $artist->id,
                'category_id' => $category->id,
                'wall_id' => $wall->id,
                'primary_image_path' => '/storage/artworks/sample-artwork-' . ($i + 1) . '.jpg',
                'images' => [
                    '/storage/artworks/sample-artwork-' . ($i + 1) . '.jpg',
                    '/storage/artworks/sample-artwork-' . ($i + 1) . '-2.jpg'
                ],
                'thumbnail_path' => '/storage/artworks/thumbs/sample-artwork-' . ($i + 1) . '-thumb.jpg',
                'tags' => ['street-art', 'urban', 'colorful', 'creative'],
                'colors' => ['#FF6B6B', '#4ECDC4', '#45B7D1', '#96CEB4'],
                'style' => ['graffiti', 'mural', 'stencil', 'mosaic', 'sculpture', 'installation', 'other'][rand(0, 6)],
                'technique' => ['spray_paint', 'brush', 'marker', 'stencil', 'digital', 'mixed_media', 'other'][rand(0, 6)],
                'created_date' => Carbon::now()->subDays(rand(1, 365)),
                'is_commissioned' => rand(0, 1),
                'latitude' => $wall->latitude + (rand(-100, 100) / 10000),
                'longitude' => $wall->longitude + (rand(-100, 100) / 10000),
                'location_text' => $wall->address,
                'status' => 'published',
                'likes_count' => rand(5, 500),
                'comments_count' => rand(0, 50),
                'views_count' => rand(50, 2000),
                'shares_count' => rand(0, 100),
                'rating' => rand(35, 50) / 10, // 3.5 to 5.0
                'ratings_count' => rand(5, 100),
                'slug' => strtolower(str_replace(' ', '-', $artworkTitles[array_rand($artworkTitles)])) . '-' . $i,
                'is_featured' => rand(0, 10) === 0, // 10% chance of being featured
            ]);
        }

        // Update category artwork counts
        foreach ($categories as $category) {
            $count = Artwork::where('category_id', $category->id)->count();
            $category->update(['artworks_count' => $count]);
        }

        // Update wall artwork counts
        foreach ($walls as $wall) {
            $count = Artwork::where('wall_id', $wall->id)->count();
            $wall->update(['artworks_count' => $count]);
        }
    }

    private function seedPosts()
    {
        $this->command->info('📝 Seeding blog posts...');

        $postTitles = [
            'The Evolution of Street Art in Modern Cities',
            'Top 10 Street Artists You Should Know',
            'How to Start Your Street Art Journey',
            'The Legal Side of Street Art',
            'Street Art vs Graffiti: Understanding the Difference',
            'Best Cities for Street Art Tourism',
            'Preserving Street Art for Future Generations',
            'The Role of Technology in Modern Street Art',
            'Community Impact of Public Art Projects',
            'Street Art Techniques Every Beginner Should Learn'
        ];

        $authors = User::whereIn('role', ['admin', 'artist'])->get();
        $categories = Category::all();

        foreach ($postTitles as $index => $title) {
            Post::create([
                'title' => $title,
                'slug' => strtolower(str_replace(' ', '-', $title)),
                'content' => 'This is a comprehensive blog post about ' . strtolower($title) . '. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.',
                'excerpt' => 'A brief introduction to ' . strtolower($title) . ' and its importance in the street art community.',
                'user_id' => $authors->random()->id,
                'category_id' => $categories->random()->id,
                'featured_image' => '/storage/posts/blog-' . ($index + 1) . '.jpg',
                'status' => 'published',
                'is_featured' => rand(0, 3) === 0, // 25% chance
                'views_count' => rand(100, 5000),
                'likes_count' => rand(10, 200),
                'comments_count' => rand(0, 50),
                'published_at' => Carbon::now()->subDays(rand(1, 180)),
            ]);
        }
    }

    private function seedProducts()
    {
        $this->command->info('🛍️ Seeding shop products...');

        $products = [
            ['name' => 'Street Art Spray Paint Set', 'category' => 'Art Supplies', 'price' => 29.99],
            ['name' => 'Graffiti Stencil Kit', 'category' => 'Art Supplies', 'price' => 19.99],
            ['name' => 'Urban Art Photography Book', 'category' => 'Books', 'price' => 39.99],
            ['name' => 'Street Art History Guide', 'category' => 'Books', 'price' => 24.99],
            ['name' => 'Banksy Inspired Wallpaper', 'category' => 'Wallpapers', 'price' => 49.99],
            ['name' => 'Abstract Mural Wallpaper', 'category' => 'Wallpapers', 'price' => 44.99],
            ['name' => 'Professional Marker Set', 'category' => 'Art Supplies', 'price' => 34.99],
            ['name' => 'Street Art Techniques Manual', 'category' => 'Books', 'price' => 29.99],
        ];

        $categories = Category::all();

        $users = User::all();

        foreach ($products as $index => $productData) {
            Product::create([
                'user_id' => $users->random()->id,
                'name' => $productData['name'],
                'slug' => strtolower(str_replace(' ', '-', $productData['name'])),
                'description' => 'High-quality ' . strtolower($productData['name']) . ' perfect for street artists and art enthusiasts.',
                'price' => $productData['price'],
                'category_id' => $categories->random()->id,
                'primary_image' => '/storage/products/product-' . ($index + 1) . '.jpg',
                'images' => [
                    '/storage/products/product-' . ($index + 1) . '.jpg',
                    '/storage/products/product-' . ($index + 1) . '-2.jpg'
                ],
                'quantity' => rand(10, 100),
                'status' => 'active',
                'type' => 'merchandise',
                'condition' => 'new',
                'currency' => 'USD',
                'local_pickup' => true,
                'shipping_available' => true,
                'shipping_cost' => rand(5, 15),
            ]);
        }
    }

    private function seedInteractions()
    {
        $this->command->info('💬 Seeding likes and comments...');

        $users = User::all();
        $artworks = Artwork::all();

        // Seed likes for artworks
        foreach ($artworks as $artwork) {
            $likeCount = rand(5, 50);
            $randomUsers = $users->random(min($likeCount, $users->count()));

            foreach ($randomUsers as $user) {
                Like::create([
                    'user_id' => $user->id,
                    'likeable_type' => Artwork::class,
                    'likeable_id' => $artwork->id,
                ]);
            }
        }

        // Seed comments for artworks
        $comments = [
            'Amazing work! Love the colors.',
            'This is incredible street art!',
            'Beautiful piece, very inspiring.',
            'Great technique and style.',
            'This brightens up the whole neighborhood.',
            'Fantastic use of space and color.',
            'Really powerful message.',
            'Love the detail in this piece.',
            'This is why I love street art!',
            'Brilliant work by a talented artist.'
        ];

        foreach ($artworks as $artwork) {
            $commentCount = rand(0, 10);

            for ($i = 0; $i < $commentCount; $i++) {
                Comment::create([
                    'user_id' => $users->random()->id,
                    'commentable_type' => Artwork::class,
                    'commentable_id' => $artwork->id,
                    'content' => $comments[array_rand($comments)],
                    'status' => 'published',
                ]);
            }
        }
    }
}
