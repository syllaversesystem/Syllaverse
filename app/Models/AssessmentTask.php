<?php

// -----------------------------------------------------------------------------
// * File: app/Models/AssessmentTask.php
// * Description: Eloquent model for Assessment Tasks (e.g., ME, FE, QCT, ARR, PR, LE, LEX).
// -----------------------------------------------------------------------------
// 📜 Log:
// [2025-08-17] Initial creation – fillable, casts, relationship to group, scopes
//              (active/ordered/inGroupCode), and code label accessor.
// [2025-08-17] Update – Removed 'description' field from model (no longer used
//              in UI/validation); adjusted comments accordingly.
// -----------------------------------------------------------------------------

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssessmentTask extends Model
{
    use HasFactory;

    /**
     * This model represents a single assessment task (like Midterm Exam) that
     * belongs to a group such as LEC (Lecture) or LAB (Laboratory).
     */

    // Allow mass-assignment for these attributes (description removed)
    protected $fillable = [
        'group_id',
        'code',
        'title',
        'sort_order',
        'is_active',
    ];

    // Type casting for attributes
    protected $casts = [
        'is_active' => 'boolean',
    ];

    // Optionally append a handy computed label in JSON/API responses, e.g., "LEC-ME"
    protected $appends = ['code_label'];

    // ░░░ START: Relationships ░░░

    /**
     * This links the task back to its parent group (LEC or LAB).
     */
    public function group()
    {
        return $this->belongsTo(AssessmentTaskGroup::class, 'group_id');
    }

    // ░░░ END: Relationships ░░░


    // ░░░ START: Query Scopes ░░░

    /**
     * This limits results to only active tasks (used to hide disabled rows in UI).
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * This orders tasks by their drag handle order, then by id (stable ordering).
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }

    /**
     * This filters tasks by a parent group code like 'LEC' or 'LAB'.
     */
    public function scopeInGroupCode($query, string $groupCode)
    {
        return $query->whereHas('group', function ($q) use ($groupCode) {
            $q->where('code', $groupCode);
        });
    }

    // ░░░ END: Query Scopes ░░░


    // ░░░ START: Accessors & Helpers ░░░

    /**
     * This returns a friendly label combining the group's code with this task's code, e.g., "LEC-ME".
     */
    public function getCodeLabelAttribute(): string
    {
        $prefix = $this->group ? ($this->group->code . '-') : '';
        return $prefix . $this->code;
    }

    /**
     * This returns the same "LEC-ME" label via a method for convenient use in PHP-only contexts.
     */
    public function codeLabel(): string
    {
        return $this->code_label;
    }

    // ░░░ END: Accessors & Helpers ░░░
}
