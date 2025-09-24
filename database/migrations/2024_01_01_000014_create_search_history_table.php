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
        Schema::create('search_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade'); // Null for anonymous searches
            $table->string('query'); // The search term
            $table->enum('search_type', ['artworks', 'posts', 'walls', 'users', 'global'])->default('global');
            $table->json('filters')->nullable(); // Applied filters as JSON
            $table->unsignedInteger('results_count')->default(0); // Number of results returned
            $table->boolean('had_results')->default(true); // Whether search returned any results
            $table->string('ip_address')->nullable();
            $table->decimal('latitude', 10, 8)->nullable(); // Location when search was made
            $table->decimal('longitude', 11, 8)->nullable();
            $table->string('device_type')->nullable();
            $table->unsignedInteger('click_position')->nullable(); // Which result was clicked (1-based)
            $table->unsignedBigInteger('clicked_result_id')->nullable(); // What was clicked (artwork, post, etc.)
            $table->string('clicked_result_type')->nullable();
            $table->timestamp('searched_at');

            // Indexes for analytics and suggestions
            $table->index(['user_id', 'searched_at']);
            $table->index(['query', 'search_type']);
            $table->index(['search_type', 'searched_at']);
            $table->index('had_results');
            $table->index('searched_at');
            $table->index(['clicked_result_type', 'clicked_result_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('search_history');
    }
};
