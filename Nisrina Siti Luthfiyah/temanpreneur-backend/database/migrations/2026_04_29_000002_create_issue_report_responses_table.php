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
        Schema::create('issue_report_responses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('issue_report_id')->constrained('issue_reports')->onDelete('cascade');
            $table->foreignId('admin_id')->constrained('users')->onDelete('cascade');
            $table->text('response_message');
            $table->enum('action_type', ['notify_seller', 'cancel_order', 'refund', 'replacement', 'warning', 'block_seller', 'other'])->comment('Tipe aksi yang diambil');
            $table->json('action_details')->nullable()->comment('Detail aksi (JSON)');
            $table->enum('status', ['pending', 'in_progress', 'completed'])->default('pending');
            $table->timestamp('notified_at')->nullable()->comment('Kapan seller diberitahu');
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('issue_report_responses');
    }
};
