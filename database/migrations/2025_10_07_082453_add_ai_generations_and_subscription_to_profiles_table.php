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
        Schema::table('user_profiles', function (Blueprint $table) {
            // AI Generation tracking - lifetime count (never decreases)
            $table->unsignedInteger('ai_generations_count')->default(0)->after('posts_count');

            // Subscription fields for future implementation
            $table->enum('subscription_tier', ['free', 'basic', 'premium', 'pro'])->default('free')->after('ai_generations_count');
            $table->enum('subscription_status', ['active', 'cancelled', 'expired', 'trial'])->nullable()->after('subscription_tier');
            $table->timestamp('subscription_expires_at')->nullable()->after('subscription_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_profiles', function (Blueprint $table) {
            $table->dropColumn([
                'ai_generations_count',
                'subscription_tier',
                'subscription_status',
                'subscription_expires_at'
            ]);
        });
    }
};
