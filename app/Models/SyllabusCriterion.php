<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SyllabusCriterion extends Model
{
    use HasFactory;

    protected $table = 'syllabus_criteria';

    protected $fillable = [
        'syllabus_id',
        'key',
        'heading',
        'value',
        'position',
    ];

    protected $casts = [
        'value' => 'array',
    ];

    public function syllabus()
    {
        return $this->belongsTo(Syllabus::class);
    }
}
