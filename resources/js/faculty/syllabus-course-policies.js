/**
 * File: resources/js/faculty/syllabus-course-policies.js
 * Description: JavaScript for Course Policies module - Load Predefined functionality
 */

document.addEventListener('DOMContentLoaded', function () {
  // Load Predefined Policy functionality
  const loadPolicyBtn = document.getElementById('policy-load-predefined');
  const loadPolicyModal = document.getElementById('loadPredefinedPolicyModal');
  const confirmLoadBtn = document.getElementById('confirmLoadPredefinedPolicy');
  const syllabusId = document.getElementById('syllabus-document')?.dataset?.syllabusId;

  if (loadPolicyBtn && loadPolicyModal && syllabusId) {
    loadPolicyBtn.addEventListener('click', async function() {
      const modal = new bootstrap.Modal(loadPolicyModal);
      const previewContent = document.getElementById('policyPreviewContent');
      
      // Show loading state
      previewContent.innerHTML = `
        <div class="text-center text-muted py-3">
          <i data-feather="loader" class="spinner"></i>
          <p class="mb-0 mt-2">Loading policies...</p>
        </div>
      `;
      feather.replace();
      
      modal.show();
      
      try {
        // Fetch all predefined policies from server
        const response = await fetch(`/faculty/syllabi/${syllabusId}/predefined-policies`);
        const data = await response.json();
        
        if (data.success && data.policies) {
          // Display all policies in a formatted view
          let html = '';
          const sectionLabels = {
            policy: 'Class Policy',
            exams: 'Missed Examinations',
            dishonesty: 'Academic Dishonesty',
            dropping: 'Dropping',
            other: 'Other Course Policies and Requirements'
          };
          
          Object.entries(data.policies).forEach(([section, content]) => {
            if (content) {
              html += `
                <div class="mb-3">
                  <div class="fw-semibold text-uppercase" style="font-size: 0.875rem; color: #6c757d; margin-bottom: 0.5rem;">${sectionLabels[section] || section}</div>
                  <div class="policy-content">${content}</div>
                </div>
              `;
            }
          });
          
          if (html) {
            previewContent.innerHTML = html;
          } else {
            previewContent.innerHTML = `
              <div class="text-center text-muted py-3">
                <i data-feather="alert-circle"></i>
                <p class="mb-0 mt-2">No predefined policies found.</p>
              </div>
            `;
            feather.replace();
          }
        } else {
          previewContent.innerHTML = `
            <div class="text-center text-muted py-3">
              <i data-feather="alert-circle"></i>
              <p class="mb-0 mt-2">${data.message || 'No predefined policies found.'}</p>
            </div>
          `;
          feather.replace();
        }
      } catch (error) {
        console.error('Error loading predefined policies:', error);
        previewContent.innerHTML = `
          <div class="text-center text-danger py-3">
            <i data-feather="alert-triangle"></i>
            <p class="mb-0 mt-2">Failed to load policies. Please try again.</p>
          </div>
        `;
        feather.replace();
      }
    });

    // Handle confirm load button
    if (confirmLoadBtn) {
      confirmLoadBtn.addEventListener('click', async function() {
        try {
          // Fetch policies again to get raw data
          const response = await fetch(`/faculty/syllabi/${syllabusId}/predefined-policies`);
          const data = await response.json();
          
          if (data.success && data.policies) {
            // Get all policy textareas in order
            const textareas = document.querySelectorAll('.course-policies textarea[name="course_policies[]"]');
            const sections = ['policy', 'exams', 'dishonesty', 'dropping', 'other'];
            
            // Populate each textarea with corresponding policy
            sections.forEach((section, index) => {
              if (textareas[index] && data.policies[section]) {
                textareas[index].value = data.policies[section];
                
                // Trigger autosize if available
                if (window.autosize) {
                  autosize.update(textareas[index]);
                }
                
                // Trigger change event for unsaved tracking
                textareas[index].dispatchEvent(new Event('input', { bubbles: true }));
              }
            });
            
            // Close modal
            bootstrap.Modal.getInstance(loadPolicyModal).hide();
          }
        } catch (error) {
          console.error('Error loading policies:', error);
        }
      });
    }
  }
});
