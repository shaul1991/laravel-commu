<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('comments', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->unsignedBigInteger('article_id')->comment('articles 테이블의 id 참조');
            $table->unsignedBigInteger('author_id')->comment('users 테이블의 id 참조');
            $table->unsignedBigInteger('parent_id')->nullable()->comment('comments 테이블의 id 참조 (대댓글)');
            $table->text('content');
            $table->unsignedInteger('like_count')->default(0);
            $table->unsignedInteger('reply_count')->default(0);
            $table->boolean('is_deleted')->default(false);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['article_id', 'created_at']);
            $table->index(['author_id', 'created_at']);
            $table->index('parent_id');
        });

        Schema::create('comment_likes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('comment_id')->comment('comments 테이블의 id 참조');
            $table->unsignedBigInteger('user_id')->comment('users 테이블의 id 참조');
            $table->timestamps();

            $table->unique(['comment_id', 'user_id']);
            $table->index('comment_id');
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('comment_likes');
        Schema::dropIfExists('comments');
    }
};
