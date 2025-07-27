<?php

// File: app/Models/Syllabus.php
// Description: Represents a syllabus created by a faculty; includes textbook and TLA relations – Syllaverse

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
        'academic_year',
        'semester',
        'year_level',
        'mission',
        'vision',
    ];

    // 🔁 Each syllabus belongs to one faculty
    public function faculty()
    {
        return $this->belongsTo(User::class, 'faculty_id');
    }

    // 🔁 Each syllabus belongs to one program
    public function program()
    {
        return $this->belongsTo(Program::class);
    }

    // 🔁 Each syllabus belongs to one course
    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    // 🔁 A syllabus has many topic-learning activities (TLA)
    public function tla()
    {
        return $this->hasMany(TLA::class);
    }

    // ✅ A syllabus has many uploaded textbook files
    public function textbooks()
    {
        return $this->hasMany(SyllabusTextbook::class);
    }

    // File: app/Models/Syllabus.php
// Description: Represents a created syllabus (Faculty version)

public function ilos()
{
    return $this->hasMany(SyllabusIlo::class);
}

public function sos()
{
    return $this->hasMany(SyllabusSo::class);
}

}


