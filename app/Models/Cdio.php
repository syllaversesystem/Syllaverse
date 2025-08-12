<?php

// -------------------------------------------------------------------------------
// * File: app/Models/Cdio.php
// * Description: Conceive-Design-Implement-Operate (CDIO) with ordered codes (CDIO1, CDIO2, …) – Syllaverse
// -------------------------------------------------------------------------------
// 📜 Log:
// [2025-08-12] Add `code` + `sort_order`, ordered scope, and auto-sync of code from sort_order.
// -------------------------------------------------------------------------------

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Cdio extends Model
{
    // START: Fillable & Casts
    protected $fillable = ['title', 'description', 'code', 'sort_order'];
    protected $casts = [
        'sort_order' => 'integer',
    ];
    // END: Fillable & Casts

    // START: Constants
    public const CODE_PREFIX = 'CDIO';
    // END: Constants

    // START: Scopes
    /** Order by sort_order ascending (then id for stability). */
    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }

    /** Quick code filter (e.g., CDIO7). */
    public function scopeByCode(Builder $query, string $code): Builder
    {
        return $query->where('code', $code);
    }
    // END: Scopes

    // START: Helpers
    /** Returns the code prefix for this model (CDIO). */
    public static function codePrefix(): string
    {
        return static::CODE_PREFIX;
    }

    /** Compose code from a 1-based position (e.g., CDIO + 5 => CDIO5). */
    public static function makeCodeFromPosition(int $position): string
    {
        return static::CODE_PREFIX . max(1, $position);
    }
    // END: Helpers

    // START: Model Events (keep code ↔ sort_order in sync)
    protected static function booted(): void
    {
        static::creating(function (self $model): void {
            if (is_null($model->sort_order)) {
                $model->sort_order = (int) static::max('sort_order') + 1;
            }
            if (empty($model->code)) {
                $model->code = static::makeCodeFromPosition((int) $model->sort_order);
            }
        });

        static::saving(function (self $model): void {
            if ($model->isDirty('sort_order')) {
                $model->code = static::makeCodeFromPosition((int) $model->sort_order);
            }
        });
    }
    // END: Model Events
}
