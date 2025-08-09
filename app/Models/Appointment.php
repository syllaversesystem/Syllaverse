<?php

// -------------------------------------------------------------------------------
// * File: app/Models/Appointment.php
// * Description: Grants scoped chair authority (Dept/Program) with time-bounds and helpers – Syllaverse
// -------------------------------------------------------------------------------
// 📜 Log:
// [2025-08-08] Initial creation – relationships, active scopes, and utility helpers.
// -------------------------------------------------------------------------------

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    use HasFactory;

    // This lets us mass-assign fields when approving requests.
    protected $fillable = [
        'user_id',
        'role',         // 'DEPT_CHAIR' | 'PROG_CHAIR'
        'scope_type',   // 'Department' | 'Program'
        'scope_id',
        'status',       // 'active' | 'ended'
        'start_at',
        'end_at',
        'assigned_by',
    ];

    // Cast timestamps to Carbon for easy date math.
    protected $casts = [
        'start_at' => 'datetime',
        'end_at'   => 'datetime',
    ];

    // Simple constants to avoid string typos around the codebase.
    public const ROLE_DEPT = 'DEPT_CHAIR';
    public const ROLE_PROG = 'PROG_CHAIR';
    public const SCOPE_DEPT = 'Department';
    public const SCOPE_PROG = 'Program';

    // ░░░ START: Relationships ░░░

    /** The user who holds this appointment. */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /** The superadmin (or actor) who assigned/approved this appointment. */
    public function assignedBy()
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    /** If the scope is a Department, this resolves to that Department. */
    public function department()
    {
        return $this->belongsTo(Department::class, 'scope_id')
            ->where('scope_type', self::SCOPE_DEPT);
    }

    /** If the scope is a Program, this resolves to that Program. */
    public function program()
    {
        return $this->belongsTo(Program::class, 'scope_id')
            ->where('scope_type', self::SCOPE_PROG);
    }

    // ░░░ END: Relationships ░░░


    // ░░░ START: Query Scopes ░░░

    /**
     * Only currently-active appointments (status=active AND no past end_at).
     * This keeps "future-proof" if someone sets end_at in the past.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active')
            ->where(function ($q) {
                $q->whereNull('end_at')
                  ->orWhere('end_at', '>', Carbon::now());
            });
    }

    /** Filter to department-chair appointments for a given department. */
    public function scopeForDepartment($query, int $departmentId)
    {
        return $query->where('role', self::ROLE_DEPT)
            ->where('scope_type', self::SCOPE_DEPT)
            ->where('scope_id', $departmentId);
    }

    /** Filter to program-chair appointments for a given program. */
    public function scopeForProgram($query, int $programId)
    {
        return $query->where('role', self::ROLE_PROG)
            ->where('scope_type', self::SCOPE_PROG)
            ->where('scope_id', $programId);
    }

    // ░░░ END: Query Scopes ░░░


    // ░░░ START: Helpers ░░░

    /** True if this appointment is currently active (based on status/end_at). */
    public function isActive(): bool
    {
        if ($this->status !== 'active') {
            return false;
        }
        return is_null($this->end_at) || $this->end_at->isFuture();
    }

    /** Easy role checks used in gates/policies/UI. */
    public function isDeptChair(): bool
    {
        return $this->role === self::ROLE_DEPT && $this->scope_type === self::SCOPE_DEPT;
    }

    public function isProgChair(): bool
    {
        return $this->role === self::ROLE_PROG && $this->scope_type === self::SCOPE_PROG;
    }

    /**
     * End this appointment "now" (for turnover). Keeps history via end_at and status.
     * This takes the appointment out of play without deleting it.
     */
    public function endNow(): void
    {
        $this->end_at = Carbon::now();
        $this->status = 'ended';
        $this->save();
    }

    /**
     * Return a compact array you can drop into session for the “Acting as …” context switcher.
     * Example use: session(['acting' => $appointment->toActingContext()]);
     */
    public function toActingContext(): array
    {
        return [
            'role'       => $this->role,
            'scope_type' => $this->scope_type,
            'scope_id'   => $this->scope_id,
            'label'      => $this->getScopeLabel(),
        ];
    }

    /**
     * Human label for the current scope (e.g., "Dept of ITE" or "BSIT").
     * Falls back to generic text if the related model isn't eager-loaded.
     */
    public function getScopeLabel(): string
    {
        if ($this->isDeptChair() && $this->relationLoaded('department') && $this->department) {
            return $this->department->name;
        }
        if ($this->isProgChair() && $this->relationLoaded('program') && $this->program) {
            return $this->program->name;
        }
        return "{$this->scope_type} #{$this->scope_id}";
    }

    /**
     * Quick coverage check: does this appointment cover the given program?
     * - Program Chair of that program → true
     * - Dept Chair of that program’s department → true
     */
    public function coversProgram(Program $program): bool
    {
        if ($this->isProgChair() && $this->scope_id === $program->id) {
            return true;
        }
        if ($this->isDeptChair() && $program->department_id === $this->scope_id) {
            return true;
        }
        return false;
    }

    // ░░░ END: Helpers ░░░
}
