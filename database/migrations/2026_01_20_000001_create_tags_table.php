<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tags', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('name', 50)->unique();
            $table->string('slug', 100)->unique();
            $table->unsignedInteger('article_count')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index('slug');
            $table->index('article_count');
        });

        Schema::create('article_tags', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('article_id')->comment('articles 테이블의 id 참조');
            $table->unsignedBigInteger('tag_id')->comment('tags 테이블의 id 참조');
            $table->timestamps();

            $table->unique(['article_id', 'tag_id']);
            $table->index('article_id');
            $table->index('tag_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('article_tags');
        Schema::dropIfExists('tags');
    }
};
