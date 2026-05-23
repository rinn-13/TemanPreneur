<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('businesses', function (Blueprint $table) {
            $table->text('address')->nullable()->after('theme_color');
            $table->string('logo')->nullable()->after('address');
            $table->string('banner')->nullable()->after('logo');
        });
    }

    public function down(): void
    {
        Schema::table('businesses', function (Blueprint $table) {
            $table->dropColumn(['address', 'logo', 'banner']);
        });
    }
};

