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
        Schema::create('likes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->morphs('likeable'); // likeable_id and likeable_type
            $table->enum('reaction_type', ['like', 'love', 'wow', 'laugh', 'sad', 'angry'])->default('like');
            $table->timestamps();

            // Ensure unique likes per user per item
            $table->unique(['user_id', 'likeable_id', 'likeable_type']);

            // Indexes for performance (morphs already creates likeable_type, likeable_id index)
            $table->index(['user_id', 'created_at']);
            $table->index('reaction_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('likes');
    }
};
