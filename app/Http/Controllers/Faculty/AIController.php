<?php

namespace App\Http\Controllers\Faculty;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;

class AIController extends Controller
{
    /**
     * OpenAI API endpoint
     */
    private const OPENAI_API_URL = 'https://api.openai.com/v1/chat/completions';

    /**
     * Handle AI chat request for syllabus assistance
     *
     * @param Request $request
     * @param int $syllabusId
     * @return \Illuminate\Http\JsonResponse
     */
    public function chat(Request $request, $syllabusId)
    {
        try {
            // Validate request (no max length caps)
            $validated = $request->validate([
                'message' => 'required|string',
                'context' => 'nullable|string',
                'snapshots' => 'nullable|json',
                'prompt' => 'nullable|string',
                'prompts' => 'nullable|json',
                'history' => 'nullable|json',
            ]);

            $userMessage = $validated['message'];
            $context = $validated['context'] ?? '';
            $snapshots = json_decode($validated['snapshots'] ?? '[]', true);
            $prompt = $validated['prompt'] ?? '';
            $allPrompts = json_decode($validated['prompts'] ?? '{}', true);
            $history = json_decode($validated['history'] ?? '[]', true);

            // Load ALL textbook chunks for this syllabus (no limit)
            $textbookChunks = $this->loadTextbookChunks($syllabusId, null);

            // Get OpenAI API key from environment
            $apiKey = config('services.openai.api_key');
            
            if (empty($apiKey)) {
                return response()->json([
                    'success' => false,
                    'error' => 'OpenAI API key not configured'
                ], 500);
            }

            // Build conversation messages for OpenAI
            $messages = $this->buildMessages($userMessage, $context, $history, $snapshots, $prompt, $allPrompts, $textbookChunks);

            // Call OpenAI API
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type' => 'application/json',
            ])
            ->timeout(60)
            ->post(self::OPENAI_API_URL, [
                'model' => config('services.openai.model', 'gpt-4-turbo-preview'),
                'messages' => $messages,
                'temperature' => 0.7,
                // Intentionally omit max_tokens to avoid capping output
                'top_p' => 1,
                'frequency_penalty' => 0,
                'presence_penalty' => 0,
            ]);

            if (!$response->successful()) {
                Log::error('OpenAI API error', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);

                return response()->json([
                    'success' => false,
                    'error' => 'Failed to get response from AI service'
                ], 500);
            }

            $responseData = $response->json();
            $reply = $responseData['choices'][0]['message']['content'] ?? 'No response generated';

            return response()->json([
                'success' => true,
                'reply' => $reply,
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'error' => 'Invalid request data',
                'details' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            Log::error('AI Chat error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'An error occurred while processing your request'
            ], 500);
        }
    }

    /**
     * Build messages array for OpenAI API
     *
     * @param string $userMessage
     * @param string $context
     * @param array $history
     * @param array $snapshots
     * @param string $prompt
     * @param array $allPrompts
     * @param array $textbookChunks
     * @return array
     */
    private function buildMessages(string $userMessage, string $context, array $history, array $snapshots = [], string $prompt = '', array $allPrompts = [], array $textbookChunks = []): array
    {
        $messages = [];

        // System message with instructions
        $systemPrompt = $this->getSystemPrompt();
        $messages[] = [
            'role' => 'system',
            'content' => $systemPrompt
        ];

        // Add context if provided
        if (!empty($context)) {
            $contextData = json_decode($context, true);
            
            if (is_array($contextData) && !empty($contextData['sections'])) {
                $contextMessage = "Current syllabus context:\n\n";
                
                if (!empty($contextData['courseTitle'])) {
                    $contextMessage .= "Course: {$contextData['courseTitle']}\n";
                }
                
                if (!empty($contextData['courseCode'])) {
                    $contextMessage .= "Code: {$contextData['courseCode']}\n\n";
                }

                $contextMessage .= "Sections:\n";
                foreach ($contextData['sections'] as $section) {
                    $contextMessage .= "- {$section['key']}: " . substr($section['text'], 0, 500) . "...\n";
                }

                $messages[] = [
                    'role' => 'system',
                    'content' => $contextMessage
                ];
            }
        }

        // Add comprehensive prompt library
        $promptLibrary = "You have access to specialized guidance for analyzing syllabus sections:\n\n";
        
        if (!empty($allPrompts) && is_array($allPrompts)) {
            foreach ($allPrompts as $promptKey => $promptText) {
                if (!empty($promptText)) {
                    $promptLibrary .= "## {$promptKey}\n{$promptText}\n\n";
                }
            }
        }

        // Add partial-specific prompt if provided
        if (!empty($prompt)) {
            $promptLibrary .= "## Current Focus\n{$prompt}\n";
        }

        $messages[] = [
            'role' => 'system',
            'content' => $promptLibrary,
        ];

        // Add textbook chunks if available
        if (!empty($textbookChunks) && is_array($textbookChunks)) {
            $textbookContext = "Uploaded Textbook Content (use this as reference material):\n\n";
            $chunkCount = count($textbookChunks);
            $textbookContext .= "Total chunks available: {$chunkCount}\n\n";
            
            foreach ($textbookChunks as $chunk) {
                $name = $chunk['original_name'] ?? 'Textbook';
                $index = $chunk['chunk_index'] ?? 0;
                $content = $chunk['content'] ?? '';
                // Include full content of each chunk (no truncation)
                $textbookContext .= "### {$name} (Chunk {$index})\n{$content}\n\n";
            }
            $messages[] = [
                'role' => 'system',
                'content' => $textbookContext,
            ];
        }

        // Add structured snapshots (markdown per partial)
        if (!empty($snapshots) && is_array($snapshots)) {
            $snapshotMessage = "Structured syllabus snapshots (all partials):\n\n";
            foreach ($snapshots as $snap) {
                $key = $snap['key'] ?? 'snapshot';
                $markdown = $snap['markdown'] ?? '';
                $snapshotMessage .= "## {$key}\n{$markdown}\n\n";
            }

            $messages[] = [
                'role' => 'system',
                'content' => $snapshotMessage,
            ];
        }

        // Add conversation history (last 10 messages)
        $recentHistory = array_slice($history, -10);
        foreach ($recentHistory as $msg) {
            if (isset($msg['role']) && isset($msg['text'])) {
                $role = $msg['role'] === 'user' ? 'user' : 'assistant';
                $messages[] = [
                    'role' => $role,
                    'content' => $msg['text']
                ];
            }
        }

        // Add current user message
        $messages[] = [
            'role' => 'user',
            'content' => $userMessage
        ];

        return $messages;
    }

    /**
     * Get system prompt for AI assistant
     *
     * @return string
     */
    private function getSystemPrompt(): string
    {
        return <<<PROMPT
You are an expert AI assistant specialized in syllabus development and curriculum design. You help faculty members create, review, and improve their course syllabi.

Your expertise includes:
- Course design and learning outcome alignment
- Bloom's taxonomy and cognitive levels
- Educational frameworks (ILO, SO, IGA, CDIO, SDG)
- Assessment strategies and rubrics
- Teaching methodologies and best practices
- Curriculum mapping and alignment

Guidelines:
- Provide clear, actionable, and pedagogically sound advice
- Be specific and practical in your suggestions
- Consider educational standards and accreditation requirements
- Support evidence-based teaching practices
- Maintain professional and supportive tone
- When suggesting content, provide examples that can be directly used
- If asked about specific sections, reference the context provided

Format your responses clearly:
- Use bullet points for lists
- Use **bold** for emphasis
- Use numbered lists for sequential steps
- Keep paragraphs concise and focused

Remember: You're assisting in creating high-quality, accreditation-ready syllabi that enhance student learning.
PROMPT;
    }

    /**
     * Load textbook chunks for the given syllabus
     *
     * @param int $syllabusId
     * @param int|null $limit Number of chunks to load (null = all chunks)
     * @return array
     */
    private function loadTextbookChunks(int $syllabusId, ?int $limit = null): array
    {
        try {
            $query = DB::table('textbook_chunks')
                ->join('syllabus_textbooks', 'textbook_chunks.textbook_id', '=', 'syllabus_textbooks.id')
                ->where('syllabus_textbooks.syllabus_id', $syllabusId)
                ->select(
                    'syllabus_textbooks.original_name',
                    'textbook_chunks.chunk_index',
                    'textbook_chunks.content'
                )
                ->orderBy('syllabus_textbooks.id')
                ->orderBy('textbook_chunks.chunk_index');

            // Apply limit only if specified
            if ($limit !== null) {
                $query->limit($limit);
            }

            $chunks = $query->get()->toArray();

            return array_map(function($chunk) {
                return [
                    'original_name' => $chunk->original_name,
                    'chunk_index' => $chunk->chunk_index,
                    'content' => $chunk->content,
                ];
            }, $chunks);

        } catch (\Exception $e) {
            Log::error('Failed to load textbook chunks', [
                'syllabus_id' => $syllabusId,
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }
}
