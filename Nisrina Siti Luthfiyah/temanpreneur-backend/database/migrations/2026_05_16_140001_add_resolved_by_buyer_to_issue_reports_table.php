<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('issue_reports', function (Blueprint $table) {
            $table->timestamp('resolved_by_buyer_at')->nullable()->after('status');
            $table->boolean('admin_locked')->default(false)->after('resolved_by_buyer_at');
        });
    }

    public function down(): void
    {
        Schema::table('issue_reports', function (Blueprint $table) {
            $table->dropColumn(['resolved_by_buyer_at', 'admin_locked']);
        });
    }
};
