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
        Schema::table('artworks', function (Blueprint $table) {
            $table->boolean('ai_generated')->default(false)->after('comment_count');
            $table->string('ai_archetype')->nullable()->after('ai_generated');
            $table->string('ai_service')->nullable()->after('ai_archetype');
            $table->text('ai_prompt')->nullable()->after('ai_service');
            $table->string('ai_model_version')->nullable()->after('ai_prompt');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('artworks', function (Blueprint $table) {
            $table->dropColumn([
                'ai_generated',
                'ai_archetype',
                'ai_service',
                'ai_prompt',
                'ai_model_version'
            ]);
        });
    }
};
