/*
 * File: resources/js/faculty/ai/prompts.js
 * Description: Central place to manage prompt snippets per syllabus partial.
 * API:
 *   - SVPrompts.get(key): returns a prompt string for the partial key or default.
 *   - SVPrompts.set(key, prompt): override/add a prompt.
 *   - SVPrompts.list(): returns shallow copy of all prompts.
 *   - SVPrompts.defaultPrompt: base fallback text.
 */
(function(){
  'use strict';

  const defaultPrompt = [
    'You are an expert curriculum assistant. Use the supplied snapshot to give concise, actionable feedback.',
    'If the snapshot is empty or missing, say so and ask for the needed details.',
    'Keep responses brief and specific to the partial.',
  ].join(' ');

  // Prompt factory functions keyed by partial. Each receives optional snapshot
  // (shape: { key, markdown, raw }) and may tailor the prompt accordingly.
  const promptFns = {
    mission_vision: (snap) => 'Review mission and vision. Check clarity, alignment to institution, and distinctness between mission vs vision.',
    course_info: (snap) => [
      'Handle the course rationale and description based on the following scenarios:',
      '',
      'SCENARIO 1 - Existing course rationale & sufficient syllabus data:',
      'Check if the existing course rationale and description aligns with the entire syllabus (objectives, ILOs, SOs, teaching/learning strategies, assessment methods, etc.).',
      'If ALIGNED: Improve and enhance the existing description by making it more comprehensive, adding more relevant details, and strengthening its connection to the syllabus elements.',
      'If NOT ALIGNED: Rephrase and refocus the description to better align with the syllabus content and learning outcomes.',
      '',
      'SCENARIO 2 - Existing course rationale & insufficient syllabus data:',
      'If there is existing course rationale but the syllabus lacks sufficient detail to properly align it, simply rephrase and polish the existing description.',
      '',
      'SCENARIO 3 - No existing course rationale & sufficient syllabus data:',
      'Generate a new comprehensive, one-paragraph course rationale and description that aligns with the entire syllabus.',
      '',
      'GENERAL GUIDELINES:',
      'Structure the rationale to include: (1) What the course provides/offers to students and its relevance, (2) Key concepts, topics, or trends covered, (3) Learning methods and practical opportunities for students.',
      'Use a professional yet accessible tone suitable for a syllabus.',
      'Example format: "This course provides students with an overview of [key topic/trend] that drives [relevance]. The course will provide understanding on [main concepts] that can help [student benefit/organizational value]. This will also introduce [tools/methods] used in [field] to provide students with opportunities to apply these techniques in [practical settings]."',
      'Start your response with natural conversation (e.g., "Here\'s an improved/rephrased course description..." or similar), then provide the one-paragraph description.',
      'Ensure it is clear, detailed, cohesive with the syllabus, and directly applicable for inclusion.'
    ].join(' '),
    criteria_assessment: (snap) => [
      'Review the assessment criteria structure. Categories represent course components (e.g., lecture, lab, practical), and Assessments are evaluation methods (e.g., exams, exercises, projects) mapped to each category with percentages.',
      'Check that: (1) Assessment percentages within each category sum to 100%, (2) Overall category percentages sum to 100%, (3) The distribution reflects the course structure and workload.',
      'Suggest diverse assessment methods appropriate to each course component and aligned with the course learning outcomes.',
      'Ensure the weighting reflects the course design and academic requirements, balancing theory and practical work.',
      'Flag if percentages are missing, unbalanced, or if assessment methods need diversification.'
    ].join(' '),
    course_policies: (snap) => 'Summarize or improve policies (attendance, exams, dishonesty, dropping). Ensure tone is clear and student-facing.',
    tlas: (snap) => [
      'Generate comprehensive teaching, learning, and assessment strategies based on the entire syllabus.',
      '',
      'IMPORTANT DISTINCTION:',
      'This is the OVERALL STRATEGIC APPROACH (TLAS) describing how the course will be taught and assessed.',
      'This is NOT the weekly TLA Activities table. Do not confuse this with the detailed week-by-week schedule.',
      'Focus on describing the general teaching methods, assessment philosophy, and learning modalities used throughout the course.',
      '',
      'STRUCTURE YOUR RESPONSE:',
      '1. Start with a brief, natural conversational reply (1-2 sentences) acknowledging the request.',
      '2. Then provide the TLAS content in PARAGRAPH FORM (not bullet points).',
      '',
      'TLAS CONTENT SHOULD BE WRITTEN AS CONTINUOUS PARAGRAPHS:',
      'Paragraph 1 - Assessment Methods and Rubrics: Write flowing text describing the types of assessments used (e.g., written exams, oral exams, quizzes, assignments, presentations, case analyses, reflective journals, rubrics, portfolios, laboratory outputs). Explain how these assessments evaluate student learning and the role of rubrics in ensuring fairness.',
      '',
      'Paragraph 2 - Teaching and Learning Modalities: Write as a narrative describing the instructional approaches and modalities used throughout the course (e.g., hybrid learning, face-to-face and online sessions, video presentations, tutorials, laboratory activities, self-directed learning, web-based research, individual and group work, student-centered activities). Explain how these methods support diverse learning styles.',
      '',
      'Paragraph 3 - Assessment Instruments and Tools: Write in paragraph form specifying the evaluation methods and tools (e.g., quizzes, midterm exam, final exam, chapter tests, attendance tracking, rubric-based evaluations, oral/paper presentations, project evaluations, laboratory outputs). Describe how these instruments collectively measure course outcomes.',
      '',
      'EXAMPLE PARAGRAPH FORMAT:',
      '"Assessment Methods: Students will be evaluated using a combination of formative and summative assessments, including quizzes, written examinations, laboratory exercises, group projects, presentations, case analyses, and reflective journals. Rubrics will be used to ensure fairness and clarity in grading, especially for projects and presentations. This multifaceted approach ensures comprehensive evaluation of student learning across different domains.',
      '',
      'Teaching and Learning Methods: The course is taught using a structured program of hybrid learning (face-to-face and online), incorporating video presentations, tutorials, laboratory activities, and student-centered learning specifically through (a) self-directed learning using online materials and lectures, (b) laboratory sessions to gain practical experience and reinforce theory, (c) individual assignment work as part of laboratory activities, (d) web-based research, and (e) group-based problem solving and reporting. These diverse modalities accommodate different learning preferences and promote engagement.',
      '',
      'Assessment Tools and Instruments: Students will be assessed using a combination of rubrics, paper and pencil tests, oral and paper presentations, and portfolio methods. Specific instruments include Midterm and Final Exams, Quizzes/Chapter Tests, Attendance and Assignments, Evaluation of Laboratory Outputs using rubrics, and Projects. This comprehensive assessment battery ensures that learning outcomes are measured across cognitive, practical, and collaborative dimensions."',
      '',
      'IMPORTANT:',
      'Write as flowing, professional paragraphs - NOT as lists or bullet points.',
      'Ensure the strategies align with the course outcomes (ILOs and SOs) provided in the syllabus.',
      'Make it specific to this course\'s content, structure, and context.',
      'Use professional, clear language suitable for a syllabus.',
      'Keep the content practical and implementable.'
    ].join(' '),
    tla_activities: (snap) => [
      'Generate Teaching, Learning, and Assessment (TLA) Activities as a structured markdown table based on the syllabus snapshot.',
      '',
      'CRITICAL REQUIREMENT - TEXTBOOKS MUST BE PRESENT:',
      '- MAIN TOPICS MUST ALWAYS BE BASED ON UPLOADED TEXTBOOKS.',
      '- CHECK TEXTBOOK ALIGNMENT: Confirm uploaded textbooks/readings match the course description, ILOs, and assessment methods. If misaligned or irrelevant, DO NOT GENERATE the table; instead reply: "I cannot generate TLA Activities because the uploaded textbooks/readings are not aligned to the course. Please upload appropriate textbooks or a relevant reading list."',
      '- If the snapshot does NOT contain any textbooks, readings, or references, DO NOT generate the table.',
      '- Instead, respond with: "I cannot generate TLA Activities because no textbooks or readings have been uploaded to the syllabus. Please upload textbooks or provide a reading list first, and I will derive the main topics from those chapters/sections."',
      '- Only proceed to generate the table if textbooks/readings are present AND aligned to the course.',
      '',
      'CRITICAL REQUIREMENT - ILOs AND SOs MUST BE PRESENT:',
      '- If the snapshot lacks ILOs or SOs, DO NOT generate the TLA table.',
      '- Instead, respond with: "I cannot generate TLA Activities because the ILOs and/or SOs are missing. Please provide the ILOs and SOs first."',
      '- Only proceed when both ILOs and SOs are available in the snapshot.',
      '',
      'WHAT THIS IS (AND IS NOT):',
      '- This is the WEEKLY TLA ACTIVITIES table (18 weeks).',
      '- This is NOT the high-level TLAS narrative.',
      '',
      'OUTPUT RULES:',
      '1) Start with one short intro sentence (e.g., "Here are the TLA activities for your course:").',
      '2) Immediately output the markdown table. No extra commentary.',
      '3) Table columns must be exactly: | Ch. | Topics / Reading List | Wks. | Topic Outcomes | ILO | SO | Delivery Method |',
      '4) Use the separator row: | --- | --- | --- | --- | --- | --- | --- |',
      '',
      'WEEK CONSTRAINTS (TITLE CASE ONLY FOR EXAMS):',
      '- 18 rows total, covering Weeks 1–18.',
      '- Week 1: Orientation & Introduction; Ch., ILO, SO blank.',
      '- Week 9 (Midterm Examination): Topics/Reading List = "Midterm Examination", Wks. = "9"; ALL other cells blank including Delivery Method.',
      '- Week 17 (Final Examination): Topics/Reading List = "Final Examination", Wks. = "17"; ALL other cells blank including Delivery Method.',
      '- Week 18: Topics/Reading List = "Uploading and Submission of Grades", Wks. = "18"; ALL other cells blank.',
      '- Populate Weeks 2–8 and 10–16 with instructional topics/activities.',
      '',
      'COLUMN NOTES:',
      '- Ch.: Chapter number (sequential, e.g., 1, 2, 3); leave blank for Orientation/Exams/Week 18.',
      '- Topics / Reading List: Main topic derived from the chapter + aligned assessment tasks listed below (multi-line allowed).',
      '- Wks.: Week range assigned to that chapter (e.g., "2", "2-3", "3-4"). Duration depends on the scope and depth of the Topics/Reading List content for that chapter. Chapters typically span 2–3 weeks or run for 3 weeks straight based on content volume.',
      '- Topic Outcomes: Specific, measurable outcome (action verbs).',
      '- ILO: Comma-separated ILO numbers (blank for Orientation/Exams/Week 18).',
      '- SO: Comma-separated SO numbers (blank for Orientation/Exams/Week 18).',
      '- Delivery Method: e.g., Lecture, Lab, Discussion, Case Study, Group Work, Presentation, Written Exam, Practical Work, Online Module.',
      '',
      'CHAPTER AND TOPIC DERIVATION (FROM UPLOADED TEXTBOOKS):',
      '- Each row represents ONE CHAPTER from the textbook.',
      '- Extract Main Topics DIRECTLY from the chapters/sections/titles in the uploaded textbooks.',
      '- Use chapter numbers and titles as the basis for Main Topic naming.',
      '- Ensure Main Topics align with the sequence and structure of the textbooks.',
      '- Example: If a textbook has "Chapter 1: Introduction to Analytics", use "Main Topic 1: Introduction to Analytics" in that row.',
      '- Do not fabricate or invent topics; rely entirely on the textbook structure.',
      '- WEEK ASSIGNMENT PER CHAPTER: Assign a week range (Wks. column) based on how extensive the chapter content is. Chapters can span 2 weeks, 3 weeks, or run consecutively depending on the Topics/Reading List volume for that chapter.',
      '',
      'TASK INTEGRATION:',
      '- Prefix each instructional entry with "Main Topic X: <Title>".',
      '- In the Topics cell, put the Main Topic on the first line, add a BLANK LINE, then list aligned assessment tasks underneath with no dash prefix.',
      '- Use # for numbered tasks (Quiz #1, Assignment #2, Laboratory Activity #1). Non-numbered tasks stay plain text.',
      '- TASK SOURCE: Use ALL tasks from the Assessment Method & Distribution (AMD) partial and ONLY those tasks; do not invent or drop tasks.',
      '- TASK SPLITTING: If a task name in AMD contains a forward slash (e.g., "Assignment/Research Review"), treat it as TWO SEPARATE tasks. Split them and assign each a number (e.g., "Assignment #2" and "Research Review #1"). Place these tasks in DIFFERENT rows under different Main Topics based on your judgment for optimal course flow. Never combine slash-separated tasks in the same row.',
      '- PLACE TASKS: Use the AMD tasks snapshot to place each task under the closest matching Main Topic; keep pacing realistic. Use AI judgment to distribute tasks for best course flow and learning progression.',
      '- ALIGNMENT RULES:',
      '  * Topic-to-ILO/SO: Choose best-fit ILOs and SOs using AI judgment for the topic/outcome coverage.',
      '  * Task-to-ILO: Follow the AMD syllabus partial mappings; reflect those ILOs in the row that lists the task.',
      '  * Task-to-SO: Choose best-fit SOs using AI judgment.',
      '- Ensure ILO/SO columns reflect the combined coverage of the topic and the tasks listed in that row.',
      '- Choose week numbers/ranges that reflect workload when multiple tasks sit under a topic.',
      '- If a project presentation/submission exists, include it in Week 17 with the Final Examination cell content.',
      '',
      'DISTRIBUTION & COVERAGE:',
      '- Distribute quizzes/assessments every 2–3 topics; cover all ILOs/SOs across Weeks 2–16.',
      '- Keep logical progression and realistic pacing aligned to the textbook chapter sequence.',
      '',
      'FORMATTING ENFORCEMENT:',
      '- Use proper markdown table pipes; no extra text after the table.',
      '- Orientation/Exam/Grades rows: only Wks. cell populated (except Orientation has topic/outcomes/delivery).',
      '',
      'EXAMPLE OUTPUT:',
      'Here are the TLA activities for your course:',
      '',
      '| Ch. | Topics / Reading List | Wks. | Topic Outcomes | ILO | SO | Delivery Method |',
      '| --- | --- | --- | --- | --- | --- | --- |',
      '|  | Orientation & Introduction | 1 | VMGO Orientation, Presentation of Syllabus, Class Rules |  |  | Face-to-face Discussion |',
      '| 1 | Main Topic 1: Overview of Big Data and Analytics | 2-3 | Explain fundamental concepts of data analytics | 1,2 | 1 | Lecture, Videos |',
      '| 2 | Main Topic 2: Statistical Foundations | 4-5 | Apply statistical methods to analyze datasets | 2,3 | 1,2 | Lecture, Lab Work |',
      '|  | Midterm Examination | 9 |  |  |  |  |',
      '|  | Final Examination | 17 |  |  |  |  |',
      '|  |  | 18 |  |  |  |  |'
    ].join(' '),
    assessment_tasks: (snap) => [
      'Review the Assessment Method and Distribution Map. Output ONLY a markdown table (no cards, no extra paragraphs).',
      '',
      'TABLE COLUMN DEFINITIONS:',
      '- Code: Task code (e.g., ME, FE, PRJ)',
      '- Task: Task name (e.g., "Midterm Exam", "Project")',
      '- I/R/D: Type of task (I=Introduction, R=Reinforce, D=Deliverable)',
      '- %: Percentage weight of task in overall grade',
      '- ILO#: Distribution of items/points to each ILO (numbers add up to CPA totals)',
      '- C: Cognitive domain (total items/points for cognitive assessment)',
      '- P: Psychomotor domain (total items/points for psychomotor skills)',
      '- A: Affective domain (total items/points for attitude/appreciation)',
      '',
      'EXAMPLE ROW LOGIC:',
      'Midterm Exam (I type): C=75 means 75 cognitive items → distributed as ILO1=35, ILO2=35 (adds to 75)',
      'Project (D type): P=1000 means 1000 points for deliverable → distributed as ILO2=1000 (deliverable assigned only to ILO2)',
      'Values in ILO columns must sum to their corresponding CPA column (C+P+A totals across ILO columns equals the CPA total for that task).',
      '',
      'INSTRUCTIONS:',
      'If the snapshot is empty or missing tasks, ask the user for assessment task details: code, name, I/R/D type, weight %, and C/P/A totals with ILO distribution.',
      'Or propose a concise starter table (e.g., Midterm, Final, 1-2 assignments/projects) that the user can edit.',
      'Use exact ILO columns from snapshot (ILO1..ILOn); preserve existing codes and structure.',
      'Ensure weights (%) sum to 100%; keep distributions realistic and balanced.',
      'CATEGORY PROMPTING:',
      'When tasks include category labels (Laboratory, Lecture, Major Requirements, Minor Requirements, Additional Requirements), treat each as a category row and list their specific assessments directly below them in the table.',
      'Keep category rows as the parent task name; list the category-specific assessments as the tasks underneath with their own codes, I/R/D types, weights, and CPA/ILO distributions.'
    ].join(' '),
    assessment_schedule: (snap) => 'Check assessment schedule by week and distribution. Identify collisions, overload weeks, or missing early feedback.',
    ilo: (snap) => [
      'Generate a comprehensive list of Intended Learning Outcomes (ILOs) for this course based on the entire syllabus.',
      '',
      'STRUCTURE YOUR RESPONSE:',
      '1. Start with a brief, natural conversational reply (1-2 sentences) acknowledging the request.',
      '2. Then provide the ILOs in TABLE FORMAT (this will appear in a card and be inserted into the syllabus).',
      '',
      'TABLE FORMAT REQUIREMENTS:',
      'Create a markdown table with TWO columns:',
      '| ILO # | Intended Learning Outcome |',
      '| --- | --- |',
      '| ILO 1 | [Specific, measurable outcome statement] |',
      '| ILO 2 | [Specific, measurable outcome statement] |',
      '...',
      '',
      'ILO CONTENT GUIDELINES:',
      'Each ILO should be:',
      '- Specific and measurable (use action verbs like: understand, apply, analyze, create, evaluate, synthesize, etc.)',
      '- Aligned with the course topics, assessments, and learning activities provided in the syllabus',
      '- Written at an appropriate level for the course (e.g., introductory, intermediate, advanced)',
      '- Focused on what students will be able to DO or KNOW after completing the course',
      '- Clear, concise, and free of jargon',
      '',
      'EXAMPLE TABLE:',
      '| ILO # | Intended Learning Outcome |',
      '| --- | --- |',
      '| ILO 1 | Understand fundamental concepts and principles of business analytics and their application in organizational decision-making |',
      '| ILO 2 | Apply data analysis techniques using relevant tools and software to extract insights from real-world business data |',
      '| ILO 3 | Analyze business problems and develop data-driven solutions using analytical methodologies |',
      '| ILO 4 | Create visualizations and reports that effectively communicate analytical findings to diverse stakeholders |',
      '| ILO 5 | Evaluate the effectiveness of analytical solutions and recommend improvements based on business impact |',
      '',
      'IMPORTANT:',
      'Generate 4-8 ILOs depending on course depth and complexity.',
      'Ensure ILOs cover cognitive levels from basic understanding to higher-order thinking.',
      'Align ILOs with the course learning activities, assessments, and outcomes provided in the syllabus.',
      'Use consistent, professional language throughout.',
      'Do NOT include explanatory text after the table - only the table itself.'
    ].join(' '),
    so: (snap) => 'Check SO list for completeness and distinctness. Ensure phrasing is outcome-based and aligned to accreditation.',
    cdio: (snap) => 'Review CDIO skills coverage. Suggest integration points in activities and assessments.',
    sdg: (snap) => 'Check SDG alignment. Suggest authentic integration of relevant goals in topics/assessments.',
    iga: (snap) => 'Review institutional graduate attributes and how they appear in the syllabus. Suggest tighter linkage to activities/assessments.',
    ilo_so_cpa_mapping: (snap) => 'Evaluate ILO-to-SO mapping with C/P/A. Identify gaps or overloaded outcomes.',
    ilo_iga_mapping: (snap) => 'Evaluate ILO-to-IGA mapping. Identify missing alignments or redundancies.',
    ilo_cdio_sdg_mapping: (snap) => 'Evaluate ILO to CDIO and SDG mapping. Look for gaps, misalignments, or overstuffed outcomes.',
    textbook: (snap) => 'Review textbook and references. Check recency and fit. Suggest newer editions or open resources if needed.',
    general_info: (snap) => [
      'This section appears to be empty or incomplete in the syllabus.',
      'Do not reference or display the snapshot data provided.',
      'Instead, provide helpful suggestions for what should be included, best practices, and a brief example or template to guide the faculty member.',
      'Respond based on general syllabus design best practices and educational frameworks without showing or quoting the snapshot content.',
      'Be encouraging and practical in your suggestions.'
    ].join(' '),
  };

  function isSnapshotEmpty(snapshot){
    if (!snapshot) return true;
    if (snapshot.raw === null || snapshot.raw === undefined) return true;
    const raw = snapshot.raw;
    // Check for common empty patterns
    if (Array.isArray(raw.rows) && raw.rows.length === 0) return true;
    if (Array.isArray(raw.sections) && raw.sections.length === 0) return true;
    if (Array.isArray(raw.ilos) && raw.ilos.length === 0) return true;
    if (Array.isArray(raw.sos) && raw.sos.length === 0) return true;
    if (Array.isArray(raw.igas) && raw.igas.length === 0) return true;
    if (Array.isArray(raw.cdios) && raw.cdios.length === 0) return true;
    if (Array.isArray(raw.sdgs) && raw.sdgs.length === 0) return true;
    if (!raw.vision && !raw.mission) return true; // mission_vision
    if (raw.tlas === '' || raw.tlas === '-') return true; // tlas
    return false;
  }

  function get(key, snapshot){
    // If snapshot is empty, use general info prompt instead
    if (isSnapshotEmpty(snapshot)) {
      const fn = promptFns['general_info'];
      if (typeof fn === 'function') {
        try { return fn(snapshot); } catch(_) { /* fall through */ }
      }
    }

    const fn = promptFns[key];
    if (typeof fn === 'function') {
      try { return fn(snapshot); } catch(_) { /* fall through */ }
    }
    return defaultPrompt;
  }

  function set(key, promptOrFn){
    if (!key) return;
    promptFns[key] = promptOrFn;
  }

  function list(){
    const keys = Object.keys(promptFns);
    const out = {};
    keys.forEach(k => { out[k] = '[Function]'; });
    return out;
  }

  function getAll(){
    const keys = Object.keys(promptFns);
    const out = {};
    keys.forEach(k => {
      try {
        const fn = promptFns[k];
        if (typeof fn === 'function') {
          out[k] = fn(null);
        }
      } catch(_) {
        out[k] = '';
      }
    });
    return out;
  }

  window.SVPrompts = {
    get,
    set,
    list,
    getAll,
    defaultPrompt,
  };
})();
