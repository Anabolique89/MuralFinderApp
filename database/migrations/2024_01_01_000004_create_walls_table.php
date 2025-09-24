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
        Schema::create('walls', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->text('description')->nullable();
            $table->text('location_text'); // Human readable location
            $table->string('address')->nullable();
            $table->string('city')->nullable();
            $table->string('country')->nullable();
            $table->decimal('latitude', 10, 8);
            $table->decimal('longitude', 11, 8);
            $table->string('image_path')->nullable();
            $table->json('images')->nullable(); // Multiple images as JSON array
            $table->foreignId('added_by')->constrained('users')->onDelete('cascade');
            $table->foreignId('verified_by')->nullable()->constrained('users')->onDelete('set null');
            $table->enum('status', ['pending', 'verified', 'rejected'])->default('pending');
            $table->timestamp('verified_at')->nullable();
            $table->text('rejection_reason')->nullable();
            
            // Wall characteristics
            $table->enum('wall_type', ['building', 'fence', 'tunnel', 'bridge', 'other'])->nullable();
            $table->enum('surface_type', ['brick', 'concrete', 'metal', 'wood', 'other'])->nullable();
            $table->decimal('height', 5, 2)->nullable(); // Height in meters
            $table->decimal('width', 5, 2)->nullable(); // Width in meters
            $table->boolean('is_legal')->nullable(); // Legal to paint on
            $table->boolean('requires_permission')->default(true);
            
            // Statistics
            $table->unsignedInteger('artworks_count')->default(0);
            $table->unsignedInteger('likes_count')->default(0);
            $table->unsignedInteger('comments_count')->default(0);
            $table->unsignedInteger('check_ins_count')->default(0);
            $table->unsignedInteger('views_count')->default(0);
            
            $table->timestamps();
            $table->softDeletes();

            // Indexes for geospatial queries and performance
            $table->index(['latitude', 'longitude']);
            $table->index(['status', 'verified_at']);
            $table->index(['added_by', 'status']);
            $table->index(['city', 'country']);
            $table->index('wall_type');
            $table->index('is_legal');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('walls');
    }
};
