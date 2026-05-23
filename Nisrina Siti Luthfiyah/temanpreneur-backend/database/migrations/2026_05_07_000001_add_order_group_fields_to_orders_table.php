<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'business_id')) {
                $table->foreignId('business_id')->nullable()->after('buyer_id')->constrained('businesses')->nullOnDelete();
            }

            if (!Schema::hasColumn('orders', 'order_group_id')) {
                $table->foreignId('order_group_id')->nullable()->after('business_id')->constrained('order_groups')->nullOnDelete();
            }

            if (!Schema::hasColumn('orders', 'payment_method')) {
                $table->string('payment_method')->default('transfer')->after('status');
            }

            if (!Schema::hasColumn('orders', 'shipping_cost')) {
                $table->decimal('shipping_cost', 12, 2)->default(0)->after('payment_method');
            }

            if (!Schema::hasColumn('orders', 'total_amount')) {
                $table->decimal('total_amount', 12, 2)->nullable()->after('shipping_cost');
            }

            if (!Schema::hasColumn('orders', 'shipping_address')) {
                $table->string('shipping_address')->nullable()->after('total_amount');
            }

            if (!Schema::hasColumn('orders', 'shipping_phone')) {
                $table->string('shipping_phone')->nullable()->after('shipping_address');
            }

            if (!Schema::hasColumn('orders', 'shipping_name')) {
                $table->string('shipping_name')->nullable()->after('shipping_phone');
            }
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasColumn('orders', 'shipping_name')) {
                $table->dropColumn('shipping_name');
            }

            if (Schema::hasColumn('orders', 'shipping_phone')) {
                $table->dropColumn('shipping_phone');
            }

            if (Schema::hasColumn('orders', 'shipping_address')) {
                $table->dropColumn('shipping_address');
            }

            if (Schema::hasColumn('orders', 'total_amount')) {
                $table->dropColumn('total_amount');
            }

            if (Schema::hasColumn('orders', 'shipping_cost')) {
                $table->dropColumn('shipping_cost');
            }

            if (Schema::hasColumn('orders', 'payment_method')) {
                $table->dropColumn('payment_method');
            }

            if (Schema::hasColumn('orders', 'order_group_id')) {
                $table->dropForeign(['order_group_id']);
                $table->dropColumn('order_group_id');
            }

            if (Schema::hasColumn('orders', 'business_id')) {
                $table->dropForeign(['business_id']);
                $table->dropColumn('business_id');
            }
        });
    }
};
