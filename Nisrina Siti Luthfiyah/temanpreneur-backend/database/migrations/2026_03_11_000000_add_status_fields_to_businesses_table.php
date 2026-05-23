<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('businesses', function (Blueprint $table) {
            $table->string('category')->nullable()->after('description');
            $table->string('phone')->nullable()->after('category');
            $table->string('status')->default('pending')->after('is_premium');
            $table->text('rejection_reason')->nullable()->after('status');
            $table->timestamp('processed_at')->nullable()->after('rejection_reason');
        });

        \DB::table('businesses')->where('is_verified', true)->update(['status' => 'approved']);
    }

    public function down(): void
    {
        Schema::table('businesses', function (Blueprint $table) {
            $table->dropColumn(['status', 'category', 'phone', 'rejection_reason', 'processed_at']);
        });
    }
};
