<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE issue_reports MODIFY status ENUM('open','in_progress','closed') DEFAULT 'open'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE issue_reports MODIFY status ENUM('pending','in_progress','completed') DEFAULT 'pending'");
    }
};
