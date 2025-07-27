<?php

// File: app/Models/SyllabusIlo.php
// Description: Model representing a syllabus-specific ILO record

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SyllabusIlo extends Model
{
    protected $fillable = ['syllabus_id', 'description'];

    public function syllabus()
    {
        return $this->belongsTo(Syllabus::class);
    }
}
