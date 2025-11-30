<?php

// File: app/Models/Syllabus.php
// Description: Represents a syllabus created by a faculty; includes relationships for program, course, ILOs, SOs, TLAs, textbooks, and mapped SDGs – Syllaverse

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Syllabus extends Model
{
    use HasFactory;

    protected static function booted(): void
    {
        // When a syllabus is created, seed master CDIOs into the per-syllabus table (best-effort)
        static::created(function (self $syllabus) {
            try {
                // If per-syllabus CDIOs already exist (controller may have seeded them), skip
                if (\App\Models\SyllabusCdio::where('syllabus_id', $syllabus->id)->exists()) {
                    return;
                }

                if (\Illuminate\Support\Facades\Schema::hasTable('cdios')) {
                    $masterCdios = Cdio::ordered()->get();
                    $pos = 1;
                    foreach ($masterCdios as $mcdio) {
                        try {
                            SyllabusCdio::create([
                                'syllabus_id' => $syllabus->id,
                                'code' => $mcdio->code ?? Cdio::makeCodeFromPosition($pos),
                                'description' => $mcdio->description ?? $mcdio->title ?? null,
                                'position' => $pos++,
                            ]);
                        } catch (\Throwable $__ignore) {
                            // continue seeding remaining items
                            continue;
                        }
                    }
                }
            } catch (\Throwable $e) {
                \Log::warning('Syllabus model created: failed to seed master CDIOs', ['error' => $e->getMessage(), 'syllabus_id' => $syllabus->id]);
            }
        });
    }

    protected $fillable = [
        'faculty_id',
        'program_id',
        'course_id',
        'title',
        'academic_year',
        'semester',
        'year_level',
        // Submission workflow fields
        'submission_status',
        'submission_remarks',
        'submitted_at',
        'reviewed_by',
        'reviewed_at',
        // Serialized assessment tasks JSON created by the AT module
        'assessment_tasks_data',
        // Serialized ILO->SO->CPA mapping payload
        'ilo_so_cpa_data',
        // Status fields (Prepared/Reviewed/Approved)
        'prepared_by_name',
        'prepared_by_title',
        'prepared_by_date',
        'reviewed_by_name',
        'reviewed_by_title',
        'reviewed_by_date',
        'approved_by_name',
        'approved_by_title',
        'approved_by_date',
        'status_remarks',
    ];

    // 🔁 Each syllabus belongs to one faculty
    public function faculty()
    {
        return $this->belongsTo(User::class, 'faculty_id');
    }

    // 🔁 Each syllabus belongs to one course
    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    // 🔁 Each syllabus belongs to one program
    public function program()
    {
        return $this->belongsTo(Program::class);
    }

    // 🔁 A syllabus has many criteria
    public function criteria()
    {
        return $this->hasMany(SyllabusCriteria::class)->orderBy('position');
    }

    // 🔁 A syllabus has many topic-learning activities (TLA)
    public function tla()
    {
        return $this->hasMany(TLA::class);
    }

    // 🔁 A syllabus has many uploaded textbook files
    public function textbooks()
    {
        return $this->hasMany(SyllabusTextbook::class);
    }

    // 🔁 A syllabus has many Intended Learning Outcomes (ILO)
    public function ilos()
    {
        return $this->hasMany(SyllabusIlo::class);
    }

    // 🔁 A syllabus has many Student Outcomes (SO)
    public function sos()
    {
        return $this->hasMany(SyllabusSo::class);
    }

    // 🔁 A syllabus has many Institutional Graduate Attributes (IGA)
    public function igas()
    {
        return $this->hasMany(SyllabusIga::class)->orderBy('position');
    }

    // 🔁 A syllabus has many CDIO entries (per-syllabus copies of master CDIOs)
    public function cdios()
    {
        return $this->hasMany(SyllabusCdio::class)->orderBy('position');
    }

    // Per-syllabus overrides for course information (so edits inside a syllabus don't change master course)
    public function courseInfo()
    {
        return $this->hasOne(SyllabusCourseInfo::class, 'syllabus_id');
    }

    // Per-syllabus stored assessment tasks table
    public function assessmentTasks()
    {
        return $this->hasMany(SyllabusAssessmentTask::class, 'syllabus_id')->orderBy('position');
    }

    // Per-syllabus assessment mappings (name + week_marks)
    public function assessmentMappings()
    {
        return $this->hasMany(SyllabusAssessmentMapping::class, 'syllabus_id')->orderBy('position');
    }

    // Per-syllabus ILO -> SO -> CPA mapping (normalized table)
    public function iloSoCpa()
    {
        return $this->hasMany(SyllabusIloSoCpa::class, 'syllabus_id')->orderBy('position');
    }

    // Per-syllabus ILO -> IGA mapping (normalized table)
    public function iloIga()
    {
        return $this->hasMany(SyllabusIloIga::class, 'syllabus_id')->orderBy('position');
    }

    // Per-syllabus ILO -> CDIO -> SDG mapping (normalized table)
    public function iloCdioSdg()
    {
        return $this->hasMany(SyllabusIloCdioSdg::class, 'syllabus_id')->orderBy('position');
    }

    // mission/vision moved to a separate table so they can be managed independently
    public function missionVision()
    {
        return $this->hasOne(SyllabusMissionVision::class, 'syllabus_id');
    }

    // 🔁 A syllabus maps many SDGs with editable pivot data
    public function sdgs()
    {
        // per-syllabus SDG entries (not a simple many-to-many pivot anymore)
        return $this->hasMany(SyllabusSdg::class)->orderBy('sort_order');
    }

    // 🔁 A syllabus can have multiple faculty members (owner, collaborators, viewers)
    public function facultyMembers()
    {
        return $this->belongsToMany(User::class, 'faculty_syllabus', 'syllabus_id', 'faculty_id')
                    ->withPivot('role', 'can_edit')
                    ->withTimestamps();
    }

    // 🔁 Reviewer relationship for submission workflow
    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    // 🔁 Submission history
    public function submissions()
    {
        return $this->hasMany(SyllabusSubmission::class)->orderBy('action_at', 'desc');
    }

    /**
     * Determine if the syllabus can be edited by the given faculty user.
     * Uses the pivot table faculty_syllabus (can_edit flag) or direct owner faculty_id.
     */
    public function canBeEditedBy($facultyId): bool
    {
        if (!$facultyId) return false;
        // Owner shortcut
        if ((int)$this->faculty_id === (int)$facultyId) return true;
        // Collaborators with can_edit flag
        return $this->facultyMembers()
            ->where('faculty_id', $facultyId)
            ->where(function($q){ $q->where('can_edit', true)->orWhereNull('can_edit'); })
            ->exists();
    }
}
