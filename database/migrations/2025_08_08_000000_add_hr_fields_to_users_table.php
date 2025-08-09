<?php

// -------------------------------------------------------------------------------
// * File: database/migrations/2025_08_08_000000_add_hr_fields_to_users_table.php
// * Description: Add HR fields (designation, employee_code) to users table – Syllaverse
// -------------------------------------------------------------------------------
// 📜 Log:
// [2025-08-08] Initial creation – adds nullable designation and employee_code to users.
// [2025-08-08] Made idempotent: guard with Schema::hasColumn to avoid duplicate column errors.
// -------------------------------------------------------------------------------

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // This adds two optional HR fields to the existing users table so admins can enter them on profile completion.
    public function up(): void
    {
        // ░░░ START: Up Migration ░░░
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'designation')) {
                $table->string('designation')->nullable()->after('status');
            }
            if (!Schema::hasColumn('users', 'employee_code')) {
                // If designation already exists, this will still work; 'after' is just cosmetic.
                $table->string('employee_code')->nullable()->after('designation');
            }
        });
        // ░░░ END: Up Migration ░░░
    }

    // This cleanly removes the HR fields if we ever roll back.
    public function down(): void
    {
        // ░░░ START: Down Migration ░░░
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'employee_code')) {
                $table->dropColumn('employee_code');
            }
            if (Schema::hasColumn('users', 'designation')) {
                $table->dropColumn('designation');
            }
        });
        // ░░░ END: Down Migration ░░░
    }
};
