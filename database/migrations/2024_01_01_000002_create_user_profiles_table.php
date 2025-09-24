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
        Schema::create('user_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->text('bio')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('profession')->nullable();
            $table->string('country')->nullable();
            $table->string('city')->nullable();
            $table->string('location')->nullable(); // General location string
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->string('profile_image_url')->nullable();
            $table->string('cover_image_url')->nullable();
            $table->string('website')->nullable();
            
            // Social media links
            $table->string('instagram')->nullable();
            $table->string('twitter')->nullable();
            $table->string('facebook')->nullable();
            $table->string('linkedin')->nullable();
            $table->string('tiktok')->nullable();
            
            // Privacy settings
            $table->boolean('is_profile_public')->default(true);
            $table->boolean('show_location')->default(true);
            $table->boolean('show_email')->default(false);
            
            // Statistics
            $table->unsignedInteger('followers_count')->default(0);
            $table->unsignedInteger('following_count')->default(0);
            $table->unsignedInteger('artworks_count')->default(0);
            $table->unsignedInteger('posts_count')->default(0);
            
            $table->timestamps();

            // Indexes
            $table->unique('user_id');
            $table->index(['country', 'city']);
            $table->index(['latitude', 'longitude']);
            $table->index('is_profile_public');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_profiles');
    }
};
