import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    base: process.env.VITE_BASE_URL || '/',
    plugins: [
        laravel({
            input: [
                // Core CSS
                'resources/css/app.css', 
                'resources/css/syllaverse-colors.css',
                // Optional app-wide
                'resources/css/admin-dashboard.css',
                'resources/css/admin-faculty.css',
                'resources/css/student.css',
                
                // Components CSS
                'resources/css/components/alert-overlay.css',
                'resources/css/components/dropdown.css',
                // Components JS
                'resources/js/components/alert-overlay.js',
                // In-app browser guard
                'resources/css/components/inapp-browser-guard.css',
                'resources/js/components/inapp-browser-guard.js',
                
                // Faculty CSS
                'resources/css/faculty/faculty-sidebar.css',
                'resources/css/faculty/faculty-navbar.css',
                'resources/css/faculty/faculty-layout.css',
                                // In-app browser guard
                                'resources/css/components/inapp-browser-guard.css',
                                'resources/js/components/inapp-browser-guard.js',
                'resources/css/faculty/syllabus.css',
                'resources/css/faculty/syllabus-index.css',
                'resources/css/faculty/complete-profile/main-tabs.css',
                
                // Super Admin CSS
                'resources/css/superadmin/login.css',
                'resources/css/superadmin/dashboard.css',
                'resources/css/superadmin/layouts/superadmin-sidebar.css',
                'resources/css/superadmin/layouts/superadmin-navbar.css',
                'resources/css/superadmin/layouts/superadmin-layout.css',
                'resources/css/superadmin/departments/departments.css',
                'resources/css/superadmin/manage-accounts/manage-accounts.css',
                // Super Admin CSS Partials - Manage Accounts
                'resources/css/superadmin/manage-accounts/partials/toolbar.css',
                'resources/css/superadmin/manage-accounts/partials/tabs.css',
                'resources/css/superadmin/manage-accounts/partials/table.css',
                'resources/css/superadmin/manage-accounts/partials/pills.css',
                'resources/css/superadmin/manage-accounts/partials/modal.css',
                'resources/css/superadmin/manage-accounts/partials/details.css',
                'resources/css/superadmin/manage-accounts/partials/buttons.css',
                // Super Admin CSS Partials - Departments
                'resources/css/superadmin/departments/partials/variables.css',
                'resources/css/superadmin/departments/partials/toolbar.css',
                'resources/css/superadmin/departments/partials/table.css',
                'resources/css/superadmin/departments/partials/modals.css',
                'resources/css/superadmin/departments/partials/media.css',
                'resources/css/superadmin/departments/partials/fab.css',
                'resources/css/superadmin/departments/partials/card.css',
                'resources/css/superadmin/departments/partials/buttons.css',
                // Note: possible typo directory retained for completeness
                'resources/css/superladmin/manage-accounts/partials/details.css',
                
                // Core JS
                'resources/js/app.js',
                
                // Faculty JS
                'resources/js/faculty/layout.js',
                'resources/js/faculty/departments.js',
                'resources/js/faculty/manageprofile.js',
                'resources/js/faculty/programs/programs.js',
                'resources/js/faculty/courses/courses.js',
                'resources/js/faculty/complete-profile.js',
                'resources/js/faculty/syllabus.js',
                'resources/js/faculty/syllabus-course-info.js',
                'resources/js/faculty/syllabus-course-policies.js',
                'resources/js/faculty/syllabus-sdg.js',
                'resources/js/faculty/syllabus-textbook.js',
                'resources/js/faculty/syllabus-tla.js',
                'resources/js/faculty/syllabus-ilo.js',
                'resources/js/faculty/syllabus-so.js',
                'resources/js/faculty/syllabus-assessment-mapping.js',
                'resources/js/faculty/syllabus-cdio.js',
                'resources/js/faculty/syllabus-create.js',
                'resources/js/faculty/syllabus-status.js',
                'resources/js/faculty/syllabus-ai-chat.js',
                'resources/js/faculty/syllabus-criteria.js',
                'resources/js/faculty/syllabus-iga.js',
                'resources/js/faculty/syllabus-ilo-iga.js',
                'resources/js/faculty/syllabus-ilo-so-cpa.js',
                'resources/js/faculty/syllabus-ilo-cdio-sdg.js',
                'resources/js/faculty/syllabus-mission-vision.js',

                // Faculty Master Data JS
                'resources/js/faculty/master-data/so.js',
                'resources/js/faculty/master-data/sdg.js',
                'resources/js/faculty/master-data/iga.js',
                'resources/js/faculty/master-data/cdio.js',
                'resources/js/faculty/master-data/ilo-simple.js',
                'resources/js/faculty/master-data/shared-crud.js',
                
                // Super Admin JS
                'resources/js/superadmin/superadmin-login.js',
                'resources/js/superadmin/layout.js',
                'resources/js/superadmin/departments.js',
                'resources/js/superadmin/alert-timer.js',
                'resources/js/superadmin/chair-requests.js',
                'resources/js/superadmin/appointments.js',
                'resources/js/superadmin/manage-accounts/manage-accounts.js',

                // Shared libs
                'resources/js/lib/api.js'
            ],
            refresh: true,
        }),
        tailwindcss(),
    ],
});
