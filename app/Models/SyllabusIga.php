<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SyllabusIga extends Model
{
    protected $table = 'syllabus_igas';
    protected $fillable = ['syllabus_id', 'code', 'description', 'position'];
    protected $casts = ['position' => 'integer'];
}
