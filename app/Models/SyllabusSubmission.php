<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SyllabusSubmission extends Model
{
    use HasFactory;

    protected $fillable = [
        'syllabus_id',
        'submitted_by',
        'from_status',
        'to_status',
        'action_by',
        'remarks',
        'action_at',
    ];

    protected $casts = [
        'action_at' => 'datetime',
    ];

    /**
     * Relationship: Submission belongs to a syllabus
     */
    public function syllabus()
    {
        return $this->belongsTo(Syllabus::class);
    }

    /**
     * Relationship: User who submitted
     */
    public function submitter()
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    /**
     * Relationship: User who performed the action (approved/rejected/etc)
     */
    public function actionBy()
    {
        return $this->belongsTo(User::class, 'action_by');
    }
}
