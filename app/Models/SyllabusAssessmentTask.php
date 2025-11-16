<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SyllabusAssessmentTask extends Model
{
    use HasFactory;

    protected $table = 'syllabus_assessment_tasks';

    protected $fillable = [
        'syllabus_id', 
        'section_number', 
        'row_type', 
        'section_label', 
        'section_legacy', 
        'code', 
        'task', 
        'ird', 
        'percent', 
        'ilo_flags', 
        'c', 
        'p', 
        'a', 
        'position'
    ];

    protected $casts = [
        'ilo_flags' => 'array',
        'percent' => 'decimal:2',
        'section_number' => 'integer',
    ];

    public function syllabus()
    {
        return $this->belongsTo(Syllabus::class);
    }
}
