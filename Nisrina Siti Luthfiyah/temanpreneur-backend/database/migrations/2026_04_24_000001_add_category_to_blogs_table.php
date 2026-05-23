<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('blogs')) {
            Schema::table('blogs', function (Blueprint $table) {
                if (!Schema::hasColumn('blogs', 'category')) {
                    $table->string('category')->nullable()->after('slug')->default('umum')->index();
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('blogs')) {
            Schema::table('blogs', function (Blueprint $table) {
                if (Schema::hasColumn('blogs', 'category')) {
                    $table->dropColumn('category');
                }
            });
        }
    }
};
