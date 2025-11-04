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
    protected $fillable = ['title', 'description', 'code', 'sort_order', 'department_id'];
    protected $casts = [
        'sort_order' => 'integer',
    ];
    // END: Fillable & Casts

    // START: Constants
    public const CODE_PREFIX = 'SDG';
    // END: Constants

    // START: Relationships
    public function department()
    {
        return $this->belongsTo(Department::class);
    }
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
    /** Order by sort_order ascending (then id for stability). */
    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }

    /** Quick code filter (e.g., SDG3). */
    public function scopeByCode(Builder $query, string $code): Builder
    {
        return $query->where('code', $code);
    }
    // END: Scopes

    // START: Helpers
    /** Returns the code prefix for this model (SDG). */
    public static function codePrefix(): string
    {
        return static::CODE_PREFIX;
    }

    /** Compose code from a 1-based position (e.g., SDG + 3 => SDG3). */
    public static function makeCodeFromPosition(int $position): string
    {
        return static::CODE_PREFIX . max(1, $position);
    }
    // END: Helpers

    // START: Model Events (keep code ↔ sort_order in sync)
    protected static function booted(): void
    {
        static::creating(function (self $model): void {
            // Default sort_order to (max + 1) if not provided
            if (is_null($model->sort_order)) {
                $model->sort_order = (int) static::max('sort_order') + 1;
            }
            // Default code from sort_order if empty
            if (empty($model->code)) {
                $model->code = static::makeCodeFromPosition((int) $model->sort_order);
            }
        });

        static::saving(function (self $model): void {
            // If sort_order changed, regenerate code to match
            if ($model->isDirty('sort_order')) {
                $model->code = static::makeCodeFromPosition((int) $model->sort_order);
            }
        });
    }
    // END: Model Events
}
