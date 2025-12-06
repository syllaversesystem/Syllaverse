<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('super_admins', function (Blueprint $table) {
            if (!Schema::hasColumn('super_admins', 'email')) {
                $table->string('email')->nullable()->after('username');
            }
            if (!Schema::hasColumn('super_admins', 'email_verified_at')) {
                $table->timestamp('email_verified_at')->nullable()->after('email');
            }
        });
    }

    public function down(): void
    {
        Schema::table('super_admins', function (Blueprint $table) {
            if (Schema::hasColumn('super_admins', 'email_verified_at')) {
                $table->dropColumn('email_verified_at');
            }
            if (Schema::hasColumn('super_admins', 'email')) {
                $table->dropColumn('email');
            }
        });
    }
};