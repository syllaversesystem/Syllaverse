<?php
// -------------------------------------------------------------------------------
// * File: app/Models/Department.php
// * Description: Department Model
// -------------------------------------------------------------------------------
// 📜 Log:
// [2025-08-09] Replaced legacy user FK approach with appointment-based helpers.
// [2025-08-09] Added backward-compatible admin() relation via hasOneThrough to current Dept Chair (active).
// -------------------------------------------------------------------------------

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Appointment;
use App\Models\User;
use App\Models\Program;
use App\Models\Course;

class Department extends Model
{
    use HasFactory;

    protected $fillable = ['name','code'];

    // ░░░ START: Back-compat – current Dept Chair user via appointments ░░░
    /** Plain-English: Returns the current (active) Department Chair USER for this department, or null. */
    public function admin()
    {
        return $this->hasOneThrough(
                User::class,        // Final model we want
                Appointment::class, // Through model
                'scope_id',         // FK on appointments that points to departments.id
                'id',               // FK on users that appointments.user_id references
                'id',               // Local key on departments
                'user_id'           // Local key on appointments to join users
            )
            ->where('appointments.scope_type', Appointment::SCOPE_DEPT)
            ->where('appointments.role', Appointment::ROLE_DEPT)
            ->where('appointments.status', 'active');
    }
    // ░░░ END: Back-compat – current Dept Chair user via appointments ░░░

    // ░░░ START: Helpful collections (optional) ░░░
    /** All active Dept Chair appointments (usually 0 or 1). */
    public function activeDeptChairAppointments()
    {
        return $this->hasMany(Appointment::class, 'scope_id')
            ->where('scope_type', Appointment::SCOPE_DEPT)
            ->where('role', Appointment::ROLE_DEPT)
            ->where('status', 'active');
    }

    /** Programs under this department. */
    public function programs()
    {
        return $this->hasMany(Program::class);
    }

    /** Courses under this department. */
    public function courses()
    {
        return $this->hasMany(Course::class);
    }
    // ░░░ END: Helpful collections ░░░
}
