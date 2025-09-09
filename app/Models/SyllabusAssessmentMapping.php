<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SyllabusAssessmentMapping extends Model
{
    use HasFactory;

    protected $table = 'syllabus_assessment_mappings';

    protected $fillable = [
        'syllabus_id', 'name', 'week_marks', 'position'
    ];

    protected $casts = [
        'week_marks' => 'array',
    ];

    public function syllabus()
    {
        return $this->belongsTo(Syllabus::class);
    }
}
