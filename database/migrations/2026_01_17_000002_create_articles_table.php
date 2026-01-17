<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('articles', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('author_id')->constrained('users')->onDelete('cascade');
            $table->string('title', 200);
            $table->string('slug')->unique();
            $table->text('content_markdown');
            $table->text('content_html');
            $table->string('category', 50);
            $table->string('status', 20)->default('draft');
            $table->unsignedInteger('view_count')->default(0);
            $table->unsignedInteger('like_count')->default(0);
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'published_at']);
            $table->index(['author_id', 'status']);
            $table->index('category');
        });

        Schema::create('article_likes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('article_id')->constrained('articles')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->timestamp('created_at');

            $table->unique(['article_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('article_likes');
        Schema::dropIfExists('articles');
    }
};
