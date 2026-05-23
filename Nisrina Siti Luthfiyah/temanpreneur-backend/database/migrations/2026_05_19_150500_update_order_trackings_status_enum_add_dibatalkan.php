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
        if (Schema::hasTable('order_trackings')) {
            DB::statement("ALTER TABLE `order_trackings` MODIFY `status` ENUM('diproses','dikemas','diantarkan','selesai','dibatalkan') NOT NULL");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('order_trackings')) {
            DB::statement("ALTER TABLE `order_trackings` MODIFY `status` ENUM('diproses','dikemas','diantarkan','selesai') NOT NULL");
        }
    }
};
