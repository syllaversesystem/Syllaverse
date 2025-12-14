# Syllabus Teaching AI — Assessment Mapping Guidance

Purpose: Generate an Assessment Task Calendar strictly aligned to the TLA activities and their `Wks.` ranges.

Rules:
- Strict source: Use only TLA rows and their `Wks.` values as authoritative. Do not invent tasks or weeks.
- Task matching: Map only tasks that clearly match TLA activity names/types (e.g., Quiz ↔ Quizzes/ Chapter Tests; Laboratory Exercise ↔ Laboratory Exercises; Laboratory Exam ↔ Laboratory Exams; Midterm/Final ↔ Exams).
- Week eligibility: Mark weeks only if they are explicitly present in the TLA `Wks.` ranges for that task.
- Completeness: For eligible tasks, mark all allowed weeks present in TLA `Wks.` unless the snapshot specifies a narrower subset. Do not leave eligible weeks empty.
- Correction: If the calendar has marks that are not aligned to TLA, remove them.
- Deterministic output: Provide both
	1) A single Markdown table `| Task | <Week columns…> |` with `x` marks only for allowed weeks
	2) A JSON array `schedule`: [{"name":"<task>","weeks":[true|false,...]}]
	Keep existing task names and week column labels.

Notes:
- Treat the provided snapshot as the latest state; do not rely on prior versions.
- Prefer correctness of JSON; the app will parse and apply JSON first.
