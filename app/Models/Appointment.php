<?php

// -------------------------------------------------------------------------------
// * File: app/Models/Appointment.php
// * Description: Grants scoped chair/faculty authority (Dept/Program/Faculty) with time-bounds and helpers – Syllaverse
// -------------------------------------------------------------------------------
// 📜 Log:
// [2025-08-08] Initial creation – relationships, active scopes, and utility helpers.
// [2025-08-11] Defaults & safety – auto-set start_at=now() and status=active on create;
//              infer scope_type from role on create/save; added role mutator and
//              a convenience setter for role+scope to keep data consistent.
// [2025-08-16] Added FACULTY role/scope support – updated constants, inference logic,
//              and helper methods.
// -------------------------------------------------------------------------------

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    use HasFactory;

    // ░░░ START: Constants & Attributes ░░░

    /** Role constants to avoid string typos. */
    // Legacy department chair constant; kept for backward compatibility
    public const ROLE_DEPT    = 'DEPT_CHAIR';
    // New naming: Department Head (preferred)
    public const ROLE_DEPT_HEAD = 'DEPT_HEAD';
    public const ROLE_FACULTY = 'FACULTY';
    // Program-chair compatibility (shims) — program-scoped role was removed but
    // some legacy code still references these constants. Keep as shims to avoid
    // undefined constant errors; logic elsewhere treats program chair as removed.
    public const ROLE_PROG    = 'PROG_CHAIR';
    // Institution-level roles
    public const ROLE_VCAA       = 'VCAA';
    public const ROLE_ASSOC_VCAA = 'ASSOC_VCAA';
    public const ROLE_DEAN       = 'DEAN';
    public const ROLE_ASSOC_DEAN = 'ASSOC_DEAN';

    /** Scope type constants (polymorphic-ish discriminator). */
    public const SCOPE_DEPT    = 'Department';
    // Program scope shim
    public const SCOPE_PROG    = 'Program';
    public const SCOPE_FACULTY = 'Faculty';
    public const SCOPE_INSTITUTION = 'Institution';

    protected $fillable = [
        'user_id',
        'role',         // 'DEPT_CHAIR' | 'PROG_CHAIR' | 'FACULTY'
        'scope_type',   // 'Department' | 'Program' | 'Faculty'
        'scope_id',
        'status',       // 'active' | 'ended'
        'start_at',
        'end_at',
        'assigned_by',
    ];

    protected $casts = [
        'start_at' => 'datetime',
        'end_at'   => 'datetime',
    ];

    // ░░░ END: Constants & Attributes ░░░


    // ░░░ START: Relationships ░░░

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function assignedBy()
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    public function department()
    {
        return $this->belongsTo(Department::class, 'scope_id')
            ->where('scope_type', self::SCOPE_DEPT);
    }

    // Program relation removed (Program Chair no longer exists)

    // ░░░ END: Relationships ░░░


    // ░░░ START: Query Scopes ░░░

    public function scopeActive($query)
    {
        return $query->where('status', 'active')
            ->where(function ($q) {
                $q->whereNull('end_at')
                  ->orWhere('end_at', '>', Carbon::now());
            });
    }

    public function scopeForDepartment($query, int $departmentId)
    {
        return $query->where('role', self::ROLE_DEPT)
            ->where('scope_type', self::SCOPE_DEPT)
            ->where('scope_id', $departmentId);
    }

    // Program-scoped queries removed (Program Chair no longer exists)

    public function scopeForFaculty($query, int $departmentId)
    {
        return $query->where('role', self::ROLE_FACULTY)
            ->where('scope_type', self::SCOPE_FACULTY)
            ->where('scope_id', $departmentId);
    }

    // ░░░ END: Query Scopes ░░░


    // ░░░ START: Model Events (Defaults & Invariants) ░░░

    protected static function booted(): void
    {
        static::creating(function (Appointment $appt) {
            if (empty($appt->start_at)) {
                $appt->start_at = now();
            }
            if (empty($appt->status)) {
                $appt->status = 'active';
            }
            if (empty($appt->scope_type)) {
                $appt->scope_type = match ($appt->role) {
                    // Program role removed
                    self::ROLE_FACULTY      => self::SCOPE_FACULTY,
                    self::ROLE_VCAA,
                    self::ROLE_ASSOC_VCAA   => self::SCOPE_INSTITUTION,
                    // Dean and Associate Dean are department-scoped in Syllaverse
                    self::ROLE_DEAN,
                    self::ROLE_ASSOC_DEAN   => self::SCOPE_DEPT,
                    default                 => self::SCOPE_DEPT,
                };
                // For institution-level roles we allow scope_id to remain null.
            }
        });

        static::saving(function (Appointment $appt) {
            if (empty($appt->scope_type)) {
                $appt->scope_type = match ($appt->role) {
                    // Program role removed
                    self::ROLE_FACULTY      => self::SCOPE_FACULTY,
                    self::ROLE_VCAA,
                    self::ROLE_ASSOC_VCAA   => self::SCOPE_INSTITUTION,
                    // Dean and Associate Dean are department-scoped in Syllaverse
                    self::ROLE_DEAN,
                    self::ROLE_ASSOC_DEAN   => self::SCOPE_DEPT,
                    default                 => self::SCOPE_DEPT,
                };
            }
        });
    }

    public function setRoleAttribute($value): void
    {
        $this->attributes['role'] = $value;
        if (!array_key_exists('scope_type', $this->attributes)) {
            $this->attributes['scope_type'] = match ($value) {
                self::ROLE_FACULTY => self::SCOPE_FACULTY,
                default            => self::SCOPE_DEPT,
            };
        }
    }

    // ░░░ END: Model Events (Defaults & Invariants) ░░░


    // ░░░ START: Helpers ░░░

    public function isActive(): bool
    {
        return $this->status === 'active' &&
            (is_null($this->end_at) || $this->end_at->isFuture());
    }

    public function isDeptChair(): bool
    {
        return $this->role === self::ROLE_DEPT && $this->scope_type === self::SCOPE_DEPT;
    }

    public function isProgChair(): bool
    {
        return false; // Program Chair removed
    }

    public function isFaculty(): bool
    {
        return $this->role === self::ROLE_FACULTY && $this->scope_type === self::SCOPE_FACULTY;
    }

    public function setRoleAndScope(string $role, ?int $departmentId, ?int $programId): self
    {
        $this->role = $role;

        if ($role === self::ROLE_PROG) {
            $this->scope_type = self::SCOPE_PROG;
            $this->scope_id   = (int) $programId;
        } elseif ($role === self::ROLE_FACULTY) {
            $this->scope_type = self::SCOPE_FACULTY;
            $this->scope_id   = (int) $departmentId;
        } else {
            $this->scope_type = self::SCOPE_DEPT;
            $this->scope_id   = (int) $departmentId;
        }

        return $this;
    }

    public function toActingContext(): array
    {
        return [
            'role'       => $this->role,
            'scope_type' => $this->scope_type,
            'scope_id'   => $this->scope_id,
            'label'      => $this->getScopeLabel(),
        ];
    }

    public function getScopeLabel(): string
    {
        if ($this->isDeptChair() && $this->relationLoaded('department') && $this->department) {
            return $this->department->name;
        }

        // Program Chair removed

        if ($this->isFaculty() && $this->relationLoaded('department') && $this->department) {
            return $this->department->name . ' (Faculty)';
        }

        return "{$this->scope_type} #{$this->scope_id}";
    }

    public function coversProgram(Program $program): bool
    {
        // Program Chair removed: coverage determined by department/faculty only

        if (($this->isDeptChair() || $this->isFaculty()) && $program->department_id === $this->scope_id) {
            return true;
        }

        return false;
    }

    public function endNow(): void
    {
        $this->end_at = Carbon::now();
        $this->status = 'ended';
        $this->save();
    }

    // ░░░ END: Helpers ░░░
}
