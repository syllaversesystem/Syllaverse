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
        $contextAll = trim((string)$request->input('context_all', ''));
        $contextPhase1 = trim((string)$request->input('context_phase1', ''));
        $contextPhase2 = trim((string)$request->input('context_phase2', ''));
        $contextPhase3 = trim((string)$request->input('context_phase3', ''));
        $contextCombinedExact = trim((string)$request->input('context', ''));
        $useSameChatContext = $request->boolean('use_same_chat_context');
        // Prefer a single chat_payload object if provided (exact parity with AI Chat)
        $chatPayload = $request->input('chat_payload');
        if (is_array($chatPayload)) {
            $chatMessage = (string)($chatPayload['message'] ?? '');
            $contextCombinedExact = (string)($chatPayload['context'] ?? '');
            $contextPhase1 = (string)($chatPayload['context_phase1'] ?? '');
            $contextPhase2 = (string)($chatPayload['context_phase2'] ?? '');
            $contextPhase3 = (string)($chatPayload['context_phase3'] ?? '');
            $historyJson = (string)($chatPayload['history'] ?? '');
            $phaseFromChat = (string)($chatPayload['phase'] ?? '');
            if (!$request->has('phase') && $phaseFromChat !== '') {
                $request->merge(['phase' => $phaseFromChat]);
            }
        }
        $chatMessage = trim((string)$request->input('message', ''));
        $historyJson = (string)$request->input('history', '');
        $history = null;
        if ($historyJson) {
            try { $history = json_decode($historyJson, true, 512, JSON_THROW_ON_ERROR); } catch (\Throwable $e) { $history = null; }
        }
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
        // If chat history exists, include a compact summary to provide continuity
        if (is_array($history) && count($history)) {
            $summary = json_encode($history);
            if (strlen($summary) > 8000) { $summary = substr($summary, 0, 8000) . ' [trimmed]'; }
            $messages[] = ['role' => 'system', 'content' => "CHAT_HISTORY_SUMMARY:\n" . $summary];
        }
        // If chat history exists, include a compact summary to provide continuity
        if (is_array($history) && count($history)) {
            $summary = json_encode($history);
            if (strlen($summary) > 8000) { $summary = substr($summary, 0, 8000) . ' [trimmed]'; }
            $messages[] = ['role' => 'system', 'content' => "CHAT_HISTORY_SUMMARY:\n" . $summary];
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

        // Compose content with exact chat contexts when available to mirror AI Chat input
        // If asked to use the same chat context, prefer the exact combined ordering provided
        // Simplify prompt: use only the text "hotdog" as the user content
        $userContent = 'hotdog';
        $messages[] = ['role' => 'user', 'content' => $userContent];

        // Build a sanitized preview of the OpenAI request
        $systemContents = [];
        foreach ($messages as $m) {
            if (($m['role'] ?? '') === 'system') {
                $c = (string)($m['content'] ?? '');
                if (strlen($c) > 12000) $c = substr($c, 0, 12000) . ' [trimmed]';
                $systemContents[] = $c;
            }
        }
        $requestPreview = [
            'model' => $model,
            'system_messages' => $systemContents,
            'user_message' => (strlen($userContent) > 12000) ? (substr($userContent, 0, 12000) . ' [trimmed]') : $userContent,
        ];

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
            // If user wants plain text (no JSON), return as-is
            $plainTextOnly = true;
            if ($plainTextOnly) {
                return response()->json([
                    'success' => true,
                    'text' => $content,
                    'request_preview' => $requestPreview,
                ]);
            }
            // Attempt to decode JSON directly (legacy path)
            $json = null;
            try { $json = json_decode($content, true, 512, JSON_THROW_ON_ERROR); } catch (\Throwable $e) {}
            if (!is_array($json) || !isset($json['mappings']) || !is_array($json['mappings'])) {
                return response()->json(['error' => 'Invalid AI response format'], 500);
            }

            return response()->json([
                'success' => true,
                'mappings' => $json['mappings'],
                'weeks' => $json['weeks'] ?? [],
                'request_preview' => $requestPreview,
            ]);
        } catch (\Throwable $e) {
            Log::error('AI autoMap exception', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'AI request failed'], 500);
        }
    }
}
