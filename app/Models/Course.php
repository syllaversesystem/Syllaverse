<?php
// -----------------------------------------------------------------------------
// File: app/Models/Course.php
// Description: Represents a course and its related prerequisites, department, 
//              and ILOs (Syllaverse) – refactored to only use contact hours.
// -----------------------------------------------------------------------------
// 📜 Log:
// [2025-08-16] Original version with lec/lab units.
// [2025-08-17] Refactor – removed units_lec/lab, only contact hours remain.
// -----------------------------------------------------------------------------

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    use HasFactory;

    protected $fillable = [
        'department_id',
        'course_category',
        'code',
        'title',
        'has_iga',
        'contact_hours_lec',
        'contact_hours_lab',
        'description',
        'status',
    ];

    // Status constants
    const STATUS_ACTIVE = 'active';
    const STATUS_INACTIVE = 'inactive';
    const STATUS_DELETED = 'deleted';

    // Query scopes
    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    public function scopeNotDeleted($query)
    {
        return $query->whereIn('status', [self::STATUS_ACTIVE, self::STATUS_INACTIVE]);
    }

    // Status check methods
    public function isActive()
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    public function isDeleted()
    {
        return $this->status === self::STATUS_DELETED;
    }

    // 🔁 Belongs to a department
    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    // 🔁 Many-to-Many: prerequisites
    public function prerequisites()
    {
        return $this->belongsToMany(
            Course::class,
            'course_prerequisite',
            'course_id',
            'prerequisite_id'
        );
    }

    // 🔁 Reverse: this course is a prerequisite for others
    public function isPrerequisiteFor()
    {
        return $this->belongsToMany(
            Course::class,
            'course_prerequisite',
            'prerequisite_id',
            'course_id'
        );
    }

    // ✅ One-to-Many: ILOs
    public function ilos()
    {
        return $this->hasMany(\App\Models\IntendedLearningOutcome::class);
    }

    // ⚡ Helper: total contact hours
    public function getTotalContactHoursAttribute()
    {
        return $this->contact_hours_lec + $this->contact_hours_lab;
    }
}
