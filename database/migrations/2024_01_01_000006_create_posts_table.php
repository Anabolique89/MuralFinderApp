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
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('title');
            $table->longText('content');
            $table->text('excerpt')->nullable(); // Short description for previews
            $table->string('featured_image')->nullable();
            $table->json('images')->nullable(); // Multiple images as JSON array
            $table->json('tags')->nullable(); // Tags as JSON array
            $table->string('slug')->nullable();
            
            // Post type and categorization
            $table->enum('type', ['article', 'discussion', 'question', 'showcase', 'event', 'news'])->default('article');
            $table->foreignId('category_id')->nullable()->constrained('categories')->onDelete('set null');
            
            // Status and moderation
            $table->enum('status', ['draft', 'published', 'under_review', 'rejected', 'archived'])->default('published');
            $table->text('rejection_reason')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamp('published_at')->nullable();
            
            // Engagement and interaction
            $table->boolean('allow_comments')->default(true);
            $table->boolean('is_pinned')->default(false);
            $table->boolean('is_featured')->default(false);
            $table->timestamp('featured_at')->nullable();
            
            // Statistics
            $table->unsignedInteger('likes_count')->default(0);
            $table->unsignedInteger('comments_count')->default(0);
            $table->unsignedInteger('views_count')->default(0);
            $table->unsignedInteger('shares_count')->default(0);
            
            // SEO
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index(['user_id', 'status']);
            $table->index(['category_id', 'status']);
            $table->index(['status', 'published_at']);
            $table->index(['type', 'status']);
            $table->index(['is_featured', 'featured_at']);
            $table->index(['is_pinned', 'published_at']);
            $table->index('slug');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};
