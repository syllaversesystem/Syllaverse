<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;
use App\Models\Syllabus;
use App\Services\TextbookChunkService;
use App\Models\SyllabusSo;
use App\Models\StudentOutcome;
use App\Models\SyllabusCdio;
use App\Models\Cdio;
use App\Models\SyllabusSdg;
use App\Models\Sdg;

class AIChatController extends Controller
{
    public function chat(Request $request, Syllabus $syllabus)
    {
        $userMessage = trim((string)$request->input('message', ''));
        // Gather contexts from multiple fields
        $context = trim((string)$request->input('context', ''));
        $contextAll = trim((string)$request->input('context_all', ''));
        $contextPhase1 = trim((string)$request->input('context_phase1', ''));
        $contextPhase2 = trim((string)$request->input('context_phase2', ''));
        $contextPhase3 = trim((string)$request->input('context_phase3', ''));
        $phaseReq = (string)$request->input('phase', '');
        $tlaOnly = false;
        // Build a combined context (prefer context_all, else concatenate phases, else single context)
        $combinedContext = '';
        if ($contextAll !== '') {
            $combinedContext = $contextAll;
        } elseif ($contextPhase1 !== '' || $contextPhase2 !== '' || $contextPhase3 !== '') {
            $parts = [];
            if ($contextPhase1 !== '') $parts[] = "--- Phase 1 ---\n".$contextPhase1;
            if ($contextPhase2 !== '') $parts[] = "--- Phase 2 ---\n".$contextPhase2;
            if ($contextPhase3 !== '') $parts[] = "--- Phase 3 ---\n".$contextPhase3;
            $combinedContext = implode("\n\n", $parts);
        } else {
            $combinedContext = $context; // legacy single field
        }
        // Do not reorder or elevate TLA on the backend; keep snapshot order as provided by the frontend
        // Server-side diagnostics: confirm context receipt and presence of key blocks
        try {
            $len = mb_strlen($combinedContext);
            $hasTla = ($combinedContext !== '' && (strpos($combinedContext, 'PARTIAL_BEGIN:tla') !== false));
            // Do not append any TLA-specific structural guide on the backend
            $hasCriteria = ($combinedContext !== '' && (strpos($combinedContext, 'PARTIAL_BEGIN:criteria_assessment') !== false));
            $hasCourseInfo = ($combinedContext !== '' && (strpos($combinedContext, 'PARTIAL_BEGIN:course_info') !== false));
            $hasMv = ($combinedContext !== '' && (strpos($combinedContext, 'PARTIAL_BEGIN:mission_vision') !== false));
            $preview = '';
            if ($len > 0) {
                // Take first 400 chars and last 200 chars for quick preview
                $head = mb_substr($combinedContext, 0, min(400, $len));
                $tail = $len > 200 ? mb_substr($combinedContext, $len - 200) : '';
                $preview = $head . ($tail !== '' ? "\n…\n" . $tail : '');
            }
            Log::info('AIChat incoming context', [
                'len' => $len,
                'hasTla' => $hasTla,
                'hasCriteria' => $hasCriteria,
                'hasCourseInfo' => $hasCourseInfo,
                'hasMv' => $hasMv,
                'preview' => $preview,
            ]);
        } catch (\Throwable $e) { /* ignore diagnostics errors */ }
        if ($userMessage === '') {
            return response()->json(['error' => 'Empty message'], 422);
        }
        // Basic length constraint
        if (mb_strlen($userMessage) > 4000) {
            return response()->json(['error' => 'Message too long'], 422);
        }

        $apiKey = env('OPENAI_API_KEY');
        $model = env('OPENAI_MODEL', 'gpt-4.1');
        if (!$apiKey) {
            return response()->json(['error' => 'AI service not configured'], 500);
        }

        // Load teaching prompt if present; fallback to built-in comprehensive system prompt
        $teachingPromptPath = resource_path('ai/prompts/syllabus-teaching-prompt.md');
        $externalTeachingPrompt = null;
        try {
            if (File::exists($teachingPromptPath)) {
                // Cap size to avoid excessive token usage
                $externalTeachingPrompt = File::get($teachingPromptPath);
                $maxTeach = 16000;
                if (mb_strlen($externalTeachingPrompt) > $maxTeach) {
                    $externalTeachingPrompt = mb_substr($externalTeachingPrompt, 0, $maxTeach) . "\n[Teaching prompt trimmed]";
                }
            }
        } catch (\Throwable $e) {
            // Non-fatal if prompt cannot be read
        }

        $systemPrompt = <<<'PROMPT'
    You are SyllaverseAI, the official AI engine for Syllaverse, a syllabus creation and academic alignment system used at Batangas State University ARASOF–Nasugbu.

    Primary objectives
    - Create and refine complete, academically correct, logically aligned, quality‑assured syllabi.
    - Follow BatStateU institutional formatting, CHED requirements, program outcomes, departmental standards, academic best practices, and proper mapping (ILO → SO → IGA → CDIO → SDG).
    - Validate inputs, detect missing requirements, correct errors, improve weak content, suggest best practices, ensure logical alignment, and prevent inaccurate/irrelevant outputs.

    Context handling
    - You will receive a unified syllabus snapshot (and optional DB context). If you see "[Snapshot truncated]" or "[Context trimmed]" treat missing details cautiously and ask 2–4 targeted questions. Never fabricate.

    Dynamic outcomes (no fixed templates)
    - ILO, SO, IGA, CDIO, and SDG are not fixed templates. Generate the best‑fit, most appropriate, academically aligned outcomes based on the course, program, department, discipline, academic level, and course nature. Any PDF or example is illustrative only.
    - Dynamically create custom ILOs unique to the course; choose correct SOs per program; select appropriate IGAs matching objectives; determine relevant CDIO skills per course complexity; choose relevant SDG skills per topic relevance.

    Syllabus sections you must support
    A. Vision & Mission (use official BatStateU statements unless alternatives provided)
    B. Course Information: Title, Code, Program, Department, Category, Semester & Year, Credit hours (lec/lab), Prerequisites, Instructor/contact ([TBD] if unknown), CMO refs, Campus, Revision date/number.
    C. Course Rationale & Description: professional tone; importance, scope, program linkage, industry relevance, academic purpose.
    D. Contact Hours: lecture, laboratory, total. Reject contradictions.
    E. Criteria for Assessment: accept any instructor structure but totals must equal 100%. Validate, detect errors, suggest corrections, reformat professionally.
    F. Teaching, Learning, and Assessment Strategies: adapt to course nature (e.g., IT/CS labs/simulations; Analytics datasets/visualization; GE discussions/essays; Engineering computations/design). Align with competencies and outcomes.
    G. Intended Learning Outcomes (ILO): 3–6 measurable outcomes using Bloom’s Taxonomy; appropriate to level; unique to the course; aligned with description. Action verbs; observable; cover cognitive/psychomotor/affective where relevant; align with assessments.
    H. Assessment Method & Distribution Map: table mapping Assessment Method, Task, ILO coverage, SO coverage, CPA domain, Weight %. Must align with generated ILOs; correct mismatches.
    I. Institutional Graduate Attributes (IGA): select only relevant from BSU IGAs (IGA1–IGA8); generate ILO–IGA mapping.
    J. Student Outcomes (SO): must match the program; identify program, select correct SOs, align to ILOs; generate ILO–SO mapping.
    K. CDIO Skills: select relevant skills (CDIO1–CDIO4); generate ILO–CDIO mapping.
    L. Sustainable Development Goals (SDG): select relevant skills; generate ILO–SDG mapping.
    M. Course Policies: Attendance, Dropping, Missed Exams, Academic Integrity, Accessibility, Consultation, Submission Rules, Class Discipline; professional, consistent tone.
    N. TLA Activities (Weekly Schedule): per‑week Topics, Topic Outcomes, Delivery Mode, Teaching/Learning/Assessment Activities; logical progression.
    O. Assessment Method (Descriptions): concise academic descriptions (Midterm, Final, Quizzes, Assignments, Labs, Projects, etc.), grading scale, remarks.
    P. Mapping Tables: ILO–SO, ILO–CPA, ILO–IGA, ILO–CDIO, ILO–SDG (all must match dynamically generated ILOs).

    Dynamic validation engine
    - Before output: analyze inputs, detect missing/incorrect content, evaluate correctness, catch nonsense, detect mismatches, verify 100% assessment totals, check alignment.
    - If missing: say "Before generating this section, please provide: …" and offer a suggested draft.
    - If incorrect: explain briefly and suggest a corrected version.
    - If totals ≠ 100%: provide a corrected distribution summing to 100%.
    - If ILO verbs are weak/non‑measurable: propose improved ILOs with measurable verbs.
    - If mappings contradict the course: provide a corrected aligned mapping.

    Dynamic suggestion engine
    - Based on program, department, level, course nature, outcomes, and contact hours, suggest: best‑fitting ILOs; suited SOs; relevant IGAs; relevant CDIO/SDG skills; topic list; teaching strategies; assessment categories. Examples include analytics (exploration/visualization/ethics), programming (syntax/algorithms/debugging), GE (discussions/essays/reflection).

    Workflow you must follow
    1) Validate inputs
    2) Inform about missing pieces
    3) Suggest corrected/improved academic content
    4) Wait for confirmation before major rewriting
    5) Generate complete, aligned, high‑quality output

    Output rules and format
    - Use clear, concise Markdown: headings for sections, bullets for actions, compact tables for lists/mappings. Prefer tables when ≥4 items. When creating with unknowns, propose best‑practice drafts with [TBD] placeholders and ask 2–4 clarifying questions.
    - Always maintain academic accuracy, measurable outcomes, logical alignment, institution consistency, and completeness. Never fabricate data or exceed 100% assessments.

    Special formatting aids
    - For ILOs: present as “ILO <n>: <description>” when listing. For IGA/SO/CDIO/SDG selections: include a brief Title and Description when helpful, but do not treat these as fixed templates—the content must be dynamically appropriate.
    - Include a final section “Proposed Edits” with a compact table: | Section | Field/Label | Suggested Text | Rationale | to enable quick copy‑paste replacements when applicable.
    
        Snapshot Structure Guidance
        - The syllabus snapshot is structurally tagged for machine parsing.
        - Each section (partial) appears as a block:
            PARTIAL_BEGIN:<key>
            HEADINGS:Heading1 | Heading2 | ... (optional)
            FIELDS_START (optional)
            <FieldLabel> = <Value>
            ... (one per line)
            FIELDS_END (optional)
            TEXT_START (optional)
            <Raw consolidated textual content (may be truncated with …)>
            TEXT_END (optional)
            PARTIAL_END:<key>
        - Never assume absent fields; if critical academic data (e.g., contact hours, assessment weights, ILOs) is missing ask 2–4 clarifying questions before large-scale generation.
        - When proposing replacements reference the <key> and the exact FieldLabel where available.
        - If assessment weights are embedded only in TEXT without field lines, parse carefully and re-present normalized totals.
        - Treat HEADINGS tokens as high-level semantic anchors (e.g., use to detect sections like Course Information, Policies, Criteria for Assessment).
        - If [Snapshot truncated] or [Context trimmed] markers appear, explicitly state limitations and ask for the missing slice needed for high‑risk decisions (ILO finalization, mapping tables, assessment validation).

        Assessment Method & Distribution Map Structure
        - This partial is a structured table capturing assessment tasks and their outcome alignment.
        - Columns typically include: Code | Assessment Task | I/R/D | (%) | ILO 1..n | Domains C | P | A.
        - Semantics:
            • Code: short identifier for the task (e.g., ME, FE, QCT, ARR, PR, LE, LEX). Use this to reference tasks succinctly.
            • I/R/D: type of learning exposure — Introduce (I), Reinforce (R), Demonstrate (D).
            • (%) : grading weight percentage for the task (ensure totals across tasks equal 100%).
            • ILO 1..n: item counts per ILO for that task (e.g., ME has 35 items aligned to ILO1 and 35 to ILO2).
            • Domains C/P/A: total item counts by domain — Cognitive (C), Psychomotor (P), Affective (A). P indicates hands‑on/practical.
        - Validation rules:
            • The sum of ILO item counts for a task must equal the total items indicated in Domains (C+P+A).
            • The distribution of (%) must sum to 100% across all tasks (lecture+laboratory totals must also reconcile with overall criteria if present).
            • ILO alignments must correspond to existing or proposed ILOs; flag mismatches and suggest corrections.
            • If any column is missing (e.g., some ILO cells empty) provide a concise, best‑practice distribution draft based on course nature and contact hours.
        - Output expectations:
            • When asked to analyze or generate this map, present a compact Markdown table with the columns above.
            • Show per‑task checks: item count equality, domain totals, and ILO coverage rationales.
            • If corrections are needed, provide a “Corrected Map” table and include precise rows in Proposed Edits referencing Code and column names.

        Textbook References (Optional Block)
        - You may receive a block delimited by TEXTBOOKS_BEGIN ... TEXTBOOKS_END.
            Each entry may include:
                TEXTBOOK: <filename or title>
                EXCERPT_BEGIN
                <plain text excerpt>
                EXCERPT_END
        - Use excerpts to ground weekly topics, reading lists, and terminology. Do not invent content that contradicts excerpts. If only titles are provided (no excerpt), use them as references but ask for chapter/page context before citing specifics.

        Alignment to Textbooks & TLA
        - For each assessment task, align its content to specific textbook chapters/sections visible in the excerpts.
        - When sufficient evidence exists, include a compact Sources note per task (e.g., “<Textbook Title>, Ch. 3–4”). If uncertain, ask 2–3 targeted questions (chapters/pages, scope, week coverage) before finalizing.
        - Cross‑check against the TLA/weekly schedule when available: assessments should only cover topics introduced prior to the assessment week. If mismatch detected, suggest a corrected schedule or narrowed task scope.
    PROMPT;

        try {
            $messages = [];
            // 1) Attach system guidance: external teaching prompt (if any) + core system prompt
            try {
                $sys = '';
                if ($externalTeachingPrompt) {
                    $sys .= "TEACHING_PROMPT_BEGIN\n".$externalTeachingPrompt."\nTEACHING_PROMPT_END\n\n";
                }
                $sys .= $systemPrompt;
                $messages[] = ['role' => 'system', 'content' => $sys];
            } catch (\Throwable $e) { /* ignore system prompt assembly */ }
            // Include context when provided (phased snapshots)
            if ($contextPhase1 !== '') {
                $messages[] = ['role' => 'system', 'content' => "SYLLABUS_CONTEXT_PHASE1_BEGIN\n".$contextPhase1."\nSYLLABUS_CONTEXT_PHASE1_END"]; 
            }
            if ($contextPhase2 !== '') {
                $messages[] = ['role' => 'system', 'content' => "SYLLABUS_CONTEXT_PHASE2_BEGIN\n".$contextPhase2."\nSYLLABUS_CONTEXT_PHASE2_END"]; 
            }
            if ($contextPhase3 !== '') {
                $messages[] = ['role' => 'system', 'content' => "SYLLABUS_CONTEXT_PHASE3_BEGIN\n".$contextPhase3."\nSYLLABUS_CONTEXT_PHASE3_END"]; 
            }
            // Backward compatibility: context_all or single context field
            if ($contextAll !== '') {
                $messages[] = ['role' => 'system', 'content' => "SYLLABUS_CONTEXT_ALL_BEGIN\n".$contextAll."\nSYLLABUS_CONTEXT_ALL_END"]; 
            } elseif ($combinedContext !== '') {
                $messages[] = ['role' => 'system', 'content' => "SYLLABUS_CONTEXT_BEGIN\n".$combinedContext."\nSYLLABUS_CONTEXT_END"]; 
            }
            // Do not append TLA structural hints here; frontend snapshot suffices
            // Do not add edit/replace or creation boosters
            // Append compact DB context and program extras unless in TLA-only mode
            // Do not append DB context or program extras
            // Schema injection on generation requests
            $u = mb_strtolower($userMessage);
            $wantsIlo = (strpos($u, 'generate') !== false && (strpos($u, 'ilo') !== false || strpos($u, 'learning outcome') !== false));
            $wantsIga = (strpos($u, 'generate') !== false && strpos($u, 'iga') !== false);
            $wantsSo  = (strpos($u, 'generate') !== false && (strpos($u, 'so') !== false || strpos($u, 'student outcome') !== false));
            $wantsCdio= (strpos($u, 'generate') !== false && strpos($u, 'cdio') !== false);
            $wantsSdg = (strpos($u, 'generate') !== false && strpos($u, 'sdg') !== false);
            // Do not inject schema guidance
            // Optional prior conversation history
            $historyRaw = (string)$request->input('history', '');
            if ($historyRaw !== '') {
                try {
                    $arr = json_decode($historyRaw, true);
                    if (is_array($arr)) {
                        $maxMsgs = 10;
                        $maxPer = 2000;
                        $maxTotal = 12000;
                        $acc = 0;
                        // take last messages if array longer than max
                        if (count($arr) > $maxMsgs) {
                            $arr = array_slice($arr, count($arr) - $maxMsgs);
                        }
                        foreach ($arr as $m) {
                            if (!is_array($m)) continue;
                            $role = strtolower((string)($m['role'] ?? ''));
                            $content = (string)($m['content'] ?? '');
                            if ($role !== 'user' && $role !== 'assistant') continue;
                            if ($content === '') continue;
                            if ($acc >= $maxTotal) break;
                            if (mb_strlen($content) > $maxPer) {
                                $content = mb_substr($content, 0, $maxPer);
                            }
                            $messages[] = ['role' => $role, 'content' => $content];
                            $acc += mb_strlen($content);
                        }
                    }
                } catch (\Throwable $e) {
                    // ignore bad history payloads
                }
            }
            // Attach a general TEXTBOOKS excerpts block to ground responses
            try {
                $tbLines = [];
                $tbLines[] = 'TEXTBOOKS_BEGIN';
                // List all textbook/reference titles
                try {
                    $textbooks = method_exists($syllabus, 'textbooks')
                        ? $syllabus->textbooks()->orderBy('type')->get()
                        : DB::table('syllabus_textbooks')->where('syllabus_id', $syllabus->id)->orderBy('type')->get();
                    foreach ($textbooks as $tb) {
                        $title = (string)($tb->original_name ?? $tb->title ?? '');
                        $type  = (string)($tb->type ?? 'main');
                        $isRef = (bool)($tb->is_reference ?? (empty($tb->file_path)));
                        $kind  = $isRef ? 'reference' : 'file';
                        $tbLines[] = 'TEXTBOOK: ['.$type.'] '.$title.' ('.$kind.')';
                    }
                } catch (\Throwable $e) { /* ignore list errors */ }

                // Add compact excerpts from textbook_chunks if available (general, not chapter-specific)
                try {
                    $MAX_ITEMS = 8;    // number of chunk excerpts
                    $MAX_LEN   = 1000; // per excerpt length cap
                    $chunks = DB::table('textbook_chunks')
                        ->select(['textbook_chunks.*'])
                        ->join('syllabus_textbooks', function($join){
                            $join->on('syllabus_textbooks.id', '=', 'textbook_chunks.textbook_id');
                        })
                        ->where('syllabus_textbooks.syllabus_id', $syllabus->id)
                        ->orderBy('textbook_chunks.textbook_id')
                        ->orderBy('textbook_chunks.chunk_index')
                        ->limit($MAX_ITEMS)
                        ->get();
                    $added = 0;
                    foreach ($chunks as $ch) {
                        if ($added >= $MAX_ITEMS) break;
                        $bookTitle = '';
                        try {
                            $tb = DB::table('syllabus_textbooks')->where('id', $ch->textbook_id)->first();
                            if ($tb) $bookTitle = (string)($tb->original_name ?? '');
                        } catch (\Throwable $e) {}
                        $excerpt = (string)($ch->content ?? $ch->chunk_text ?? '');
                        if ($excerpt === '') continue;
                        if (mb_strlen($excerpt) > $MAX_LEN) {
                            $excerpt = mb_substr($excerpt, 0, $MAX_LEN)."\n[Excerpt trimmed]";
                        }
                        $tbLines[] = 'EXCERPT_BEGIN';
                        if ($bookTitle !== '') $tbLines[] = 'BOOK: '.$bookTitle;
                        $tbLines[] = $excerpt;
                        $tbLines[] = 'EXCERPT_END';
                        $added++;
                    }
                    if ($added === 0) {
                        $tbLines[] = 'NOTE: Uploaded textbooks present but no excerpts available yet.';
                    }
                } catch (\Throwable $e) {
                    $tbLines[] = 'NOTE: Error retrieving textbook excerpts.';
                }
                $tbLines[] = 'TEXTBOOKS_END';
                if (count($tbLines) > 2) {
                    $messages[] = ['role' => 'system', 'content' => implode("\n", $tbLines)];
                }
            } catch (\Throwable $e) { /* ignore textbook block failures */ }

            $messages[] = ['role' => 'user', 'content' => $userMessage];

            // Do not add TLA-specific output guidance

            // Attach Textbooks/References and optional chunk excerpts (Phase 2)
            try {
                $phaseStr = (string)$request->input('phase', '');
                $isPhase2 = ($phaseStr === '2');
                if ($isPhase2) {
                    $lowerMsg = mb_strtolower($userMessage);
                    $mentionsChapter1 = (preg_match('/chapter\s*1|\bintroduction\b/i', $userMessage) === 1);
                    $tbLines = [];
                    $tbLines[] = 'TEXTBOOKS_BEGIN';
                    // List textbook and reference titles
                    try {
                        if (method_exists($syllabus, 'textbooks')) {
                            $textbooks = $syllabus->textbooks()->orderBy('type')->get();
                        } else {
                            // Use Syllaverse table name: syllabus_textbooks
                            $textbooks = DB::table('syllabus_textbooks')
                                ->where('syllabus_id', $syllabus->id)
                                ->orderBy('type')->get();
                        }
                        foreach ($textbooks as $tb) {
                            $title = (string)($tb->original_name ?? $tb->title ?? '');
                            $type  = (string)($tb->type ?? 'main');
                            $isRef = (bool)($tb->is_reference ?? (empty($tb->file_path)));
                            $kind  = $isRef ? 'reference' : 'file';
                            $tbLines[] = 'TEXTBOOK: ['.$type.'] '.$title.' ('.$kind.')';
                        }
                    } catch (\Throwable $e) {
                        // Non-fatal listing error
                    }
                    // Attach limited chunk excerpts if available and relevant
                    try {
                        $maxChunks = 5; // keep excerpts compact
                        $wantChunks = $mentionsChapter1 || (strpos($lowerMsg, 'chapter') !== false);
                        if ($wantChunks) {
                            // Use actual Syllaverse table: textbook_chunks, filter by Chapter 1 patterns in content
                            $query = DB::table('textbook_chunks')
                                ->select(['textbook_chunks.*'])
                                ->join('syllabus_textbooks', function($join){
                                    $join->on('syllabus_textbooks.id', '=', 'textbook_chunks.textbook_id');
                                })
                                ->where('syllabus_textbooks.syllabus_id', $syllabus->id)
                                ->orderBy('textbook_chunks.chunk_index');
                            // Chapter 1 heuristics: lines like "1 Introduction", "Chapter 1", "1.1" etc.
                            $query = $query->where(function($q){
                                $q->where('textbook_chunks.content', 'like', '%Chapter 1%')
                                  ->orWhere('textbook_chunks.content', 'like', '%\n1 Introduction%')
                                  ->orWhere('textbook_chunks.content', 'like', '%1.1 What is%')
                                  ->orWhere('textbook_chunks.content', 'like', '%1.2 %')
                                  ->orWhere('textbook_chunks.content', 'like', '%1.3 %');
                            });
                            $chunks = $query->limit($maxChunks)->get();
                            $added = 0;
                            foreach ($chunks as $ch) {
                                if ($added >= $maxChunks) break;
                                $bookTitle = '';
                                try {
                                    $tb = DB::table('syllabus_textbooks')->where('id', $ch->textbook_id)->first();
                                    if ($tb) $bookTitle = (string)($tb->original_name ?? '');
                                } catch (\Throwable $e) {}
                                $chapter   = '1';
                                $section   = '';
                                $excerpt   = (string)($ch->content ?? '');
                                if ($excerpt === '') continue;
                                $maxLen = 1600; // ~1.6k chars per chunk
                                if (mb_strlen($excerpt) > $maxLen) {
                                    $excerpt = mb_substr($excerpt, 0, $maxLen).'\n[Excerpt trimmed]';
                                }
                                $tbLines[] = 'EXCERPT_BEGIN';
                                $meta = [];
                                if ($bookTitle !== '') $meta[] = 'BOOK: '.$bookTitle;
                                $meta[] = 'CHAPTER: '.$chapter;
                                if ($section !== '') $meta[] = 'SECTION: '.$section;
                                if (!empty($meta)) $tbLines[] = implode(' | ', $meta);
                                $tbLines[] = $excerpt;
                                $tbLines[] = 'EXCERPT_END';
                                $added++;
                            }
                            if ($added === 0) {
                                $tbLines[] = 'NOTE: No matching textbook excerpts found for Chapter 1 — Introduction.';
                            }
                        } else {
                            // Hint model that uploaded files are chunked server-side
                            $tbLines[] = 'NOTE: Uploaded textbook files are pre-processed into chunks server-side; references are plain text entries.';
                        }
                    } catch (\Throwable $e) {
                        $tbLines[] = 'NOTE: Error retrieving textbook excerpts — proceeding without chunk content.';
                    }
                    $tbLines[] = 'TEXTBOOKS_END';
                    $messages[] = ['role' => 'system', 'content' => implode("\n", $tbLines)];
                }
            } catch (\Throwable $e) {
                // ignore textbook block errors
            }

            // Allow overriding max tokens via env; default to a practical length for structured outputs
            $maxTokens = (int)env('OPENAI_MAX_TOKENS', 900);
            $payload = [
                'model' => $model,
                'messages' => $messages,
                'temperature' => 0.7,
                'max_tokens' => $maxTokens,
            ];

            // Final payload summary (no sensitive content): count and section keys
            try {
                $secKeys = [];
                foreach ($messages as $msg) {
                    $c = (string)($msg['content'] ?? '');
                    if ($c === '') continue;
                    if (preg_match_all('/PARTIAL_BEGIN:([^\s\n\r]+)/', $c, $mm)) {
                        foreach ($mm[1] as $k) { $secKeys[] = trim($k); }
                    }
                }
                $secKeys = array_unique($secKeys);
                Log::info('AIChat payload summary', [
                    'messages' => count($messages),
                    'sectionKeys' => $secKeys,
                    'model' => $model,
                ]);
            } catch (\Throwable $e) { /* ignore */ }

            // Support custom API base (e.g., proxy); default to official OpenAI
            $apiBase = rtrim((string)env('OPENAI_API_BASE', 'https://api.openai.com/v1'), '/');
            $response = Http::withHeaders([
                'Authorization' => 'Bearer '.$apiKey,
                'Content-Type' => 'application/json',
            ])->post($apiBase.'/chat/completions', $payload);

            if (!$response->ok()) {
                Log::warning('OpenAI chat error', ['status' => $response->status(), 'body' => $response->body()]);
                return response()->json(['error' => 'AI request failed'], 502);
            }

            $data = $response->json();
            $reply = $data['choices'][0]['message']['content'] ?? 'No reply received.';

            return response()->json([
                'reply' => $reply,
            ]);
        } catch (\Throwable $e) {
            Log::error('OpenAI chat exception', [
                'message' => $e->getMessage(),
                'trace' => app()->hasDebugModeEnabled() ? $e->getTraceAsString() : null,
            ]);
            $errMsg = 'Unexpected AI error';
            // In non-production, surface a concise hint to aid debugging
            try {
                if (!app()->environment('production')) {
                    $errMsg .= ': ' . $e->getMessage();
                }
            } catch (\Throwable $ee) { /* ignore env check errors */ }
            return response()->json(['error' => $errMsg], 500);
        }
    }
}
