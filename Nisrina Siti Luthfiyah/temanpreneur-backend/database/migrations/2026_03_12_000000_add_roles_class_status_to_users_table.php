<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->json('roles')->nullable()->after('role');
            $table->string('class')->nullable()->after('roles');
            $table->string('status')->default('active')->after('class'); // active, banned
        });

        // Migrate existing role ke roles
        DB::table('users')->get()->each(function ($user) {
            DB::table('users')->where('id', $user->id)->update([
                'roles' => json_encode([$user->role]),
            ]);
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['roles', 'class', 'status']);
        });
    }
};
