<?php

// File: app/Models/IntendedLearningOutcome.php
// Description: Eloquent model for the intended_learning_outcomes table (Syllaverse)
// -----------------------------------------------------------------------------
// 📜 Log:
// [2025-07-29] Added 'position' to $fillable for reorder support.
// -----------------------------------------------------------------------------

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IntendedLearningOutcome extends Model
{
    use HasFactory;

    // 🛠️ Include course_id and position to allow saving course-linked ILOs
    protected $fillable = ['code', 'description', 'course_id', 'position'];

    // 🔁 Relationship: Each ILO belongs to one course
    public function course()
    {
        return $this->belongsTo(Course::class);
    }
}