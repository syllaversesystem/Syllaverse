// -----------------------------------------------------------------------------
// File: public/js/superadmin/departments.js
// Description: Handles feather icons, modal data setup, and draggable FAB on Manage Departments page – Syllaverse
// -----------------------------------------------------------------------------
// 📜 Log:
// [2025-07-28] Initial creation – extracted inline scripts from blade, added draggable FAB and modal setup logic.
// [2025-07-28] Updated feather.replace() with safety check to prevent JS crash if feather is undefined.
// [2025-08-07] Refined: feather icons now re-render inside dropdowns; modal toggle restored after FAB drag.
// -----------------------------------------------------------------------------

document.addEventListener('DOMContentLoaded', function () {
    // 🪶 Replace all feather icons safely
    if (typeof feather !== 'undefined') {
        feather.replace();
    } else {
        console.warn("⚠️ Feather icons not loaded: skipping feather.replace()");
    }

    // 🔁 Refresh feather icons when dropdowns open (for action menus)
    document.querySelectorAll('.dropdown').forEach(dropdown => {
        dropdown.addEventListener('shown.bs.dropdown', function () {
            if (typeof feather !== 'undefined') feather.replace();
        });
    });

    // 📝 Setup Edit Department modal
    window.setEditDepartment = function (button) {
        const id = button.dataset.id;
        const name = button.dataset.name;
        const code = button.dataset.code;
        const form = document.getElementById('editDepartmentForm');

        form.action = `/superadmin/departments/${id}`;
        form.querySelector('#editDepartmentName').value = name;
        form.querySelector('#editDepartmentCode').value = code;
    };

    // 🗑️ Setup Delete Department modal
    window.setDeleteDepartment = function (button) {
        const id = button.dataset.id;
        document.getElementById('deleteDepartmentForm').action = `/superadmin/departments/${id}`;
    };

    // 🎯 Setup draggable FAB
    const fab = document.getElementById("draggableAddFab");
    if (!fab) return;

    let offsetX = 0, offsetY = 0;
    let isDragging = false;
    let isDraggableMode = false;
    let holdTimeout, dragStartEvent, holdStarted = false;

    function onHoldStart(e) {
        if (e.type === 'mousedown' && e.button !== 0) return;
        holdStarted = true;
        dragStartEvent = e;
        holdTimeout = setTimeout(() => {
            isDraggableMode = true;
            fab.classList.add('draggable-mode');
            startDrag(dragStartEvent);
        }, 1000);
    }

    function onHoldEnd() {
        clearTimeout(holdTimeout);
        holdStarted = false;
        dragStartEvent = null;
    }

    function startDrag(e) {
        if (!isDraggableMode) return;

        isDragging = true;
        fab.classList.add('dragging');
        const rect = fab.getBoundingClientRect();

        let clientX, clientY;
        if (e.type?.startsWith('touch')) {
            clientX = e.touches[0].clientX;
            clientY = e.touches[0].clientY;
        } else {
            clientX = e.clientX;
            clientY = e.clientY;
        }

        offsetX = clientX - rect.left;
        offsetY = clientY - rect.top;
        fab.setAttribute('data-bs-toggle', '');
        fab.setAttribute('data-bs-target', '');
    }

    function onDragMove(e) {
        if (!isDragging || !isDraggableMode) return;
        e.preventDefault();

        let x, y;
        if (e.type.startsWith('touch')) {
            x = e.touches[0].clientX;
            y = e.touches[0].clientY;
        } else {
            x = e.clientX;
            y = e.clientY;
        }

        const winW = window.innerWidth;
        const winH = window.innerHeight;

        const left = Math.min(Math.max(0, x - offsetX), winW - fab.offsetWidth);
        const top = Math.min(Math.max(0, y - offsetY), winH - fab.offsetHeight);

        fab.style.left = `${left}px`;
        fab.style.top = `${top}px`;
        fab.style.right = "auto";
        fab.style.bottom = "auto";
    }

    function onDragEnd() {
        if (isDragging || isDraggableMode) {
            isDragging = false;
            isDraggableMode = false;
            fab.classList.remove('dragging', 'draggable-mode');

            // ✅ Restore modal attributes after drag ends
            fab.setAttribute('data-bs-toggle', 'modal');
            fab.setAttribute('data-bs-target', '#addDepartmentModal');
        }

        clearTimeout(holdTimeout);
        holdStarted = false;
        dragStartEvent = null;
    }

    // 🚫 Prevent accidental modal open during drag
    fab.addEventListener('click', e => {
        if (isDraggableMode || isDragging || holdStarted) {
            e.preventDefault();
            e.stopPropagation();
        }
    });

    // 👆 Hold to drag listeners
    fab.addEventListener('mousedown', onHoldStart);
    fab.addEventListener('touchstart', onHoldStart);
    fab.addEventListener('mouseup', onHoldEnd);
    fab.addEventListener('mouseleave', onHoldEnd);
    fab.addEventListener('touchend', onHoldEnd);

    // 🧲 Drag start (in draggable mode only)
    fab.addEventListener('mousedown', e => { if (isDraggableMode) startDrag(e); });
    fab.addEventListener('touchstart', e => { if (isDraggableMode) startDrag(e); });

    // 🔄 Drag move events
    window.addEventListener('mousemove', onDragMove);
    window.addEventListener('touchmove', onDragMove);

    // 🧷 Drag end
    window.addEventListener('mouseup', onDragEnd);
    window.addEventListener('touchend', onDragEnd);
});
