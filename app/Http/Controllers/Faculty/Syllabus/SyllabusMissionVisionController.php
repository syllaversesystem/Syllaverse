<?php

namespace App\Http\Controllers\Faculty\Syllabus;

use App\Http\Controllers\Controller;
use App\Models\GeneralInformation;
use App\Models\Syllabus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SyllabusMissionVisionController extends Controller
{
    /**
     * Populate the syllabus mission & vision record using university-wide defaults.
     * Mission and vision are the same across all departments.
     */
    public function seedFromGeneralInformation(Syllabus $syllabus): void
    {
        // Mission and vision are university-wide (no department-specific overrides)
        $mission = GeneralInformation::where('section', 'mission')->whereNull('department_id')->first()?->content ?? '';
        $vision  = GeneralInformation::where('section', 'vision')->whereNull('department_id')->first()?->content ?? '';

        try {
            $syllabus->missionVision()->updateOrCreate([], [
                'mission' => $mission,
                'vision'  => $vision,
            ]);
        } catch (\Throwable $e) {
            Log::warning('SyllabusMissionVision::seedFromGeneralInformation failed', [
                'syllabus_id' => $syllabus->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Validate and persist mission & vision values coming from the syllabus form request.
     */
    public function syncFromRequest(Request $request, Syllabus $syllabus): void
    {
        // If neither mission nor vision present in the request, treat as no-op (allows partial saves)
        if (! $request->has('mission') && ! $request->has('vision')) {
            return;
        }

        // Build dynamic validation rules: require only the fields that are present
        $rules = [];
        if ($request->has('mission')) $rules['mission'] = 'required|string';
        if ($request->has('vision')) $rules['vision'] = 'required|string';

        $payload = $request->validate($rules);

        $this->sync($payload, $syllabus);
    }

    /**
     * Upsert the mission & vision values for the given syllabus.
     */
    public function sync(array $payload, Syllabus $syllabus): void
    {
        try {
            $syllabus->missionVision()->updateOrCreate([], [
                'mission' => $payload['mission'] ?? '',
                'vision'  => $payload['vision'] ?? '',
            ]);
        } catch (\Throwable $e) {
            Log::error('SyllabusMissionVision::sync failed', [
                'syllabus_id' => $syllabus->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Build the mission & vision defaults for the syllabus view payload.
     * Mission and vision are university-wide (same for all departments).
     */
    public function defaults(Syllabus $syllabus): array
    {
        $missionVision = $syllabus->missionVision;

        $mission = $missionVision?->mission;
        $vision = $missionVision?->vision;

        // Mission and vision are university-wide (no department-specific overrides)
        if ($mission === null || $mission === '') {
            $mission = GeneralInformation::where('section', 'mission')->whereNull('department_id')->first()?->content ?? '';
        }

        if ($vision === null || $vision === '') {
            $vision = GeneralInformation::where('section', 'vision')->whereNull('department_id')->first()?->content ?? '';
        }

        return [
            'mission' => $mission,
            'vision' => $vision,
        ];
    }
}
