<?php

// -----------------------------------------------------------------------------
// * File: database/seeders/DatabaseSeeder.php
// * Description: Master seeder – wires all seeders and ensures correct order.
// -----------------------------------------------------------------------------
// 📜 Log:
// [2025-08-17] Added AssessmentTaskGroupSeeder then AssessmentTaskSeeder (order matters).
// -----------------------------------------------------------------------------

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * This runs all database seeders. Keep your existing seeders in the list,
     * and make sure AssessmentTaskGroupSeeder runs before AssessmentTaskSeeder.
     */
    // ░░░ START: run() – Call seeders ░░░
    public function run(): void
    {
        $this->call([
            // --- Existing seeders (keep yours here) ---
            // RoleSeeder::class,
            // DepartmentSeeder::class,
            // UserSeeder::class,
            // ...

            // --- New: Assessment Task seeders (order matters) ---
            AssessmentTaskGroupSeeder::class,
            AssessmentTaskSeeder::class,
        ]);
    }
    // ░░░ END: run() – Call seeders ░░░
}
