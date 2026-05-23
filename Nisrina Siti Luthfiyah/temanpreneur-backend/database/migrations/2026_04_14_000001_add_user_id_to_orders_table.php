<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'user_id')) {
                $table->foreignId('user_id')->nullable()->after('buyer_id')->constrained('users')->cascadeOnDelete();
            }
        });

        DB::table('orders')->whereNull('user_id')->update(['user_id' => DB::raw('buyer_id')]);
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasColumn('orders', 'user_id')) {
                $table->dropConstrainedForeignId('user_id');
            }
        });
    }
};
