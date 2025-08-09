<?php

// -------------------------------------------------------------------------------
// * File: app/Models/ChairRequest.php
// * Description: Admin-submitted request to hold a chair role (Dept/Program) pending Superadmin decision – Syllaverse
// -------------------------------------------------------------------------------
// 📜 Log:
// [2025-08-08] Initial creation – relationships, status scopes, and helpers for approval workflow.
// [2025-08-08] Allow null decider in markApproved/markRejected to support custom superadmin auth.
// -------------------------------------------------------------------------------

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChairRequest extends Model
{
    use HasFactory;

    public const ROLE_DEPT = 'DEPT_CHAIR';
    public const ROLE_PROG = 'PROG_CHAIR';

    public const STATUS_PENDING  = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';

    protected $fillable = [
        'user_id',
        'requested_role',
        'department_id',
        'program_id',
        'status',
        'decided_by',
        'decided_at',
        'notes',
    ];

    protected $casts = [
        'decided_at' => 'datetime',
    ];

    // Relationships
    public function user()      { return $this->belongsTo(User::class); }
    public function decidedBy() { return $this->belongsTo(User::class, 'decided_by'); }
    public function department(){ return $this->belongsTo(Department::class); }
    public function program()   { return $this->belongsTo(Program::class); }

    // Scopes
    public function scopePending($q){ return $q->where('status', self::STATUS_PENDING); }
    public function scopeApproved($q){ return $q->where('status', self::STATUS_APPROVED); }
    public function scopeRejected($q){ return $q->where('status', self::STATUS_REJECTED); }
    public function scopeForRole($q, string $role){ return $q->where('requested_role', $role); }
    public function scopeForDepartment($q, int $id){ return $q->where('department_id', $id); }
    public function scopeForProgram($q, int $id){ return $q->where('program_id', $id); }

    // Helpers
    public function isDeptRequest(): bool { return $this->requested_role === self::ROLE_DEPT; }
    public function isProgRequest(): bool { return $this->requested_role === self::ROLE_PROG; }

    public function scopeConsistentWithDepartment(): bool
    {
        if ($this->isProgRequest() && $this->program) {
            return (int) $this->program->department_id === (int) $this->department_id;
        }
        return true;
    }

    /** Approve with optional decider; columns are nullable. */
    public function markApproved(?int $deciderUserId, ?string $notes = null): void
    {
        $this->status     = self::STATUS_APPROVED;
        $this->decided_by = $deciderUserId; // may be null
        $this->decided_at = now();
        $this->notes      = $notes;
        $this->save();
    }

    /** Reject with optional decider; columns are nullable. */
    public function markRejected(?int $deciderUserId, ?string $notes = null): void
    {
        $this->status     = self::STATUS_REJECTED;
        $this->decided_by = $deciderUserId; // may be null
        $this->decided_at = now();
        $this->notes      = $notes;
        $this->save();
    }

    public function toAppointmentPayload(int $assignedByUserId, ?string $startAt = null): array
    {
        return [
            'user_id'     => $this->user_id,
            'role'        => $this->isDeptRequest() ? Appointment::ROLE_DEPT : Appointment::ROLE_PROG,
            'scope_type'  => $this->isDeptRequest() ? Appointment::SCOPE_DEPT : Appointment::SCOPE_PROG,
            'scope_id'    => $this->isDeptRequest() ? $this->department_id : $this->program_id,
            'status'      => 'active',
            'start_at'    => $startAt ? \Carbon\Carbon::parse($startAt) : now(),
            'end_at'      => null,
            'assigned_by' => $assignedByUserId,
        ];
    }
}
