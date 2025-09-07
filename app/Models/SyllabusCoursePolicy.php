<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SyllabusCoursePolicy extends Model
{
    use HasFactory;

    protected $table = 'syllabus_course_policies';

    protected $fillable = [
        'syllabus_id',
        'section',
        'content',
        'position',
    ];

    public function syllabus()
    {
        return $this->belongsTo(Syllabus::class, 'syllabus_id');
    }
}
