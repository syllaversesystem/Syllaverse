<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SyllabusCriteria extends Model
{
    use HasFactory;

    protected $table = 'syllabus_criteria'; // Specify the exact table name

    protected $fillable = [
        'syllabus_id',
        'key',
        'heading',
        'section',
        'value',
        'position',
    ];

    protected $casts = [
        'value' => 'array',
    ];

    // Relationship to syllabus
    public function syllabus()
    {
        return $this->belongsTo(Syllabus::class);
    }
}
