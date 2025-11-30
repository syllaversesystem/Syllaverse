<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SyllabusComment extends Model
{
    use HasFactory;

    protected $table = 'syllabus_comments';

    protected $fillable = [
        'syllabus_id',
        'partial_key',
        'title',
        'body',
        'status',
        'batch',
        'created_by',
        'updated_by',
    ];

    public function syllabus()
    {
        return $this->belongsTo(Syllabus::class);
    }

    public function author()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function editor()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
