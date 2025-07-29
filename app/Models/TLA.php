<?php

// -----------------------------------------------------------------------------
// File: app/Models/TLA.php
// Description: TLA model – manually define 'tla' as table name to avoid Laravel pluralization errors
// -----------------------------------------------------------------------------

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TLA extends Model
{
    protected $table = 'tla'; // ✅ your actual table name

    protected $fillable = [
        'syllabus_id', 'ch', 'topic', 'wks', 'outcomes', 'ilo', 'so', 'delivery'
    ];

    // ✅ Explicit pivot keys to avoid "t_l_a_id" errors
    public function ilos()
    {
        return $this->belongsToMany(
            SyllabusIlo::class,
            'tla_ilo',         // Pivot table
            'tla_id',          // Foreign key on pivot pointing to this model
            'syllabus_ilo_id'  // Foreign key on pivot pointing to related model
        );
    }

    public function sos()
    {
        return $this->belongsToMany(
            SyllabusSo::class,
            'tla_so',          // Pivot table
            'tla_id',          // FK on pivot pointing to TLA
            'syllabus_so_id'   // FK on pivot pointing to SO
        );
    }

    public function syllabus()
    {
        return $this->belongsTo(Syllabus::class);
    }
}
