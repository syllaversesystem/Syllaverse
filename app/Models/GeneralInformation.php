<?php

// File: app/Models/GeneralInformation.php
// Description: Model for storing general academic information like Mission, Vision, etc.
// Supports both university-wide defaults (department_id = NULL) and department-specific overrides

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GeneralInformation extends Model
{
    protected $table = 'general_information';

    protected $fillable = ['section', 'content', 'department_id'];

    public $timestamps = true;

    /**
     * Relationship: belongs to a department (nullable for university-wide defaults)
     */
    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Get content for a specific section with department fallback
     * Lookup hierarchy: Department-specific → University default
     * 
     * @param string $section The section name (e.g., 'mission', 'vision', 'policy')
     * @param int|null $departmentId The department ID to check for overrides
     * @return string The content or empty string if not found
     */
    public static function getContent(string $section, ?int $departmentId = null): string
    {
        // If department ID provided, try department-specific first
        if ($departmentId) {
            $departmentSpecific = static::where('section', $section)
                ->where('department_id', $departmentId)
                ->first();
            
            if ($departmentSpecific) {
                return $departmentSpecific->content ?? '';
            }
        }

        // Fallback to university-wide default (department_id = NULL)
        $universityDefault = static::where('section', $section)
            ->whereNull('department_id')
            ->first();

        return $universityDefault?->content ?? '';
    }
}
