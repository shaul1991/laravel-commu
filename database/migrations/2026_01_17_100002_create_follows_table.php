<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('follows', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('follower_id')->comment('users 테이블의 id 참조 (팔로워)');
            $table->unsignedBigInteger('following_id')->comment('users 테이블의 id 참조 (팔로잉)');
            $table->timestamps();

            $table->unique(['follower_id', 'following_id']);
            $table->index('follower_id');
            $table->index('following_id');
        });

        // Add follower_count and following_count to users table
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedInteger('follower_count')->default(0);
            $table->unsignedInteger('following_count')->default(0);
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['follower_count', 'following_count']);
        });

        Schema::dropIfExists('follows');
    }
};
