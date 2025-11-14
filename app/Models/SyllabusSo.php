<?php

// -----------------------------------------------------------------------------
// File: app/Models/SyllabusSo.php
// Description: Represents a syllabus-specific Student Outcome (SO) – Syllaverse
// -----------------------------------------------------------------------------
// 📜 Log:
// [2025-07-29] Regenerated with fillable for sortable SOs.
// [2025-07-29] Added TLA ↔ SO many-to-many mapping relationship.
// -----------------------------------------------------------------------------

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SyllabusSo extends Model
{
    use HasFactory;

    protected $fillable = [
        'syllabus_id',
        'code',
        'title',
        'description',
        'position',
    ];

    public function syllabus()
    {
        return $this->belongsTo(Syllabus::class);
    }

    // 🔁 Many-to-many: SO ↔ TLA
    public function tlas()
    {
        return $this->belongsToMany(TLA::class, 'tla_so', 'syllabus_so_id', 'tla_id')->withTimestamps();
    }
}
