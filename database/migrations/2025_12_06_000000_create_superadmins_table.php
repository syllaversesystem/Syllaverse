<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('superadmins', function (Blueprint $table) {
            $table->id();
            $table->string('username')->unique();
            $table->string('password');
            $table->timestamps();
        });

        try {
            $username = env('SUPERADMIN_USERNAME');
            $password = env('SUPERADMIN_PASSWORD');
            if ($username && $password) {
                $exists = DB::table('superadmins')->count() > 0;
                if (!$exists) {
                    DB::table('superadmins')->insert([
                        'username' => $username,
                        'password' => Hash::make($password),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        } catch (\Throwable $e) {
            // no-op on seed failure
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('superadmins');
    }
};
