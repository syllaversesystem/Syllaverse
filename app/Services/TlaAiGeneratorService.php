<?php

// -----------------------------------------------------------------------------
// File: app/Services/TlaAiGeneratorService.php
// Description: Handles Gemini-powered generation of TLA plans based on syllabus data – Syllaverse
// -----------------------------------------------------------------------------
// 📜 Log:
// [2025-07-30] Initial creation of modular AI service class for generating 18-week CIS-style TLA plans.
// [2025-07-30] Added raw Gemini output logging and fallback decoding if JSON parse fails.
// -----------------------------------------------------------------------------

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use App\Models\Syllabus;

class TlaAiGeneratorService
{
    protected Syllabus $syllabus;

    public function forSyllabus(Syllabus $syllabus): self
    {
        $this->syllabus = $syllabus;
        return $this;
    }

    // 🔄 Master method: collect context → call Gemini → return usable TLA rows
   public function generate(): array
{
    $context = $this->collectContext();
    $prompt = $this->buildPrompt($context);
    $rawOutput = $this->callGemini($prompt);
    $rows = $this->parseResponse($rawOutput);

    $validated = $this->validateTotalWeeks($rows);
    $validated['__prompt'] = $prompt;
    $validated['__raw'] = $rawOutput;

    return $validated;
}


    // 📊 Gather ILOs, SOs, SDGs, and textbook snippets
    protected function collectContext(): array
    {
        $ilos = $this->syllabus->ilos->map(fn($i) => "{$i->code}: {$i->description}")->implode("\n");
        $sos = $this->syllabus->sos->map(fn($s) => "{$s->code}: {$s->description}")->implode("\n");
        $sdgs = $this->syllabus->sdgs->map(fn($s) => "{$s->title}: {$s->description}")->implode("\n");

        $textbookSnippets = $this->syllabus->textbooks
            ->filter(fn($tb) => Storage::disk('public')->exists($tb->file_path))
            ->map(function ($tb) {
                return "Title: {$tb->original_name}";
                // ✊ TODO: Add actual text extraction (PDF/Text parsing)
            })->implode("\n");

        return compact('ilos', 'sos', 'sdgs', 'textbookSnippets');
    }

    // 📑 Builds a CIS-aware TLA planning prompt
    protected function buildPrompt(array $context): string
    {
        return <<<PROMPT
You are an expert in curriculum design for Information Technology.

Create a detailed Teaching and Learning Activities (TLA) plan for a 3rd-year BSIT course titled "Fundamentals of Enterprise Data Management".

Use the following:
- Intended Learning Outcomes (ILO):\n{$context['ilos']}
- Student Outcomes (SO):\n{$context['sos']}
- Sustainable Development Goals (SDG):\n{$context['sdgs']}
- Textbooks:\n{$context['textbookSnippets']}

Generate a CIS-format table plan covering exactly 18 academic weeks. Each TLA row must include:
- Chapter number
- Topics / Reading List
- Week/s (e.g. 1-2, 3, 4-5, max 18 total)
- Topic Outcomes
- ILO codes (e.g. ILO1, ILO3)
- SO codes (e.g. SO2, SO4)
- Delivery Method (Lecture, Lab, Module, Research Review, etc)

Output the result as valid JSON array of objects with keys: ch, topic, wks, outcomes, ilo_codes, so_codes, delivery.
PROMPT;
    }

    // 🎡 Sends prompt to Gemini API and returns raw response
protected function callGemini(string $prompt): string
{
    $apiKey = config('services.gemini.key');

    $response = Http::withHeaders([
        'Content-Type' => 'application/json',
        'Authorization' => "Bearer {$apiKey}",
    ])->post('https://generativelanguage.googleapis.com/v1beta/models/gemini-pro:generateContent', [
        'contents' => [
            ['parts' => [['text' => $prompt]]]
        ]
    ]);

    \Log::debug('[AI] Gemini full response body:', ['body' => $response->body()]);

    return $response->json('candidates.0.content.parts.0.text') ?? '';
}


    // 📊 Parse Gemini's JSON response
    protected function parseResponse(string $raw): array
    {
        try {
            return json_decode($raw, true) ?? [];
        } catch (\Throwable $e) {
            Log::error('[AI] Failed to parse JSON from Gemini output', ['error' => $e->getMessage(), 'raw' => $raw]);
            return [];
        }
    }

    // ⏳ Ensure that total week coverage ≤ 18 weeks
    protected function validateTotalWeeks(array $rows): array
    {
        $total = 0;
        $filtered = [];

        foreach ($rows as $row) {
            $range = explode('-', str_replace(' ', '', $row['wks']));
            $start = (int) ($range[0] ?? 0);
            $end = (int) ($range[1] ?? $range[0]);
            $span = max(1, $end - $start + 1);

            if ($total + $span > 18) break;

            $total += $span;
            $filtered[] = $row;
        }

        return $filtered;
    }
}
