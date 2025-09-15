<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SyllabusIloIga extends Model
{
    use HasFactory;

    protected $table = 'syllabus_ilo_iga';

    protected $fillable = [
        'syllabus_id',
        'ilo_text',
        'igas',
        'position',
    ];

    protected $casts = [
        'igas' => 'array',
    ];

    public function syllabus()
    {
        return $this->belongsTo(Syllabus::class, 'syllabus_id');
    }
}
