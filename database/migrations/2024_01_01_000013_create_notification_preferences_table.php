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
        Schema::create('notification_preferences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            
            // Email notifications
            $table->boolean('email_new_follower')->default(true);
            $table->boolean('email_artwork_liked')->default(true);
            $table->boolean('email_artwork_commented')->default(true);
            $table->boolean('email_post_liked')->default(true);
            $table->boolean('email_post_commented')->default(true);
            $table->boolean('email_wall_added_nearby')->default(true);
            $table->boolean('email_mentioned')->default(true);
            $table->boolean('email_weekly_digest')->default(true);
            $table->boolean('email_marketing')->default(false);
            
            // Push notifications
            $table->boolean('push_new_follower')->default(true);
            $table->boolean('push_artwork_liked')->default(true);
            $table->boolean('push_artwork_commented')->default(true);
            $table->boolean('push_post_liked')->default(true);
            $table->boolean('push_post_commented')->default(true);
            $table->boolean('push_wall_added_nearby')->default(true);
            $table->boolean('push_mentioned')->default(true);
            $table->boolean('push_live_events')->default(true);
            
            // In-app notifications
            $table->boolean('app_new_follower')->default(true);
            $table->boolean('app_artwork_liked')->default(true);
            $table->boolean('app_artwork_commented')->default(true);
            $table->boolean('app_post_liked')->default(true);
            $table->boolean('app_post_commented')->default(true);
            $table->boolean('app_wall_added_nearby')->default(true);
            $table->boolean('app_mentioned')->default(true);
            
            // Frequency settings
            $table->enum('email_frequency', ['immediate', 'daily', 'weekly', 'never'])->default('immediate');
            $table->enum('push_frequency', ['immediate', 'hourly', 'daily', 'never'])->default('immediate');
            
            // Quiet hours
            $table->time('quiet_hours_start')->nullable();
            $table->time('quiet_hours_end')->nullable();
            $table->string('timezone')->default('UTC');
            
            $table->timestamps();

            // Unique constraint
            $table->unique('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notification_preferences');
    }
};
