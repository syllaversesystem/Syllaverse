<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use App\Models\Syllabus;

class AssessmentMappingAIController extends Controller
{
    public function autoMap(Request $request, Syllabus $syllabus)
    {
        $snapshot = trim((string)$request->input('snapshot', ''));
        if ($snapshot === '') {
            return response()->json(['error' => 'Missing snapshot'], 422);
        }

        $apiKey = env('OPENAI_API_KEY');
        $model = env('OPENAI_MODEL', 'gpt-4.1');
        if (!$apiKey) {
            return response()->json(['error' => 'AI service not configured'], 500);
        }

        // Load external teaching prompt if available
        $teachingPromptPath = resource_path('ai/prompts/syllabus-teaching-prompt.md');
        $externalTeachingPrompt = null;
        try {
            if (File::exists($teachingPromptPath)) {
                $raw = File::get($teachingPromptPath);
                // Cap extremely long prompts
                $externalTeachingPrompt = mb_substr($raw, 0, 20000);
            }
        } catch (\Throwable $e) {
            // Non-fatal
        }

        // System message: prefer external prompt
        $messages = [];
        if ($externalTeachingPrompt) {
            $messages[] = ['role' => 'system', 'content' => $externalTeachingPrompt];
        }

        // Instruction: produce ONLY JSON of assessment schedule/mappings
                $userInstruction = <<<TXT
You are SyllaverseAI. Based on the provided syllabus snapshot, generate the Assessment Schedule (weeks × codes) and normalized Assessment Mappings rows.

Return ONLY JSON with the following shape:
{
  "mappings": [
    { "distribution": "<task name>", "weeks": [<int week numbers>], "code": "<short code e.g., ME, FE, QCT, ARR>" }
  ],
  "weeks": [1,2,...,18]
}

Rules:
- Term length is 18 weeks (clamp entries to 1–18).
 - Midterm Exam (ME) must be week 9.
 - Final Exam (FE) must be week 17.
 - Week 18 is administrative only: "Release of Examination Results" and "Submission of Grades" (do not include as graded mappings).
- Use task codes consistent with the snapshot when possible; otherwise choose concise conventional codes.
- Do not include any text outside the JSON.
TXT;

        $messages[] = ['role' => 'user', 'content' => $userInstruction . "\n\nSNAPSHOT_BEGIN\n" . $snapshot . "\nSNAPSHOT_END"];

        try {
            $resp = Http::withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type' => 'application/json',
            ])->post('https://api.openai.com/v1/chat/completions', [
                'model' => $model,
                'messages' => $messages,
                'temperature' => 0.2,
            ]);

            if (!$resp->ok()) {
                Log::warning('AI autoMap error', ['status' => $resp->status(), 'body' => $resp->body()]);
                return response()->json(['error' => 'AI service error'], 502);
            }

            $data = $resp->json();
            $content = $data['choices'][0]['message']['content'] ?? '';
            // Attempt to decode JSON directly
            $json = null;
            try { $json = json_decode($content, true, 512, JSON_THROW_ON_ERROR); } catch (\Throwable $e) {}
            if (!is_array($json) || !isset($json['mappings']) || !is_array($json['mappings'])) {
                return response()->json(['error' => 'Invalid AI response format'], 500);
            }

            // Return normalized mappings to client; client will render and save via existing flow
            return response()->json([
                'success' => true,
                'mappings' => $json['mappings'],
                'weeks' => $json['weeks'] ?? [],
            ]);
        } catch (\Throwable $e) {
            Log::error('AI autoMap exception', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'AI request failed'], 500);
        }
    }
}
