<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('profiles', function (Blueprint $table) {
            // Change the column name and data type from 'age' to 'dob'
            $table->date('dob')->after('bio')->nullable();
            $table->dropColumn('age');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('profiles', function (Blueprint $table) {
            // Change the column name and data type from 'dob' to 'age'
            $table->integer('age')->after('bio')->nullable();
            $table->dropColumn('dob');
        });
    }
};
