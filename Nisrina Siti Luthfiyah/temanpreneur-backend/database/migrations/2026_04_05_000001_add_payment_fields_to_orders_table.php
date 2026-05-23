<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('payment_method')->nullable()->after('status'); // transfer, ewallet, cod
            $table->decimal('shipping_cost', 12, 2)->default(0)->after('payment_method');
            $table->decimal('total_amount', 12, 2)->nullable()->after('shipping_cost'); // subtotal + shipping
            $table->string('shipping_address')->nullable()->after('total_amount');
            $table->string('shipping_phone')->nullable()->after('shipping_address');
            $table->string('shipping_name')->nullable()->after('shipping_phone');
        });
    }

    public function down()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['payment_method', 'shipping_cost', 'total_amount', 'shipping_address', 'shipping_phone', 'shipping_name']);
        });
    }
};
