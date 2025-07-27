<?php

// File: app/Models/SyllabusSo.php
// Description: Represents a syllabus-specific Student Outcome (SO) – Syllaverse

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SyllabusSo extends Model
{
    protected $fillable = ['syllabus_id', 'description'];

    public function syllabus()
    {
        return $this->belongsTo(Syllabus::class);
    }
}
