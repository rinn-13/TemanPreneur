<?php
// database/migrations/2025_01_01_000002_create_team_members_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('team_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('role')->nullable(); // misal: 'staff', 'manager'
            $table->timestamps();

            // Mencegah duplikasi anggota di bisnis yang sama
            $table->unique(['business_id', 'user_id']);

            // Index untuk kolom yang sering di-query
            $table->index('business_id');
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('team_members');
    }
};