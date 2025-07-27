<?php

// File: app/Models/SyllabusSdg.php
// Description: Pivot model for syllabus-to-SDG mapping with editable description – Syllaverse

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SyllabusSdg extends Model
{
    protected $table = 'syllabus_sdg';

    protected $fillable = [
        'syllabus_id',
        'sdg_id',
        'description'
    ];

    public $timestamps = false;

    public function syllabus()
    {
        return $this->belongsTo(Syllabus::class);
    }

    public function sdg()
    {
        return $this->belongsTo(Sdg::class);
    }
}
