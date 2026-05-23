<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('cancellation_reason', 500)->nullable()->after('status');
            $table->timestamp('cancelled_at')->nullable()->after('cancellation_reason');
            $table->unsignedBigInteger('cancelled_by')->nullable()->after('cancelled_at');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['cancellation_reason', 'cancelled_at', 'cancelled_by']);
        });
    }
};
