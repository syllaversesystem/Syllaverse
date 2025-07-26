<?php

// File: app/Models/SyllabusTextbook.php
// Description: Model for uploaded textbook files related to a syllabus – Syllaverse

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SyllabusTextbook extends Model
{
    use HasFactory;

    protected $fillable = [
        'syllabus_id',
        'file_path',
        'original_name',
        'type', // ➕ Added type to allow mass assignment of main/other classification
    ];

    // 🔁 Each textbook belongs to one syllabus
    public function syllabus()
    {
        return $this->belongsTo(Syllabus::class);
    }
}

