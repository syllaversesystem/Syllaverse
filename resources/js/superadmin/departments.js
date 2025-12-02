// -----------------------------------------------------------------------------
// File: resources/js/superadmin/departments.js
// Description: Handles icons, modals, and AJAX form submissions for Superadmin Departments
// -----------------------------------------------------------------------------
import { Modal } from 'bootstrap';

function refreshDepartmentsTable() {
    const csrfEl = document.querySelector('meta[name="csrf-token"]');
    const csrf = csrfEl ? csrfEl.getAttribute('content') : '';
    fetch('/superadmin/departments/table-content', {
        method: 'GET',
        headers: {
            'X-CSRF-TOKEN': csrf,
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        }
    })
        .then(response => {
            if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
            return response.json();
        })
        .then(data => {
            if (data.success) {
                const tableBody = document.getElementById('departmentsTableBody');
                if (tableBody) {
                    tableBody.innerHTML = data.html;
                    if (typeof feather !== 'undefined') feather.replace();
                }
            } else {
                if (window.showAlertOverlay) window.showAlertOverlay('error', data.message || 'Failed to refresh table');
            }
        })
        .catch(error => {
            if (window.showAlertOverlay) window.showAlertOverlay('error', 'Error refreshing table: ' + error.message);
        });
}

document.addEventListener('DOMContentLoaded', function () {
    if (typeof feather !== 'undefined') feather.replace();

    document.querySelectorAll('.dropdown').forEach(dropdown => {
        dropdown.addEventListener('shown.bs.dropdown', function () {
            if (typeof feather !== 'undefined') feather.replace();
        });
    });

    const addModal = document.getElementById('addDepartmentModal');
    if (addModal) {
        addModal.addEventListener('show.bs.modal', function () {
            const form = document.getElementById('addDepartmentForm');
            const errorDiv = document.getElementById('addDepartmentErrors');
            if (form) form.reset();
            if (errorDiv) { errorDiv.classList.add('d-none'); errorDiv.innerHTML = ''; }
        });
    }

    // Expose helpers for buttons in partial
    window.setEditDepartment = function (button) {
        const id = button.dataset.id;
        const name = button.dataset.name;
        const code = button.dataset.code;
        const form = document.getElementById('editDepartmentForm');
        if (!form) return;
        form.action = `/superadmin/departments/${id}`;
        const idInput = document.getElementById('editDepartmentId');
        const nameInput = form.querySelector('#editDepartmentName');
        const codeInput = form.querySelector('#editDepartmentCode');
        if (idInput) idInput.value = id;
        if (nameInput) nameInput.value = name;
        if (codeInput) codeInput.value = code;
        const errorDiv = document.getElementById('editDepartmentErrors');
        if (errorDiv) { errorDiv.classList.add('d-none'); errorDiv.innerHTML = ''; }
    };

    window.setDeleteDepartment = function (button) {
        const id = button.dataset.id;
        const name = button.dataset.name;
        const code = button.dataset.code;
        const deleteForm = document.getElementById('deleteDepartmentForm');
        const idInput = document.getElementById('deleteDepartmentId');
        if (deleteForm) deleteForm.action = `/superadmin/departments/${id}`;
        if (idInput) idInput.value = id;
        const nameElement = document.getElementById('deleteDepartmentName');
        const codeElement = document.getElementById('deleteDepartmentCode');
        if (nameElement) nameElement.textContent = name || 'Unknown';
        if (codeElement) codeElement.textContent = code || 'Unknown';
    };

    // Add
    const addForm = document.getElementById('addDepartmentForm');
    if (addForm) {
        addForm.removeAttribute('action');
        addForm.addEventListener('submit', async function (e) {
            e.preventDefault();
            const csrfEl = document.querySelector('meta[name="csrf-token"]');
            const csrf = csrfEl ? csrfEl.getAttribute('content') : '';
            const formData = new FormData(this);
            const submitBtn = document.getElementById('addDepartmentSubmit');
            const errorDiv = document.getElementById('addDepartmentErrors');
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i data-feather="loader" class="spinner"></i> Creating...';
            if (typeof feather !== 'undefined') feather.replace();
            errorDiv.classList.add('d-none'); errorDiv.innerHTML = '';
            try {
                const res = await fetch('/superadmin/departments', { method: 'POST', headers: { 'X-CSRF-TOKEN': csrf }, body: formData });
                const data = await res.json();
                if (!res.ok || !data.success) throw data;
                if (window.showAlertOverlay) window.showAlertOverlay('success', data.message || 'Department created');
                const addEl = document.getElementById('addDepartmentModal');
                Modal.getOrCreateInstance(addEl).hide();
                refreshDepartmentsTable();
            } catch (err) {
                if (err && err.errors) {
                    errorDiv.classList.remove('d-none');
                    errorDiv.innerHTML = Object.values(err.errors).flat().map(m => `<div>${m}</div>`).join('');
                }
                if (window.showAlertOverlay) window.showAlertOverlay('error', (err && err.message) || 'Failed to create department');
            } finally {
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i data-feather="plus"></i> Create';
                if (typeof feather !== 'undefined') feather.replace();
            }
        });
    }

    // Edit
    const editForm = document.getElementById('editDepartmentForm');
    if (editForm) {
        editForm.removeAttribute('action');
        editForm.addEventListener('submit', async function (e) {
            e.preventDefault();
            const id = document.getElementById('editDepartmentId').value;
            const csrfEl = document.querySelector('meta[name="csrf-token"]');
            const csrf = csrfEl ? csrfEl.getAttribute('content') : '';
            const formData = new FormData(this);
            const submitBtn = document.getElementById('editDepartmentSubmit');
            const errorDiv = document.getElementById('editDepartmentErrors');
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i data-feather="loader" class="spinner"></i> Updating...';
            if (typeof feather !== 'undefined') feather.replace();
            errorDiv.classList.add('d-none'); errorDiv.innerHTML = '';
            try {
                const res = await fetch(`/superadmin/departments/${id}`, { method: 'POST', headers: { 'X-CSRF-TOKEN': csrf, 'X-HTTP-Method-Override': 'PUT' }, body: formData });
                const data = await res.json();
                if (!res.ok || !data.success) throw data;
                if (window.showAlertOverlay) window.showAlertOverlay('success', data.message || 'Department updated');
                const editEl = document.getElementById('editDepartmentModal');
                Modal.getOrCreateInstance(editEl).hide();
                refreshDepartmentsTable();
            } catch (err) {
                if (err && err.errors) {
                    errorDiv.classList.remove('d-none');
                    errorDiv.innerHTML = Object.values(err.errors).flat().map(m => `<div>${m}</div>`).join('');
                }
                if (window.showAlertOverlay) window.showAlertOverlay('error', (err && err.message) || 'Failed to update department');
            } finally {
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i data-feather="save"></i> Update';
                if (typeof feather !== 'undefined') feather.replace();
            }
        });
    }

    // Delete
    const deleteForm = document.getElementById('deleteDepartmentForm');
    if (deleteForm) {
        deleteForm.removeAttribute('action');
        deleteForm.addEventListener('submit', async function (e) {
            e.preventDefault();
            const id = document.getElementById('deleteDepartmentId').value;
            const csrfEl = document.querySelector('meta[name="csrf-token"]');
            const csrf = csrfEl ? csrfEl.getAttribute('content') : '';
            const formData = new FormData(this);
            try {
                const res = await fetch(`/superadmin/departments/${id}`, { method: 'POST', headers: { 'X-CSRF-TOKEN': csrf, 'X-HTTP-Method-Override': 'DELETE' }, body: formData });
                const data = await res.json();
                if (!res.ok || !data.success) throw data;
                if (window.showAlertOverlay) window.showAlertOverlay('success', data.message || 'Department deleted');
                const delEl = document.getElementById('deleteDepartmentModal');
                Modal.getOrCreateInstance(delEl).hide();
                refreshDepartmentsTable();
            } catch (err) {
                if (window.showAlertOverlay) window.showAlertOverlay('error', (err && err.message) || 'Failed to delete department');
            }
        });
    }

    // Search
    (function wireDepartmentsSearch() {
        const input = document.getElementById('departmentsSearch');
        const tbody = document.getElementById('departmentsTableBody');
        if (!input || !tbody) return;
        let t = null;
        input.addEventListener('input', () => {
            clearTimeout(t);
            t = setTimeout(async () => {
                const q = input.value.trim();
                try {
                    const url = new URL(window.location.origin + '/superadmin/departments/table-content');
                    if (q) url.searchParams.set('q', q);
                    const res = await fetch(url, { headers: { 'Accept': 'application/json' } });
                    const data = await res.json();
                    if (data.success) {
                        tbody.innerHTML = data.html;
                        if (typeof feather !== 'undefined') feather.replace();
                    }
                } catch { /* ignore */ }
            }, 250);
        });
    })();

    // Spinner style
    const style = document.createElement('style');
    style.textContent = `.spinner { animation: spin 1s linear infinite; } @keyframes spin {from{transform:rotate(0)} to{transform:rotate(360deg)}}`;
    document.head.appendChild(style);
});
