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
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reporter_id')->constrained('users')->onDelete('cascade'); // Who reported
            $table->foreignId('reported_user_id')->nullable()->constrained('users')->onDelete('cascade'); // Reported user
            $table->morphs('reportable'); // What was reported (artwork, post, comment, etc.)

            // Report details
            $table->enum('type', [
                'inappropriate_content',
                'spam',
                'harassment',
                'copyright_violation',
                'fake_artwork',
                'offensive_language',
                'violence',
                'hate_speech',
                'misinformation',
                'other'
            ]);
            $table->text('reason'); // Detailed reason
            $table->json('evidence')->nullable(); // Screenshots, links, etc.

            // Status and resolution
            $table->enum('status', ['pending', 'under_review', 'resolved', 'dismissed', 'escalated'])->default('pending');
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('reviewed_at')->nullable();
            $table->text('resolution_notes')->nullable();
            $table->enum('action_taken', [
                'none',
                'warning_sent',
                'content_removed',
                'user_suspended',
                'user_banned',
                'content_edited',
                'other'
            ])->nullable();

            // Priority and categorization
            $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium');
            $table->boolean('is_anonymous')->default(false); // Anonymous report
            $table->string('ip_address')->nullable();
            $table->json('metadata')->nullable(); // Additional context

            $table->timestamps();

            // Indexes (morphs already creates reportable_type, reportable_id index)
            $table->index(['reporter_id', 'created_at']);
            $table->index(['reported_user_id', 'created_at']);
            $table->index(['type', 'status']);
            $table->index(['status', 'priority']);
            $table->index(['reviewed_by', 'reviewed_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};
