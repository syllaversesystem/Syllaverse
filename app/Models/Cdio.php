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
    // Simplified: code & sort_order removed
    protected $fillable = ['title', 'description'];
    protected $casts = [];

    // Order by id (stable)
    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('id');
    }
}
