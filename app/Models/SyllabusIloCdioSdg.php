<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SyllabusIloCdioSdg extends Model
{
    protected $table = 'syllabus_ilo_cdio_sdg';

    protected $fillable = [
        'syllabus_id',
        'ilo_text',
        'cdios',
        'sdgs',
        'position',
    ];

    protected $casts = [
        'cdios' => 'array',
        'sdgs' => 'array',
    ];

    public function syllabus()
    {
        return $this->belongsTo(Syllabus::class);
    }
}
