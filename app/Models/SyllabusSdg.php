<?php

// File: app/Models/SyllabusSdg.php
// Description: Pivot model for syllabus-to-SDG mapping with editable description – Syllaverse

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SyllabusSdg extends Model
{
    // per-syllabus SDG entries (cdio-like structure)
    protected $table = 'syllabus_sdgs';

    protected $fillable = [
        'syllabus_id',
        'code',
        'sort_order',
        'title',
        'description',
    ];

    public $timestamps = true;

    public function syllabus()
    {
        return $this->belongsTo(Syllabus::class);
    }

    // Compute code dynamically from sort_order to avoid duplication/mismatch
    public function getCodeAttribute($value)
    {
        $order = $this->attributes['sort_order'] ?? null;
        return $order ? ('SDG' . $order) : ($value ?? null);
    }
}
