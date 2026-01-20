<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasColumn('article_tags', 'updated_at')) {
            Schema::table('article_tags', function (Blueprint $table) {
                $table->timestamp('updated_at')->nullable()->after('created_at');
            });

            // Set updated_at to created_at for existing rows
            DB::table('article_tags')->whereNull('updated_at')->update([
                'updated_at' => DB::raw('created_at'),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Only drop if this migration created the column
        // Do nothing in rollback to avoid removing column that existed before
    }
};
