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
        $context = trim((string)$request->input('context', ''));
        $tlaOnly = false;
        // Reorder: bring TLA block to the very top to avoid model missing it
        try {
            if ($context !== '' && strpos($context, 'PARTIAL_BEGIN:tla') !== false) {
                if (preg_match('/PARTIAL_BEGIN:tla[\s\S]*?PARTIAL_END:tla/', $context, $m)) {
                    $tlaBlock = $m[0];
                    $rest = trim(preg_replace('/PARTIAL_BEGIN:tla[\s\S]*?PARTIAL_END:tla/', '', $context)) ?: '';
                    // Force TLA-only mode: drop all other snapshot blocks entirely
                    $context = $tlaBlock;
                    $tlaOnly = true;
                }
            }
        } catch (\Throwable $e) { /* ignore reorder errors */ }
        // Server-side diagnostics: confirm context receipt and presence of key blocks
        try {
            $len = mb_strlen($context);
            $hasTla = ($context !== '' && (strpos($context, 'PARTIAL_BEGIN:tla') !== false));
            // If TLA Activities block exists in snapshot, add a short structural guide for the model
            try {
                if ($context !== '' && strpos($context, 'PARTIAL_BEGIN:tla') !== false) {
                    // Extract optional columns line and count rows
                    $cols = null; $rowCount = 0;
                    if (preg_match('/COLUMNS:\s*(.+)/', $context, $m)) { $cols = trim($m[1]); }
                    if (preg_match_all('/^ROW:\d+\s\|/m', $context, $m)) { $rowCount = count($m[0]); }
                    $guide = "Use the on-page TLA Activities partial (PARTIAL_BEGIN:tla) only. "
                        ."It is structured with TLA_START/TLA_END and per-row lines (ROW and FIELDS_ROW). "
                        ."Columns: ".($cols ?: 'Ch. | Topics / Reading List | Wks. | Topic Outcomes | ILO | SO | Delivery Method').". "
                        ."Detected rows: ".$rowCount.". Do not use TLAS (Teaching & Learning Strategies) as a substitute.";
                    $messages[] = ['role' => 'system', 'content' => $guide];
                }
            } catch (\Throwable $e) { /* ignore TLA guide errors */ }
            $hasCriteria = ($context !== '' && (strpos($context, 'PARTIAL_BEGIN:criteria_assessment') !== false));
            $hasCourseInfo = ($context !== '' && (strpos($context, 'PARTIAL_BEGIN:course_info') !== false));
            $hasMv = ($context !== '' && (strpos($context, 'PARTIAL_BEGIN:mission_vision') !== false));
            $preview = '';
            if ($len > 0) {
                // Take first 400 chars and last 200 chars for quick preview
                $head = mb_substr($context, 0, min(400, $len));
                $tail = $len > 200 ? mb_substr($context, $len - 200) : '';
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
            // Prefer external teaching prompt as first system message when available
            if ($externalTeachingPrompt) {
                $messages[] = ['role' => 'system', 'content' => $externalTeachingPrompt];
            }
            // Always include internal guidance to ensure full coverage
            $messages[] = ['role' => 'system', 'content' => $systemPrompt];
            // Fixed institution mission & vision block
            if (!$tlaOnly) {
                try {
                    $instVision = config('institution.vision');
                    $instMission = config('institution.mission');
                    if ($instVision || $instMission) {
                        $core = [];
                        $core[] = 'INSTITUTION_CORE_BEGIN';
                        if ($instVision) { $core[] = 'Vision: ' . $instVision; }
                        if ($instMission) { $core[] = 'Mission: ' . $instMission; }
                        $core[] = 'INSTITUTION_CORE_END';
                        $messages[] = [ 'role' => 'system', 'content' => implode("\n", $core) ];
                    }
                } catch (\Throwable $e) { /* ignore */ }
            }
            if ($context !== '') {
                // If the user is asking about TLA and a TLA block exists, slim the context to ONLY the TLA block to avoid token limits
                // Already in TLA-only mode above if TLA was present; no further slimming needed
                // Trim context to a larger cap now that we rely on unified snapshot
                $maxContextChars = 18000; // safety cap
                if (mb_strlen($context) > $maxContextChars) {
                    $context = mb_substr($context, 0, $maxContextChars) . "\n[Context trimmed]";
                }
                // Post-trim diagnostics: verify critical blocks still present
                try {
                    $len2 = mb_strlen($context);
                    $hasTla2 = (strpos($context, 'PARTIAL_BEGIN:tla') !== false);
                    $hasCriteria2 = (strpos($context, 'PARTIAL_BEGIN:criteria_assessment') !== false);
                    $hasCourseInfo2 = (strpos($context, 'PARTIAL_BEGIN:course_info') !== false);
                    $hasMv2 = (strpos($context, 'PARTIAL_BEGIN:mission_vision') !== false);
                    $head2 = mb_substr($context, 0, min(300, $len2));
                    $tail2 = $len2 > 160 ? mb_substr($context, $len2 - 160) : '';
                    Log::info('AIChat post-trim context', [
                        'len' => $len2,
                        'hasTla' => $hasTla2,
                        'hasCriteria' => $hasCriteria2,
                        'hasCourseInfo' => $hasCourseInfo2,
                        'hasMv' => $hasMv2,
                        'preview' => $head2 . ($tail2 !== '' ? "\n…\n" . $tail2 : ''),
                    ]);
                } catch (\Throwable $e) { /* ignore diagnostics errors */ }
                // Summarize detected sections so the model can rely on a compact list
                // Omit snapshot sections summary in TLA-only mode
                // Full context payload (ensure high priority by placing as system message)
                $messages[] = ['role' => 'system', 'content' => "SYLLABUS_CONTEXT_BEGIN\n".$context."\nSYLLABUS_CONTEXT_END"]; // mark boundaries
            }
            // Strengthen edit/replace behavior when the user explicitly asks for it
            $umsg = mb_strtolower($userMessage);
            $wantsEdits = (strpos($umsg, 'replace') !== false) || (strpos($umsg, 'edit') !== false) || (strpos($umsg, 'rewrite') !== false) || (strpos($umsg, 'improve') !== false) || (strpos($umsg, 'suggest') !== false);
            if ($wantsEdits) {
                $messages[] = ['role' => 'system', 'content' => 'Prioritize concrete replacements. Populate the "Proposed Edits" table with 3–7 rows referencing actual section keys and field labels from context. Keep Suggested Text as the full replacement value. Keep Rationale to ≤12 words.'];
            }
            // Creation boost when user wants to create/draft a syllabus
            $wantsCreate = (strpos($umsg, 'create') !== false) || (strpos($umsg, 'draft') !== false) || (strpos($umsg, 'new syllabus') !== false) || (strpos($umsg, 'start syllabus') !== false) || (strpos($umsg, 'generate syllabus') !== false);
            if ($wantsCreate) {
                $messages[] = ['role' => 'system', 'content' => 'Provide a "Starter Pack": (1) Course overview (draft), (2) 3–6 ILOs as "ILO <n>: <description>", (3) initial Assessment Tasks with suggested weights totaling 100%, (4) brief Alignment notes (ILO ↔ tasks), and (5) a short Policies checklist. Use [TBD] for unknown specifics. Keep concise.'];
            }
            // Append compact DB context and program extras unless in TLA-only mode
            try {
                if (!$tlaOnly) {
                    $syllabus->loadMissing([
                        'course.department',
                        'course.prerequisites',
                        'course.ilos',
                        'program.department',
                        'textbooks',
                    ]);
                    // Omit DB context and program extras intentionally for snapshot reduction.
                    // If needed later, re-enable with appropriate caps.
                }
            } catch (\Throwable $e) {
                // best-effort, ignore DB/program context errors
            }
            // Schema injection on generation requests
            $u = mb_strtolower($userMessage);
            $wantsIlo = (strpos($u, 'generate') !== false && (strpos($u, 'ilo') !== false || strpos($u, 'learning outcome') !== false));
            $wantsIga = (strpos($u, 'generate') !== false && strpos($u, 'iga') !== false);
            $wantsSo  = (strpos($u, 'generate') !== false && (strpos($u, 'so') !== false || strpos($u, 'student outcome') !== false));
            $wantsCdio= (strpos($u, 'generate') !== false && strpos($u, 'cdio') !== false);
            $wantsSdg = (strpos($u, 'generate') !== false && strpos($u, 'sdg') !== false);
            if ($wantsIlo || $wantsIga || $wantsSo || $wantsCdio || $wantsSdg) {
                $schema = [];
                if ($wantsIlo) $schema[] = 'For ILOs: output each as "ILO <number>: <description>". Prefer a numbered list; if more than three, a table with columns: ILO | Description.';
                if ($wantsIga) $schema[] = 'For IGA: output each entry with "Title" and "Description". Prefer bullets; if more than three, use a table: Title | Description.';
                if ($wantsSo)  $schema[] = 'For SO: output each entry with "Title" and "Description". Prefer bullets; if more than three, use a table: Title | Description.';
                if ($wantsCdio)$schema[] = 'For CDIO: output each entry with "Title" and "Description". Prefer bullets; if more than three, use a table: Title | Description.';
                if ($wantsSdg) $schema[] = 'For SDG: output each entry with "Title" and "Description". Prefer bullets; if more than three, use a table: Title | Description.';
                $messages[] = ['role' => 'system', 'content' => implode(' ', $schema)];
            }
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
            $messages[] = ['role' => 'user', 'content' => $userMessage];

            // If the user asks to read/analyze TLA and a TLA block exists, enforce compact table output
            try {
                $u2 = mb_strtolower($userMessage);
                $mentionsTla = (strpos($u2, 'tla') !== false) || (strpos($u2, 'teaching, learning, and assessment') !== false);
                $hasTlaBlock = ($context !== '' && (strpos($context, 'PARTIAL_BEGIN:tla') !== false));
                if ($mentionsTla && $hasTlaBlock) {
                    $messages[] = ['role' => 'system', 'content' => 'When reading TLA Activities, output a compact Markdown table with columns: Ch. | Topics / Reading List | Wks. | Topic Outcomes | ILO | SO | Delivery Method. Use only the rows in PARTIAL_BEGIN:tla (TLA_START/TLA_END). Avoid TLAS. Keep rows concise.'];
                }
            } catch (\Throwable $e) { /* ignore */ }

            $maxTokens = $wantsCreate ? 600 : 300;
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

            $response = Http::withHeaders([
                'Authorization' => 'Bearer '.$apiKey,
                'Content-Type' => 'application/json',
            ])->post('https://api.openai.com/v1/chat/completions', $payload);

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
            Log::error('OpenAI chat exception', ['message' => $e->getMessage()]);
            return response()->json(['error' => 'Unexpected AI error'], 500);
        }
    }
}
