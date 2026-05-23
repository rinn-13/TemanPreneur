<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * order_groups = 1 sesi checkout (bisa berisi banyak toko)
     */
    public function up(): void
    {
        Schema::create('order_groups', function (Blueprint $table) {
            $table->id();
            $table->string('group_code')->unique(); // e.g. GRP-20260505-A1B2C3
            $table->foreignId('buyer_id')->constrained('users')->onDelete('cascade');

            // Informasi pengiriman (digunakan oleh semua orders dalam group ini)
            $table->string('shipping_name');
            $table->string('shipping_phone')->nullable();
            $table->text('shipping_address')->nullable();

            // Metode pembayaran dipilih sekali untuk seluruh group
            $table->string('payment_method')->default('transfer'); // transfer | ewallet | cod

            // Agregasi finansial
            $table->decimal('total_items_price', 14, 2)->default(0); // subtotal semua toko
            $table->decimal('total_shipping_cost', 14, 2)->default(0); // ongkir semua toko
            $table->decimal('grand_total', 14, 2)->default(0);       // total bayar

            $table->timestamps();

            $table->index('buyer_id');
            $table->index('group_code');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_groups');
    }
};
