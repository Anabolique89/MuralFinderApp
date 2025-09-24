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
        Schema::create('comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->morphs('commentable'); // commentable_id and commentable_type
            $table->foreignId('parent_id')->nullable()->constrained('comments')->onDelete('cascade'); // For nested comments
            $table->text('content');
            $table->json('mentions')->nullable(); // User mentions as JSON array
            $table->boolean('is_edited')->default(false);
            $table->timestamp('edited_at')->nullable();

            // Status and moderation
            $table->enum('status', ['published', 'under_review', 'rejected', 'hidden'])->default('published');
            $table->text('rejection_reason')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('reviewed_at')->nullable();

            // Statistics
            $table->unsignedInteger('likes_count')->default(0);
            $table->unsignedInteger('replies_count')->default(0);

            $table->timestamps();
            $table->softDeletes();

            // Indexes for performance (morphs already creates commentable_type, commentable_id index)
            $table->index(['user_id', 'status']);
            $table->index(['parent_id', 'created_at']);
            $table->index(['status', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('comments');
    }
};
