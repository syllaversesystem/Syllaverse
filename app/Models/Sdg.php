<?php

// File: app/Models/Sdg.php
// Description: Sustainable Development Goal (SDG) master data; can be attached to multiple syllabi with editable pivot values – Syllaverse

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sdg extends Model
{
    protected $fillable = ['title', 'description'];

    // 🔁 An SDG can be mapped to many syllabi
    public function syllabi()
    {
        return $this->belongsToMany(Syllabus::class, 'syllabus_sdg')
            ->withPivot('id', 'title', 'description')
            ->withTimestamps();
    }
}
