<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->string('type')->default('string'); // string, integer, boolean, json
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // Insert default settings
        DB::table('settings')->insert([
            [
                'key' => 'ai_generation_limit_free',
                'value' => '10',
                'type' => 'integer',
                'description' => 'Maximum AI generations for free tier users (lifetime)',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'key' => 'ai_generation_limit_basic',
                'value' => '50',
                'type' => 'integer',
                'description' => 'Maximum AI generations for basic tier subscribers (lifetime)',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'key' => 'ai_generation_limit_premium',
                'value' => '200',
                'type' => 'integer',
                'description' => 'Maximum AI generations for premium tier subscribers (lifetime)',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'key' => 'ai_generation_limit_pro',
                'value' => '1000',
                'type' => 'integer',
                'description' => 'Maximum AI generations for pro tier subscribers (lifetime)',
                'created_at' => now(),
                'updated_at' => now()
            ]
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
