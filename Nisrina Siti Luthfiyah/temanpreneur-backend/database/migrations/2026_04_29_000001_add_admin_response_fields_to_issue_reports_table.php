<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('issue_reports', function (Blueprint $table) {
            // Admin response fields
            $table->foreignId('admin_id')->nullable()->constrained('users')->onDelete('set null');
            $table->text('admin_response')->nullable()->comment('Respons/penanganan dari admin');
            $table->timestamp('admin_response_at')->nullable();
            $table->enum('resolution_type', ['cancel_order', 'refund', 'replacement', 'warning', 'block_seller', 'other'])->nullable()->comment('Tipe penyelesaian');
            $table->enum('refund_status', ['pending', 'approved', 'processed', 'rejected'])->default('pending');
            $table->decimal('refund_amount', 10, 2)->nullable();
            $table->boolean('seller_contacted')->default(false);
            $table->timestamp('seller_contacted_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('issue_reports', function (Blueprint $table) {
            $table->dropForeign(['admin_id']);
            $table->dropColumn('admin_id');
            $table->dropColumn('admin_response');
            $table->dropColumn('admin_response_at');
            $table->dropColumn('resolution_type');
            $table->dropColumn('refund_status');
            $table->dropColumn('refund_amount');
            $table->dropColumn('seller_contacted');
            $table->dropColumn('seller_contacted_at');
        });
    }
};
