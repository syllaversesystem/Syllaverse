<?php

// File: app/Models/TLA.php
// Description: Eloquent model for TLA entries (Teaching, Learning, and Assessment rows) linked to a syllabus – Syllaverse

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TLA extends Model
{
    use HasFactory;

    protected $table = 'tla';

    protected $fillable = [
        'syllabus_id',
        'ch',
        'topic',
        'wks',
        'outcomes',
        'ilo',
        'so',
        'delivery',
    ];

    public function syllabus()
    {
        return $this->belongsTo(Syllabus::class);
    }
}
