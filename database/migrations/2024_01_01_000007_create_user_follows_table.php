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
        Schema::create('user_follows', function (Blueprint $table) {
            $table->id();
            $table->foreignId('follower_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('following_id')->constrained('users')->onDelete('cascade');
            $table->boolean('is_mutual')->default(false); // If both users follow each other
            $table->timestamp('followed_at')->useCurrent();
            $table->timestamps();

            // Ensure unique follow relationships
            $table->unique(['follower_id', 'following_id']);
            
            // Indexes for performance
            $table->index('follower_id');
            $table->index('following_id');
            $table->index(['follower_id', 'followed_at']);
            $table->index(['following_id', 'followed_at']);
            $table->index('is_mutual');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_follows');
    }
};
