<?php

// -----------------------------------------------------------------------------
// * File: app/Models/AssessmentTaskGroup.php
// * Description: Eloquent model for Assessment Task Groups (LEC/LAB subtabs).
// -----------------------------------------------------------------------------
// 📜 Log:
// [2025-08-17] Initial creation – fillable fields, casts, relationships, scopes,
//              and helper to find by code.
// -----------------------------------------------------------------------------

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssessmentTaskGroup extends Model
{
    use HasFactory;

    /**
     * This model represents a tab grouping like "Lecture" (LEC) or "Laboratory" (LAB)
     * which organizes Assessment Tasks and controls their order/visibility.
     */

    // Allow mass-assignment for these attributes
    protected $fillable = [
        'code',
        'title',
        'slug',
        'sort_order',
        'is_active',
    ];

    // Type casting for attributes
    protected $casts = [
        'is_active' => 'boolean',
    ];

    // START: Relationships

    /**
     * This returns all tasks under this group (e.g., all LEC tasks) ordered for display.
     */
    public function tasks()
    {
        return $this->hasMany(AssessmentTask::class, 'group_id')->orderBy('sort_order')->orderBy('id');
    }

    // END: Relationships

    // START: Query Scopes

    /**
     * This limits results to only active groups (used by UI to hide inactive tabs).
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * This orders groups by the drag handle order, so tabs appear in the right order.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }

    // END: Query Scopes

    // START: Helpers

    /**
     * This finds a group by its short code like "LEC" or "LAB".
     */
    public static function findByCode(string $code): ?self
    {
        return static::where('code', $code)->first();
    }

    // END: Helpers
}
