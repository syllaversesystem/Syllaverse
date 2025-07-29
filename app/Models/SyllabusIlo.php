<?php

// -----------------------------------------------------------------------------
// File: app/Models/SyllabusIlo.php
// Description: Model for syllabus-specific Intended Learning Outcomes – Syllaverse
// -----------------------------------------------------------------------------
// 📜 Log:
// [2025-07-29] Regenerated with fillable support for code, description, position.
// [2025-07-29] Added TLA ↔ ILO many-to-many mapping relationship.
// -----------------------------------------------------------------------------

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SyllabusIlo extends Model
{
    use HasFactory;

    protected $fillable = [
        'syllabus_id',
        'code',
        'description',
        'position',
    ];

    public function syllabus()
    {
        return $this->belongsTo(Syllabus::class);
    }

    // 🔁 Many-to-many: ILO ↔ TLA
    public function tlas()
    {
        return $this->belongsToMany(TLA::class, 'tla_ilo', 'syllabus_ilo_id', 'tla_id')->withTimestamps();
    }
}
