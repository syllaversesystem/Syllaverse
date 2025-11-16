{{--
-------------------------------------------------------------------------------
* File: resources/views/faculty/syllabus/syllabus.blade.php
* Description: Main Syllabus (CIS) edit page composed of modular partials
* Rationale: Recreated after accidental removal to satisfy SyllabusController@show
-------------------------------------------------------------------------------
--}}
@extends('layouts.faculty')

@section('title', ($syllabus->course?->code ? ($syllabus->course->code.' • ') : '').'Syllabus')

@php
  // Provide route base for partials expecting $routePrefix
  $routePrefix = 'faculty.syllabi';
@endphp

@section('content')
<div class="syllabus-doc" id="syllabus-document" data-syllabus-id="{{ $syllabus->id }}">
  {{-- Top toolbar card (parity with index toolbar) --}}
  <div class="svx-card mb-3">
    <div class="svx-card-body">
      <div class="programs-toolbar mb-0 w-100" id="syllabusToolbar">
        <!-- Left: Save -->
        <div class="d-flex align-items-center gap-2">
          <button id="syllabusSaveBtn" type="button" class="btn btn-danger" title="Save" aria-label="Save">
            <i class="bi bi-save"></i>
            <span id="unsaved-count-badge" class="badge bg-warning text-dark ms-1" style="display:none;">0</span>
          </button>
        </div>

        <span class="flex-spacer"></span>

        <!-- Right: Auto-Save + Exit -->
        <div class="d-flex align-items-center gap-3">
          <div class="form-check form-switch m-0" title="Auto-Save">
            <input class="form-check-input" type="checkbox" role="switch" id="autoSaveToggle">
            <label class="form-check-label d-none d-md-inline" for="autoSaveToggle">Auto-Save</label>
          </div>
          <button type="button" class="btn btn-outline-secondary" onclick="handleExit('{{ route('faculty.syllabi.index') }}')" title="Exit" aria-label="Exit">
            <i class="bi bi-arrow-left"></i>
            <span class="d-none d-sm-inline">Exit</span>
          </button>
        </div>
      </div>
    </div>
  </div>

  <form id="syllabusForm" method="POST" action="{{ route('faculty.syllabi.update', $syllabus->id) }}" novalidate>
    @csrf
    @method('PUT')
    <input type="hidden" name="id" value="{{ $syllabus->id }}">

    <div class="svx-card mb-3">
      <div class="svx-card-body">
        {{-- Header / Title Banner --}}
        @include('faculty.syllabus.partials.header')

        {{-- Mission & Vision --}}
        @include('faculty.syllabus.partials.mission-vision')

  {{-- Course Information (CIS table) + Criteria for Assessment (embedded inside partial) --}}
  @include('faculty.syllabus.partials.course-info')

  {{-- Teaching, Learning, and Assessment Strategies (summary) --}}
  @includeWhen(View::exists('faculty.syllabus.partials.tlas'), 'faculty.syllabus.partials.tlas')

  {{-- Intended Learning Outcomes --}}
  @include('faculty.syllabus.partials.ilo')

  {{-- Assessment Method and Distribution Map (moved directly below ILO for workflow continuity) --}}
  @includeWhen(View::exists('faculty.syllabus.partials.assessment-tasks-distribution'), 'faculty.syllabus.partials.assessment-tasks-distribution')

  {{-- Textbook Upload / References --}}
  @includeWhen(View::exists('faculty.syllabus.partials.textbook-upload'), 'faculty.syllabus.partials.textbook-upload')

  {{-- IGA (Institutional Graduate Attributes) --}}
  @includeWhen(View::exists('faculty.syllabus.partials.iga'), 'faculty.syllabus.partials.iga')

        {{-- Student Outcomes --}}
        @includeWhen(View::exists('faculty.syllabus.partials.so'), 'faculty.syllabus.partials.so')

        {{-- CDIO --}}
        @includeWhen(View::exists('faculty.syllabus.partials.cdio'), 'faculty.syllabus.partials.cdio')

        {{-- Sustainable Development Goals --}}
        @includeWhen(View::exists('faculty.syllabus.partials.sdg'), 'faculty.syllabus.partials.sdg')

        {{-- Course Policies --}}
        @include('faculty.syllabus.partials.course-policies')

        {{-- Teaching & Learning Activities (dedicated partial if present) --}}
        @includeWhen(View::exists('faculty.syllabus.partials.tla'), 'faculty.syllabus.partials.tla')

        {{-- Assessment Mapping (ILO/SO/CDIO/SDG/IGA crosswalk) moved directly below TLA for immediate follow-up workflows --}}
        @includeWhen(View::exists('faculty.syllabus.partials.assessment-mapping'), 'faculty.syllabus.partials.assessment-mapping')

        {{-- Assessment Mappings (ILO → SO → CPA) --}}
        @includeWhen(View::exists('faculty.syllabus.partials.ilo-so-cpa-mapping'), 'faculty.syllabus.partials.ilo-so-cpa-mapping')

        {{-- ILO ↔ IGA Mapping --}}
        @includeWhen(View::exists('faculty.syllabus.partials.ilo-iga-mapping'), 'faculty.syllabus.partials.ilo-iga-mapping')

          {{-- ILO ↔ CDIO ↔ SDG Mapping --}}
          @includeWhen(View::exists('faculty.syllabus.partials.mapping-ilo-cdio-sdg'), 'faculty.syllabus.partials.mapping-ilo-cdio-sdg')
      </div>
    </div>

    {{-- Bottom Save / Exit (redundant for long scroll) --}}
    <div class="d-flex gap-2 my-4">
      <button id="syllabusSaveBtnBottom" type="button" class="btn btn-danger" onclick="document.getElementById('syllabusSaveBtn').click();">
        <i class="bi bi-save"></i>
        <span class="badge bg-warning text-dark ms-1 d-none" id="unsaved-count-badge-bottom">0</span>
      </button>
      <button type="button" class="btn btn-outline-secondary" onclick="handleExit('{{ route('faculty.syllabi.index') }}')">
        <i class="bi bi-arrow-left"></i> Exit
      </button>
    </div>
  </form>
</div>
@endsection

@push('scripts')
<script>
  // Quick section search: scroll to first matching CIS section label
  // (Search removed) – cleanup placeholder logic; leaving hook if reintroduced later.

  // Mirror top unsaved count to bottom button if present
  document.addEventListener('DOMContentLoaded', function(){
    const topBadge = document.getElementById('unsaved-count-badge');
    const bottomBadge = document.getElementById('unsaved-count-badge-bottom');
    if (!topBadge || !bottomBadge) return;
    const mo = new MutationObserver(() => {
      bottomBadge.textContent = topBadge.textContent;
      bottomBadge.classList.toggle('d-none', topBadge.style.display === 'none');
    });
    mo.observe(topBadge, { characterData: true, subtree: true, childList: true, attributes: true });
  });

  // -----------------------------
  // Undo / Redo & Auto-Save Logic
  // -----------------------------
  document.addEventListener('DOMContentLoaded', function(){
    const form = document.getElementById('syllabusForm');
    if (!form) return;
    const undoBtn = document.getElementById('undoBtn');
    const redoBtn = document.getElementById('redoBtn');
  const autoToggle = document.getElementById('autoSaveToggle');
    const saveBtn = document.getElementById('syllabusSaveBtn');

    const past = []; // previous states
    const future = []; // undone states
    const MAX_STACK = 25;
    let applyingSnapshot = false;
    let autoSaveEnabled = false;
    let autoSaveTimer = null;

    // Load persisted auto-save preference
    try { autoSaveEnabled = localStorage.getItem('sv_auto_save') === 'true'; } catch(e) {}
  updateAutoSaveUI();

    function captureSnapshot(){
      // Build a map name -> values; handles multiple fields with same name
      const groups = {};
      const radiosHandled = new Set();
      const elements = Array.from(form.querySelectorAll('input, textarea, select'));
      elements.forEach(el => {
        if (!el.name) return;
        const name = el.name;
        // Radio group: store selected value once
        if (el.type === 'radio') {
          if (radiosHandled.has(name)) return;
          radiosHandled.add(name);
          const selected = elements.find(e => e.type === 'radio' && e.name === name && e.checked);
          groups[name] = selected ? selected.value : null;
          return;
        }
        // Select (multiple)
        if (el.tagName === 'SELECT' && el.multiple) {
          const vals = Array.from(el.options).filter(o => o.selected).map(o => o.value);
          groups[name] = vals;
          return;
        }
        // For checkbox with multiple of same name, collect as array of booleans in DOM order
        const siblings = elements.filter(e2 => e2.name === name && e2 !== el && e2.type === el.type);
        const isMulti = siblings.length > 0;
        if (el.type === 'checkbox') {
          if (!isMulti && !(name in groups)) {
            groups[name] = !!el.checked;
          } else {
            if (!Array.isArray(groups[name])) groups[name] = [];
            groups[name].push(!!el.checked);
          }
        } else {
          if (isMulti) {
            if (!Array.isArray(groups[name])) groups[name] = [];
            groups[name].push(el.value);
          } else {
            groups[name] = el.value;
          }
        }
      });
      return groups;
    }

    function applySnapshot(snap){
      if (!snap) return;
      applyingSnapshot = true;
      Object.keys(snap).forEach(name => {
        const value = snap[name];
        const nodes = Array.from(form.elements).filter(function(n){ return n.name === name; });
        if (!nodes || nodes.length === 0) return;
        const first = nodes[0];
        if (first.type === 'radio') {
          nodes.forEach(n => { n.checked = (n.value === value); });
        } else if (first.type === 'checkbox') {
          if (Array.isArray(value)) {
            nodes.forEach((n, i) => { n.checked = !!value[i]; });
          } else {
            nodes.forEach((n, i) => { n.checked = (i === 0) ? !!value : n.checked; });
          }
        } else if (first.tagName === 'SELECT' && first.multiple) {
          const set = new Set(Array.isArray(value) ? value : []);
          nodes.forEach(n => { Array.from(n.options).forEach(o => o.selected = set.has(o.value)); });
        } else if (nodes.length > 1 && Array.isArray(value)) {
          nodes.forEach((n, i) => { n.value = (i < value.length) ? value[i] : n.value; n.dispatchEvent(new Event('input', { bubbles:true })); });
          return;
        } else {
          nodes.forEach(n => { n.value = (Array.isArray(value) ? value[0] : value); n.dispatchEvent(new Event('input', { bubbles:true })); });
        }
      });
      applyingSnapshot = false;
      updateButtons();
    }

    function pushPast(snap){
      past.push(snap);
      while (past.length > MAX_STACK) past.shift();
      future.length = 0; // clear redo on new change
      updateButtons();
    }

    function updateButtons(){
      if (undoBtn) undoBtn.disabled = past.length <= 1; // first snapshot is initial
      if (redoBtn) redoBtn.disabled = future.length === 0;
    }

    // Seed initial state
    pushPast(captureSnapshot());

    // Debounced change listener to snapshot and maybe auto-save
    let changeTimer = null;
    function onUserChange(){
      if (applyingSnapshot) return;
      try { window.isDirty = true; } catch(e){}
      clearTimeout(changeTimer);
      changeTimer = setTimeout(()=>{
        pushPast(captureSnapshot());
        scheduleAutoSave();
      }, 350);
    }
    form.addEventListener('input', onUserChange);
    form.addEventListener('change', onUserChange);

    // Undo / Redo buttons
    if (undoBtn) undoBtn.addEventListener('click', function(){
      if (past.length <= 1) return;
      const current = past.pop();
      future.push(current);
      const prev = past[past.length - 1];
      applySnapshot(prev);
    });
    if (redoBtn) redoBtn.addEventListener('click', function(){
      if (future.length === 0) return;
      const next = future.pop();
      past.push(next);
      applySnapshot(next);
    });

    // Keyboard shortcuts: Ctrl+Z / Ctrl+Y or Ctrl+Shift+Z
    document.addEventListener('keydown', function(e){
      const key = (e.key || '').toLowerCase();
      if (e.ctrlKey && !e.shiftKey && key === 'z') { e.preventDefault(); if (undoBtn && !undoBtn.disabled) undoBtn.click(); }
      else if ((e.ctrlKey && key === 'y') || (e.ctrlKey && e.shiftKey && key === 'z')) { e.preventDefault(); if (redoBtn && !redoBtn.disabled) redoBtn.click(); }
    });

    // Auto-Save toggle + UI (switch)
    if (autoToggle) autoToggle.addEventListener('change', function(){
      autoSaveEnabled = !!autoToggle.checked;
      try { localStorage.setItem('sv_auto_save', autoSaveEnabled ? 'true' : 'false'); } catch(e){}
      updateAutoSaveUI();
      if (autoSaveEnabled) scheduleAutoSave();
    });

    function updateAutoSaveUI(){
      if (!autoToggle) return;
      autoToggle.checked = !!autoSaveEnabled;
      autoToggle.setAttribute('aria-checked', autoSaveEnabled ? 'true' : 'false');
      autoToggle.title = autoSaveEnabled ? 'Auto-Save: On' : 'Auto-Save: Off';
    }

    function scheduleAutoSave(){
      if (!autoSaveEnabled) return;
      if (!saveBtn) return;
      if (window._syllabusSaveLock) return;
      clearTimeout(autoSaveTimer);
      autoSaveTimer = setTimeout(()=>{
        if (window._syllabusSaveLock) return;
        if (saveBtn) {
          try { saveBtn.click(); } catch(e){}
        }
      }, 1500);
    }

    // Expose for debugging
    try { window._svUndoRedo = { past, future, capture: captureSnapshot, apply: applySnapshot }; } catch(e){}
  });
</script>
@endpush

@push('styles')
<style>
  /* Toolbar look – align with index page */
  .programs-toolbar { display:flex; align-items:center; flex-wrap:wrap; gap:.25rem; }
  .programs-toolbar .input-group {
    flex:1; background: var(--sv-bg, #FAFAFA);
    border: 1px solid var(--sv-border, #E3E3E3);
    border-radius: 6px; overflow: hidden;
    box-shadow: 0 1px 2px rgba(0,0,0,0.02);
  }
  .programs-toolbar .input-group .form-control { padding:.4rem .75rem; font-size:.88rem; border:none; background:transparent; height:2.2rem; }
  .programs-toolbar .input-group .form-control:focus { outline:none; box-shadow:none; background:transparent; }
  .programs-toolbar .input-group .input-group-text { background:transparent; border:none; padding-left:.7rem; padding-right:.4rem; display:flex; align-items:center; }
  .svx-card { background:#fff; border:1px solid var(--sv-border,#E3E3E3); border-radius:10px; }
  .svx-card-body { padding: .85rem; }
  @keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
  }
</style>
@endpush
