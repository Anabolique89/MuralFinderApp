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
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // Recipient
            $table->foreignId('actor_id')->nullable()->constrained('users')->onDelete('cascade'); // Who triggered the notification
            $table->string('type'); // Type of notification (like, comment, follow, etc.)
            $table->string('title');
            $table->text('message');
            $table->morphs('notifiable'); // Related object (artwork, post, wall, etc.)
            $table->json('data')->nullable(); // Additional notification data
            $table->string('action_url')->nullable(); // URL to navigate to when clicked
            $table->string('icon')->nullable(); // Icon for the notification
            $table->string('image_url')->nullable(); // Image for rich notifications

            // Status tracking
            $table->boolean('is_read')->default(false);
            $table->timestamp('read_at')->nullable();
            $table->boolean('is_sent_email')->default(false);
            $table->timestamp('email_sent_at')->nullable();
            $table->boolean('is_sent_push')->default(false);
            $table->timestamp('push_sent_at')->nullable();

            // Priority and grouping
            $table->enum('priority', ['low', 'normal', 'high', 'urgent'])->default('normal');
            $table->string('group_key')->nullable(); // For grouping similar notifications
            $table->boolean('is_grouped')->default(false);
            $table->unsignedInteger('group_count')->default(1);

            $table->timestamps();
            $table->softDeletes();

            // Indexes for performance (morphs already creates notifiable_type, notifiable_id index)
            $table->index(['user_id', 'is_read', 'created_at']);
            $table->index(['actor_id', 'created_at']);
            $table->index(['type', 'created_at']);
            $table->index(['group_key', 'is_grouped']);
            $table->index('priority');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
