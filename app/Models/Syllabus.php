<?php

// File: app/Models/Syllabus.php
// Description: Eloquent model for the syllabi table, representing faculty-created syllabus records (Syllaverse)

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Syllabus extends Model
{
    use HasFactory;

    protected $fillable = [
        'faculty_id',
        'program_id',
        'course_id',
        'title',
        'mission',
        'vision',
        'academic_year',
        'semester',
        'year_level',
        'textbook_file_path', // ✅ recently added
    ];

    // 🔁 Relationships
    public function faculty()
    {
        return $this->belongsTo(User::class, 'faculty_id');
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function program()
    {
        return $this->belongsTo(Program::class);
    }

    public function tla()
    {
        return $this->hasMany(TLA::class);
    }
}
