// -----------------------------------------------------------------------------
// File: resources/js/admin/programs-courses-search.js
// Description: Search functionality for Programs & Courses tables
// -----------------------------------------------------------------------------
// 📜 Log:
// [2025-01-04] Created search functionality for programs and courses tables
// -----------------------------------------------------------------------------

document.addEventListener('DOMContentLoaded', function () {
    // 🪶 Replace all feather icons safely
    if (typeof feather !== 'undefined') {
        feather.replace();
    }

    // 🎯 Fix modal z-index and initialization issues
    const addProgramModal = document.getElementById('addProgramModal');
    if (addProgramModal) {
        // Ensure modal is properly initialized
        addProgramModal.addEventListener('show.bs.modal', function () {
            // Force proper z-index
            this.style.zIndex = '1055';
            // Clear any previous backdrop issues
            document.querySelectorAll('.modal-backdrop').forEach(backdrop => {
                if (backdrop.style.zIndex < '1054') {
                    backdrop.style.zIndex = '1054';
                }
            });
        });
        
        // Refresh feather icons when modal opens
        addProgramModal.addEventListener('shown.bs.modal', function () {
            if (typeof feather !== 'undefined') {
                feather.replace();
            }
        });
    }

    // 🔍 Programs Search Functionality
    const programsSearch = document.getElementById('programsSearch');
    if (programsSearch) {
        programsSearch.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const programsTable = document.querySelector('#programs-main .table tbody');
            
            if (programsTable) {
                const rows = programsTable.querySelectorAll('tr');
                rows.forEach(row => {
                    const programName = row.querySelector('td:nth-child(2)')?.textContent.toLowerCase() || '';
                    const programCode = row.querySelector('td:nth-child(1)')?.textContent.toLowerCase() || '';
                    const department = row.querySelector('td:nth-child(3)')?.textContent.toLowerCase() || '';
                    
                    const matches = programName.includes(searchTerm) || 
                                  programCode.includes(searchTerm) || 
                                  department.includes(searchTerm);
                    
                    row.style.display = matches ? '' : 'none';
                });
            }
        });
    }

    // 🔍 Courses Search Functionality
    const coursesSearch = document.getElementById('coursesSearch');
    if (coursesSearch) {
        coursesSearch.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const coursesTable = document.querySelector('#courses-main .table tbody');
            
            if (coursesTable) {
                const rows = coursesTable.querySelectorAll('tr');
                rows.forEach(row => {
                    const courseCode = row.querySelector('td:nth-child(1)')?.textContent.toLowerCase() || '';
                    const courseName = row.querySelector('td:nth-child(2)')?.textContent.toLowerCase() || '';
                    const department = row.querySelector('td:nth-child(3)')?.textContent.toLowerCase() || '';
                    const program = row.querySelector('td:nth-child(4)')?.textContent.toLowerCase() || '';
                    
                    const matches = courseCode.includes(searchTerm) || 
                                  courseName.includes(searchTerm) || 
                                  department.includes(searchTerm) ||
                                  program.includes(searchTerm);
                    
                    row.style.display = matches ? '' : 'none';
                });
            }
        });
    }

    // 🔁 Refresh feather icons when tabs change
    const tabButtons = document.querySelectorAll('#programsCoursesMainTabs button[data-bs-toggle="tab"]');
    tabButtons.forEach(button => {
        button.addEventListener('shown.bs.tab', function () {
            if (typeof feather !== 'undefined') {
                feather.replace();
            }
        });
    });

    // 🐛 Debug modal functionality
    const addProgramButton = document.querySelector('[data-bs-target="#addProgramModal"]');
    if (addProgramButton) {
        addProgramButton.addEventListener('click', function(e) {
            console.log('Add Program button clicked');
            // Force modal to show if it's not working
            setTimeout(() => {
                const modal = document.getElementById('addProgramModal');
                if (modal && !modal.classList.contains('show')) {
                    console.log('Manually showing modal');
                    const bsModal = new bootstrap.Modal(modal);
                    bsModal.show();
                }
            }, 100);
        });
    }
});