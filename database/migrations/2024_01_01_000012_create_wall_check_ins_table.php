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
        Schema::create('wall_check_ins', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('wall_id')->constrained('walls')->onDelete('cascade');
            $table->text('note')->nullable(); // User's note about the check-in
            $table->json('images')->nullable(); // Photos taken during check-in
            $table->decimal('latitude', 10, 8); // Actual check-in location
            $table->decimal('longitude', 11, 8);
            $table->decimal('accuracy', 8, 2)->nullable(); // GPS accuracy in meters
            $table->boolean('is_verified')->default(false); // If location matches wall location
            $table->enum('visit_purpose', ['viewing', 'painting', 'photography', 'maintenance', 'other'])->nullable();
            $table->unsignedInteger('duration_minutes')->nullable(); // How long they stayed
            $table->json('companions')->nullable(); // Other users who were with them
            $table->boolean('is_public')->default(true); // Whether check-in is visible to others
            $table->timestamps();

            // Indexes
            $table->index(['user_id', 'created_at']);
            $table->index(['wall_id', 'created_at']);
            $table->index(['latitude', 'longitude']);
            $table->index('is_verified');
            $table->index('visit_purpose');
            $table->index('is_public');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wall_check_ins');
    }
};
