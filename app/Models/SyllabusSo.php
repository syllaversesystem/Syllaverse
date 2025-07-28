<?php

// -----------------------------------------------------------------------------
// File: app/Models/SyllabusSo.php
// Description: Represents a syllabus-specific Student Outcome (SO) – Syllaverse
// -----------------------------------------------------------------------------
// 📜 Log:
// [2025-07-29] Added `code` and `position` to fillable attributes for SO cloning from master list.
// -----------------------------------------------------------------------------

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SyllabusSo extends Model
{
    // 🔐 Mass assignable attributes
    protected $fillable = ['syllabus_id', 'code', 'description', 'position'];

    // 🔁 This links each SO to its parent syllabus
    public function syllabus()
    {
        return $this->belongsTo(Syllabus::class);
    }
}
