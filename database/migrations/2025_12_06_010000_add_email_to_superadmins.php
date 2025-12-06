<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('superadmins', function (Blueprint $table) {
            if (!Schema::hasColumn('superadmins', 'email')) {
                $table->string('email')->nullable()->after('username');
            }
            if (!Schema::hasColumn('superadmins', 'email_verified_at')) {
                $table->timestamp('email_verified_at')->nullable()->after('email');
            }
        });
    }

    public function down(): void
    {
        Schema::table('superadmins', function (Blueprint $table) {
            if (Schema::hasColumn('superadmins', 'email_verified_at')) {
                $table->dropColumn('email_verified_at');
            }
            if (Schema::hasColumn('superadmins', 'email')) {
                $table->dropColumn('email');
            }
        });
    }
};