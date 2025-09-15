<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SyllabusIloSoCpa extends Model
{
    use HasFactory;

    protected $table = 'syllabus_ilo_so_cpa';

    protected $fillable = [
        'syllabus_id',
        'ilo_text',
        'sos',
        'c',
        'p',
        'a',
        'position',
    ];

    protected $casts = [
        'sos' => 'array',
    ];

    public function syllabus()
    {
        return $this->belongsTo(Syllabus::class, 'syllabus_id');
    }
}
