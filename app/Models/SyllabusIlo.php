<?php

// File: app/Models/SyllabusIlo.php
// Description: Model for syllabus-specific Intended Learning Outcomes – Syllaverse
// -----------------------------------------------------------------------------
// 📜 Log:
// [2025-07-29] Regenerated with fillable support for code, description, position.
// -----------------------------------------------------------------------------

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SyllabusIlo extends Model
{
    use HasFactory;

    // ✅ Fillable for all editable/sortable fields
    protected $fillable = [
        'syllabus_id',
        'code',
        'description',
        'position',
    ];

    // 🔁 Relationship: Belongs to a specific syllabus
    public function syllabus()
    {
        return $this->belongsTo(Syllabus::class);
    }
}
