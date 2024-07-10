<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('artwork_images', function (Blueprint $table) {
            $table->dropForeign(['artwork_id']);
            $table->foreign('artwork_id')->references('id')->on('artworks')->onDelete('cascade');
        });

        Schema::table('likes', function (Blueprint $table) {
            $table->dropForeign(['artwork_id']);
            $table->foreign('artwork_id')->references('id')->on('artworks')->onDelete('cascade');
        });

        Schema::table('artwork_comments', function (Blueprint $table) {
            $table->dropForeign(['artwork_id']);
            $table->foreign('artwork_id')->references('id')->on('artworks')->onDelete('cascade');
        });


        Schema::table('post_likes', function (Blueprint $table) {
            $table->dropForeign(['post_id']);
            $table->foreign('post_id')->references('id')->on('posts')->onDelete('cascade');
        });

        Schema::table('post_comments', function (Blueprint $table) {
            $table->dropForeign(['post_id']);
            $table->foreign('post_id')->references('id')->on('posts')->onDelete('cascade');
        });

        Schema::table('fellowships', function (Blueprint $table) {
            $table->dropForeign(['follower_id']);
            $table->dropForeign(['following_id']);
            $table->foreign('follower_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('following_id')->references('id')->on('users')->onDelete('cascade');
        });

        Schema::table('profiles', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });

        Schema::table('artworks', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });

        Schema::table('posts', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('artwork_images', function (Blueprint $table) {
            $table->dropForeign(['artwork_id']);
            $table->foreign('artwork_id')->references('id')->on('artworks');
        });

        Schema::table('likes', function (Blueprint $table) {
            $table->dropForeign(['artwork_id']);
            $table->foreign('artwork_id')->references('id')->on('artworks');
        });

        Schema::table('artwork_comments', function (Blueprint $table) {
            $table->dropForeign(['artwork_id']);
            $table->foreign('artwork_id')->references('id')->on('artworks');
        });


        Schema::table('post_likes', function (Blueprint $table) {
            $table->dropForeign(['post_id']);
            $table->foreign('post_id')->references('id')->on('posts');
        });

        Schema::table('post_comments', function (Blueprint $table) {
            $table->dropForeign(['post_id']);
            $table->foreign('post_id')->references('id')->on('posts');
        });

        Schema::table('fellowships', function (Blueprint $table) {
            $table->dropForeign(['follower_id']);
            $table->dropForeign(['following_id']);
            $table->foreign('follower_id')->references('id')->on('users');
            $table->foreign('following_id')->references('id')->on('users');
        });

        Schema::table('profiles', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->foreign('user_id')->references('id')->on('users');
        });

        Schema::table('artworks', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->foreign('user_id')->references('id')->on('users');
        });

        Schema::table('posts', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->foreign('user_id')->references('id')->on('users');
        });
    }
};
