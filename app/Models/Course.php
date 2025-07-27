<?php

// File: app/Models/Course.php
// Description: Represents a course and its related prerequisites, department, and ILOs (Syllaverse)

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    use HasFactory;

    protected $fillable = [
        'department_id',
        'code',
        'title',
        'units_lec',
        'units_lab',
        'total_units',
        'contact_hours_lec',
        'contact_hours_lab',
        'description',
    ];

    // 🔁 Belongs to a department
    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    // 🔁 Many-to-Many: This course requires other courses as prerequisites
    public function prerequisites()
    {
        return $this->belongsToMany(Course::class, 'course_prerequisite', 'course_id', 'prerequisite_id');
    }

    // 🔁 Reverse: This course is a prerequisite for other courses
    public function isPrerequisiteFor()
    {
        return $this->belongsToMany(Course::class, 'course_prerequisite', 'prerequisite_id', 'course_id');
    }

    // ✅ One-to-Many: ILOs defined in the master data for this course
  public function ilos()
{
    return $this->hasMany(\App\Models\IntendedLearningOutcome::class);
}
}
