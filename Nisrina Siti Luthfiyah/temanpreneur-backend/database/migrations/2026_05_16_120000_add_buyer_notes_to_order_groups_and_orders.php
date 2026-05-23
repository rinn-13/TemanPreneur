<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('order_groups', function (Blueprint $table) {
            if (!Schema::hasColumn('order_groups', 'buyer_notes')) {
                $table->text('buyer_notes')->nullable()->after('grand_total');
            }
        });

        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'buyer_notes')) {
                $table->text('buyer_notes')->nullable()->after('shipping_name');
            }
        });
    }

    public function down(): void
    {
        Schema::table('order_groups', function (Blueprint $table) {
            if (Schema::hasColumn('order_groups', 'buyer_notes')) {
                $table->dropColumn('buyer_notes');
            }
        });

        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasColumn('orders', 'buyer_notes')) {
                $table->dropColumn('buyer_notes');
            }
        });
    }
};
