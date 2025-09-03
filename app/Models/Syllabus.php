<?php

// File: app/Models/Syllabus.php
// Description: Represents a syllabus created by a faculty; includes relationships for program, course, ILOs, SOs, TLAs, textbooks, and mapped SDGs – Syllaverse

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
    // Serialized assessment tasks JSON created by the AT module
    'assessment_tasks_data',
    ];

    // 🔁 Each syllabus belongs to one faculty
    public function faculty()
    {
        return $this->belongsTo(User::class, 'faculty_id');
    }

    // 🔁 Each syllabus belongs to one course
    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    // 🔁 Each syllabus belongs to one program
    public function program()
    {
        return $this->belongsTo(Program::class);
    }

    // 🔁 A syllabus has many criteria
    public function criteria()
    {
        return $this->hasMany(SyllabusCriteria::class)->orderBy('position');
    }

    // 🔁 A syllabus has many topic-learning activities (TLA)
    public function tla()
    {
        return $this->hasMany(TLA::class);
    }

    // 🔁 A syllabus has many uploaded textbook files
    public function textbooks()
    {
        return $this->hasMany(SyllabusTextbook::class);
    }

    // 🔁 A syllabus has many Intended Learning Outcomes (ILO)
    public function ilos()
    {
        return $this->hasMany(SyllabusIlo::class);
    }

    // 🔁 A syllabus has many Student Outcomes (SO)
    public function sos()
    {
        return $this->hasMany(SyllabusSo::class);
    }

    // 🔁 A syllabus has many Institutional Graduate Attributes (IGA)
    public function igas()
    {
        return $this->hasMany(SyllabusIga::class)->orderBy('position');
    }

    // Per-syllabus overrides for course information (so edits inside a syllabus don't change master course)
    public function courseInfo()
    {
        return $this->hasOne(SyllabusCourseInfo::class, 'syllabus_id');
    }

    // Per-syllabus stored assessment tasks table
    public function assessmentTasks()
    {
        return $this->hasMany(SyllabusAssessmentTask::class, 'syllabus_id')->orderBy('position');
    }

    // mission/vision moved to a separate table so they can be managed independently
    public function missionVision()
    {
        return $this->hasOne(SyllabusMissionVision::class, 'syllabus_id');
    }

    // 🔁 A syllabus maps many SDGs with editable pivot data
    public function sdgs()
    {
        return $this->belongsToMany(Sdg::class, 'syllabus_sdg')
            ->withPivot('id', 'title', 'description')
            ->withTimestamps();
    }
}
