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
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sender_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('recipient_id')->constrained('users')->onDelete('cascade');
            $table->text('content');
            $table->json('attachments')->nullable(); // Images, files, etc.
            $table->unsignedBigInteger('related_to_id')->nullable(); // Related artwork, post, product, etc.
            $table->string('related_to_type')->nullable();

            // Message status
            $table->boolean('is_read')->default(false);
            $table->timestamp('read_at')->nullable();
            $table->boolean('is_deleted_by_sender')->default(false);
            $table->boolean('is_deleted_by_recipient')->default(false);

            // Message type and metadata
            $table->enum('type', ['text', 'image', 'file', 'system', 'inquiry'])->default('text');
            $table->json('metadata')->nullable(); // Additional message data

            $table->timestamps();

            // Indexes
            $table->index(['sender_id', 'created_at']);
            $table->index(['recipient_id', 'is_read', 'created_at']);
            $table->index(['sender_id', 'recipient_id', 'created_at']);
            $table->index(['related_to_type', 'related_to_id']);
            $table->index('type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};
