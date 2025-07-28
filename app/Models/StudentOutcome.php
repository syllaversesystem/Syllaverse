<?php

// File: app/Models/StudentOutcome.php
// Description: Eloquent model for the student_outcomes table (Syllaverse)

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentOutcome extends Model
{
    use HasFactory;

    protected $fillable = ['code', 'description', 'position'];
}
