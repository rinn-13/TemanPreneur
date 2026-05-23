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
        if (Schema::hasTable('blogs')) {
            Schema::table('blogs', function (Blueprint $table) {
                if (!Schema::hasColumn('blogs', 'slug')) {
                    $table->string('slug')->after('title')->nullable()->unique();
                }
                if (!Schema::hasColumn('blogs', 'excerpt')) {
                    $table->string('excerpt', 500)->nullable()->after('content');
                }
                if (!Schema::hasColumn('blogs', 'image')) {
                    $table->string('image')->nullable()->after('excerpt');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('blogs')) {
            Schema::table('blogs', function (Blueprint $table) {
                if (Schema::hasColumn('blogs', 'slug')) {
                    $table->dropColumn('slug');
                }
                if (Schema::hasColumn('blogs', 'excerpt')) {
                    $table->dropColumn('excerpt');
                }
                if (Schema::hasColumn('blogs', 'image')) {
                    $table->dropColumn('image');
                }
            });
        }
    }
};
