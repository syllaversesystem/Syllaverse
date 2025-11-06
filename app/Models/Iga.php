<?php

// -------------------------------------------------------------------------------
// * File: app/Models/Iga.php
// * Description: Institutional Graduate Attribute (IGA) with ordered codes (IGA1, IGA2, …) – Syllaverse
// -------------------------------------------------------------------------------
// 📜 Log:
// [2025-08-12] Add `code` + `sort_order`, ordered scope, and auto-sync of code from sort_order.
// -------------------------------------------------------------------------------

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Iga extends Model
{
    // START: Fillable & Casts
    protected $fillable = ['title', 'description', 'department_id'];
    protected $casts = [];
    // END: Fillable & Casts

    // START: Relationships
    public function department()
    {
        return $this->belongsTo(Department::class);
    }
    // END: Relationships

    // START: Scopes
    /** Order by id ascending (sort_order removed). */
    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('id');
    }
    // END: Scopes

    // START: Helpers (removed code helpers)
    // END: Helpers

    // START: Model Events (removed, no sort_order/code)
    protected static function booted(): void
    {
        // no-op
    }
    // END: Model Events
}
