// -------------------------------------------------------------------------------
// * File: public/js/admin/courses-search.js
// * Description: Search functionality for admin courses table
// -------------------------------------------------------------------------------

document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('coursesSearch');
    const table = document.getElementById('svCoursesTable');
    const tbody = document.getElementById('svCoursesTbody');
    
    if (!searchInput || !table || !tbody) {
        return;
    }
    
    let searchTimeout;
    
    searchInput.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            const searchTerm = this.value.toLowerCase().trim();
            const rows = tbody.querySelectorAll('tr');
            let visibleCount = 0;
            
            rows.forEach(row => {
                // Skip empty state rows
                if (row.classList.contains('sv-empty-row')) {
                    return;
                }
                
                // Get text content from relevant columns
                const code = row.querySelector('td:first-child')?.textContent?.toLowerCase() || '';
                const title = row.querySelector('td:nth-child(2)')?.textContent?.toLowerCase() || '';
                
                // For department column, check if it exists (might be hidden when filtering)
                const departmentCell = row.querySelector('td:nth-child(3)');
                const department = departmentCell && departmentCell.querySelector('[data-feather="layers"]') 
                    ? departmentCell.textContent?.toLowerCase() || '' 
                    : '';
                
                // Check if any field matches the search term
                const matches = code.includes(searchTerm) || 
                               title.includes(searchTerm) || 
                               department.includes(searchTerm);
                
                if (matches) {
                    row.style.display = '';
                    visibleCount++;
                } else {
                    row.style.display = 'none';
                }
            });
            
            // Handle empty state
            handleEmptyState(tbody, visibleCount, searchTerm);
            
        }, 300); // 300ms debounce delay
    });
    
    function handleEmptyState(tbody, visibleCount, searchTerm) {
        let emptyRow = tbody.querySelector('.sv-empty-row');
        
        if (visibleCount === 0 && searchTerm) {
            // Show empty state
            if (!emptyRow) {
                const emptyHtml = `
                    <tr class="sv-empty-row">
                        <td colspan="100%">
                            <div class="sv-empty">
                                <h6>No courses found</h6>
                                <p>No courses match your search criteria. Try adjusting your search terms.</p>
                            </div>
                        </td>
                    </tr>
                `;
                tbody.insertAdjacentHTML('beforeend', emptyHtml);
            } else {
                emptyRow.style.display = '';
            }
        } else if (emptyRow) {
            // Hide empty state
            emptyRow.style.display = 'none';
        }
    }
});