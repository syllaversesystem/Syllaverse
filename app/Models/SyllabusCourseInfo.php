<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SyllabusCourseInfo extends Model
{
    use HasFactory;

    protected $table = 'syllabus_course_infos';

    protected $fillable = [
        'syllabus_id', 'course_title', 'course_code', 'course_category', 'course_prerequisites',
        'semester', 'year_level', 'credit_hours_text', 'instructor_name', 'employee_code',
        'reference_cmo', 'instructor_designation', 'date_prepared', 'instructor_email',
    'revision_no', 'academic_year', 'revision_date', 'course_description',
    'contact_hours', 'contact_hours_lec', 'contact_hours_lab'
    ];

    public function syllabus()
    {
        return $this->belongsTo(Syllabus::class);
    }
}
