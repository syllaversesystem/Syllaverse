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
     * Populate the syllabus mission & vision record using the system-wide defaults.
     */
    public function seedFromGeneralInformation(Syllabus $syllabus): void
    {
        $mission = GeneralInformation::where('section', 'mission')->first()?->content ?? '';
        $vision  = GeneralInformation::where('section', 'vision')->first()?->content ?? '';

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
        $payload = $request->validate([
            'mission' => 'required|string',
            'vision' => 'required|string',
        ]);

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
     */
    public function defaults(Syllabus $syllabus): array
    {
        $missionVision = $syllabus->missionVision;

        $mission = $missionVision?->mission;
        $vision = $missionVision?->vision;

        if ($mission === null || $mission === '') {
            $mission = GeneralInformation::where('section', 'mission')->first()?->content ?? '';
        }

        if ($vision === null || $vision === '') {
            $vision = GeneralInformation::where('section', 'vision')->first()?->content ?? '';
        }

        return [
            'mission' => $mission,
            'vision' => $vision,
        ];
    }
}
