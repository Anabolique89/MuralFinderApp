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
        Schema::table('walls', function (Blueprint $table) {
            // Drop existing foreign key constraint if it exists
            $table->dropForeign(['added_by']);
            
            // Add new foreign key with ON DELETE CASCADE
            $table->foreign('added_by')
                  ->references('id')->on('users')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('walls', function (Blueprint $table) {
            // Drop the foreign key constraint
            $table->dropForeign(['added_by']);
            
            // Re-add the foreign key constraint without ON DELETE CASCADE
            $table->foreign('added_by')
                  ->references('id')->on('users');
        });
    }
};
