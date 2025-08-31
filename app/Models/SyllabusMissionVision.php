<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SyllabusMissionVision extends Model
{
    use HasFactory;

    protected $table = 'syllabus_mission_visions';

    protected $fillable = [
        'syllabus_id',
        'mission',
        'vision',
    ];

    public function syllabus()
    {
        return $this->belongsTo(Syllabus::class, 'syllabus_id');
    }
}
