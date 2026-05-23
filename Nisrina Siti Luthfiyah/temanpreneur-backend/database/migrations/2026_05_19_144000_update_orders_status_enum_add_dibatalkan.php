<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('orders')) {
            DB::statement("ALTER TABLE `orders` MODIFY `status` ENUM('diproses','dikemas','diantarkan','selesai','dibatalkan') NOT NULL DEFAULT 'diproses'");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('orders')) {
            DB::statement("ALTER TABLE `orders` MODIFY `status` ENUM('diproses','dikemas','diantarkan','selesai') NOT NULL DEFAULT 'diproses'");
        }
    }
};
