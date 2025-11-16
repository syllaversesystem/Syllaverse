<?php

namespace App\Http\Controllers\Faculty\Syllabus;

use App\Http\Controllers\Controller;
use App\Models\Syllabus;
use App\Models\SyllabusCriteria;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SyllabusCriteriaController extends Controller
{
    /**
     * Normalize and persist criteria rows coming from the main syllabus request payload.
     */
    public function syncFromRequest(Request $request, Syllabus $syllabus): void
    {
        if (! $request->has('criteria_data')) {
            return;
        }

        $raw = $request->input('criteria_data');
        Log::info('SyllabusCriteriaController::syncFromRequest raw', [
            'type' => gettype($raw),
            'criteria_data' => $raw,
            'syllabus_id' => $syllabus->id,
        ]);

        $sections = $this->normalizePayload($raw);

        $this->replaceSections($sections, $syllabus);
    }

    /**
     * Directly persist an array of already structured criteria sections.
     */
    public function sync(array $sections, Syllabus $syllabus): void
    {
        $normalized = $this->normalizeSections($sections);
        $this->replaceSections($normalized, $syllabus);
    }

    /**
     * Normalize an arbitrary payload (array or JSON string) into structured sections.
     */
    protected function normalizePayload($payload): array
    {
        $data = $payload;

        if (! is_array($data)) {
            $decoded = json_decode((string) $payload, true);
            $data = is_array($decoded) ? $decoded : [];
            Log::info('SyllabusCriteriaController::normalizePayload decoded', [
                'syllabus_payload_type' => gettype($payload),
                'decoded_type' => gettype($data),
            ]);
        }

        return $this->normalizeSections($data);
    }

    /**
     * Sanitize the criteria sections and keep only meaningful rows.
     */
    protected function normalizeSections(array $sections): array
    {
        $normalized = [];

        foreach ($sections as $index => $section) {
            if (! is_array($section)) {
                continue;
            }

            if ($this->isMarkedDeleted($section)) {
                Log::info('SyllabusCriteriaController::normalizeSections skipping deleted section', [
                    'index' => $index,
                    'section' => $section,
                ]);
                continue;
            }

            $key = $section['key'] ?? null;
            $heading = $section['heading'] ?? null;
            $values = $section['value'] ?? [];

            $entries = [];
            if (is_array($values)) {
                foreach ($values as $value) {
                    if (! is_array($value)) {
                        continue;
                    }

                    if ($this->isMarkedDeleted($value)) {
                        Log::debug('SyllabusCriteriaController::normalizeSections skipping deleted entry', [
                            'section_key' => $key,
                            'entry' => $value,
                        ]);
                        continue;
                    }

                    $description = trim((string) ($value['description'] ?? ''));
                    $percent = trim((string) ($value['percent'] ?? ''));

                    if ($description === '' && $percent === '') {
                        continue;
                    }

                    $entries[] = [
                        'description' => $description,
                        'percent' => $percent,
                    ];
                }
            }

            if ($key || $heading || count($entries) > 0) {
                $normalized[] = [
                    'key' => $key ?? ('section_' . $index),
                    'heading' => $heading,
                    'values' => $entries,
                ];
            }
        }

        return $normalized;
    }

    /**
     * Determine whether the given payload fragment is flagged for deletion.
     */
    protected function isMarkedDeleted(array $payload): bool
    {
        foreach (['deleted', '_deleted', '_destroy', 'remove', 'removed'] as $flag) {
            if (!array_key_exists($flag, $payload)) {
                continue;
            }

            $value = $payload[$flag];
            if ($value === true || $value === 1 || $value === '1' || $value === 'true') {
                return true;
            }
        }

        return false;
    }

    /**
     * Delete existing criteria rows and replace them with the provided sections.
     */
    protected function replaceSections(array $sections, Syllabus $syllabus): void
    {
        $syllabus->criteria()->delete();

        foreach ($sections as $position => $section) {
            try {
                SyllabusCriteria::create([
                    'syllabus_id' => $syllabus->id,
                    'key' => $section['key'] ?? ('section_' . $position),
                    'heading' => $section['heading'] ?? null,
                    'section' => $section['heading'] ?? null,
                    'value' => $section['values'] ?? [],
                    'position' => $position,
                ]);
            } catch (\Throwable $e) {
                Log::warning('SyllabusCriteriaController::replaceSections failed to create row', [
                    'syllabus_id' => $syllabus->id,
                    'error' => $e->getMessage(),
                    'position' => $position,
                    'section' => $section,
                ]);
            }
        }
    }
}
