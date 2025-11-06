<?php

// -------------------------------------------------------------------------------
// * File: app/Models/Sdg.php
// * Description: Sustainable Development Goal (SDG) master data with ordered codes (SDG1, SDG2, …) – Syllaverse
// -------------------------------------------------------------------------------
// 📜 Log:
// [2025-08-12] Add `code` + `sort_order`, ordered scope, and auto-sync of code from sort_order.
// -------------------------------------------------------------------------------

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Sdg extends Model
{
    // START: Fillable & Casts
    protected $fillable = ['title', 'description'];
    protected $casts = [];
    // END: Fillable & Casts

    // START: Relationships
    /** An SDG can be mapped to many syllabi (with editable pivot values). */
    public function syllabi()
    {
        // Use the normalized per-syllabus SDG table name. Historically the
        // project used a legacy pivot table named `syllabus_sdg` (singular).
        // The current normalized table is `syllabus_sdgs` and contains the
        // editable pivot fields (id, code, sort_order, title, description, position).
        return $this->belongsToMany(Syllabus::class, 'syllabus_sdgs')
            ->withPivot('id', 'code', 'sort_order', 'position', 'title', 'description')
            ->withTimestamps();
    }
    // END: Relationships

    // START: Scopes
    /** Order by id ascending (sort_order removed). */
    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('id');
    }

    // (Removed code-based scope; SDG no longer has a `code` column)
    // END: Scopes

    // START: Helpers (removed code helpers)
    // END: Helpers

    // START: Model Events
    protected static function booted(): void
    {
        // No-op (sort_order removed)
    }
    // END: Model Events
}
