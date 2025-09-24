<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // Seller
            $table->string('name');
            $table->text('description');
            $table->string('slug')->unique();
            $table->json('images')->nullable(); // Product images as JSON array
            $table->string('primary_image')->nullable();
            
            // Pricing
            $table->decimal('price', 10, 2);
            $table->decimal('original_price', 10, 2)->nullable(); // For discounts
            $table->string('currency', 3)->default('USD');
            $table->boolean('is_negotiable')->default(false);
            
            // Product details
            $table->enum('type', ['artwork', 'print', 'merchandise', 'commission', 'service', 'other'])->default('artwork');
            $table->enum('condition', ['new', 'like_new', 'good', 'fair', 'poor'])->default('new');
            $table->json('tags')->nullable();
            $table->json('materials')->nullable(); // Materials used
            $table->json('dimensions')->nullable(); // Width, height, depth
            $table->decimal('weight', 8, 2)->nullable(); // Weight in kg
            
            // Availability
            $table->enum('status', ['draft', 'active', 'sold', 'reserved', 'inactive'])->default('draft');
            $table->unsignedInteger('quantity')->default(1);
            $table->boolean('is_unique')->default(true); // One-of-a-kind artwork
            $table->boolean('is_digital')->default(false);
            
            // Location and shipping
            $table->string('location')->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->boolean('local_pickup')->default(true);
            $table->boolean('shipping_available')->default(false);
            $table->decimal('shipping_cost', 8, 2)->nullable();
            $table->json('shipping_regions')->nullable(); // Where they ship to
            
            // Related content
            $table->foreignId('artwork_id')->nullable()->constrained('artworks')->onDelete('set null');
            $table->foreignId('category_id')->nullable()->constrained('categories')->onDelete('set null');
            
            // Statistics
            $table->unsignedInteger('views_count')->default(0);
            $table->unsignedInteger('favorites_count')->default(0);
            $table->unsignedInteger('inquiries_count')->default(0);
            
            // SEO
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index(['user_id', 'status']);
            $table->index(['type', 'status']);
            $table->index(['status', 'created_at']);
            $table->index(['price', 'currency']);
            $table->index(['latitude', 'longitude']);
            $table->index('slug');
            $table->index(['category_id', 'status']);
            $table->index('is_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
