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
        Schema::create('artworks', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('category_id')->nullable()->constrained('categories')->onDelete('set null');
            $table->foreignId('wall_id')->nullable()->constrained('walls')->onDelete('set null');
            
            // Image handling
            $table->string('primary_image_path');
            $table->json('images')->nullable(); // Multiple images as JSON array
            $table->string('thumbnail_path')->nullable();
            
            // Artwork details
            $table->json('tags')->nullable(); // Tags as JSON array
            $table->json('colors')->nullable(); // Dominant colors as JSON array
            $table->enum('style', ['graffiti', 'mural', 'stencil', 'mosaic', 'sculpture', 'installation', 'other'])->nullable();
            $table->enum('technique', ['spray_paint', 'brush', 'marker', 'stencil', 'digital', 'mixed_media', 'other'])->nullable();
            $table->date('created_date')->nullable(); // When the artwork was actually created
            $table->boolean('is_commissioned')->default(false);
            $table->string('commissioner')->nullable();
            
            // Location (can be different from wall if artwork moved)
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->string('location_text')->nullable();
            
            // Status and moderation
            $table->enum('status', ['draft', 'published', 'under_review', 'rejected', 'archived'])->default('published');
            $table->text('rejection_reason')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('reviewed_at')->nullable();
            
            // Statistics
            $table->unsignedInteger('likes_count')->default(0);
            $table->unsignedInteger('comments_count')->default(0);
            $table->unsignedInteger('views_count')->default(0);
            $table->unsignedInteger('shares_count')->default(0);
            $table->decimal('rating', 3, 2)->default(0); // Average rating out of 5
            $table->unsignedInteger('ratings_count')->default(0);
            
            // SEO and discovery
            $table->string('slug')->nullable();
            $table->boolean('is_featured')->default(false);
            $table->timestamp('featured_at')->nullable();
            
            $table->timestamps();
            $table->softDeletes();

            // Indexes for performance
            $table->index(['user_id', 'status']);
            $table->index(['category_id', 'status']);
            $table->index(['wall_id', 'status']);
            $table->index(['status', 'created_at']);
            $table->index(['latitude', 'longitude']);
            $table->index(['is_featured', 'featured_at']);
            $table->index('slug');
            $table->index(['style', 'technique']);
            $table->index('created_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('artworks');
    }
};
