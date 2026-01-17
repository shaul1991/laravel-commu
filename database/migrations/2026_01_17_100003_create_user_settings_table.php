<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_settings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->comment('users 테이블의 id 참조');
            $table->boolean('email_on_comment')->default(true);
            $table->boolean('email_on_reply')->default(true);
            $table->boolean('email_on_follow')->default(true);
            $table->boolean('email_on_like')->default(false);
            $table->boolean('push_enabled')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->unique('user_id');
        });

        // Add soft deletes to users table
        Schema::table('users', function (Blueprint $table) {
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::dropIfExists('user_settings');
    }
};
