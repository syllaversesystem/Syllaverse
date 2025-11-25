import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                // Core CSS
                'resources/css/app.css', 
                'resources/css/syllaverse-colors.css',
                
                // Components CSS
                'resources/css/components/alert-overlay.css',
                
                // Faculty CSS
                'resources/css/faculty/faculty-sidebar.css',
                'resources/css/faculty/faculty-navbar.css',
                'resources/css/faculty/faculty-layout.css',
                'resources/css/faculty/syllabus.css',
                'resources/css/faculty/syllabus-index.css',
                
                // Super Admin CSS
                'resources/css/superadmin/login.css',
                'resources/css/superadmin/layouts/superadmin-sidebar.css',
                'resources/css/superadmin/layouts/superadmin-navbar.css',
                'resources/css/superadmin/layouts/superadmin-layout.css',
                'resources/css/superadmin/departments/departments.css',
                'resources/css/superadmin/manage-accounts/manage-accounts.css',
                
                // Core JS
                'resources/js/app.js',
                
                // Faculty JS
                'resources/js/faculty/layout.js',
                'resources/js/faculty/departments.js',
                'resources/js/faculty/programs/programs.js',
                'resources/js/faculty/courses/courses.js',
                'resources/js/faculty/complete-profile.js',
                'resources/js/faculty/syllabus.js',
                'resources/js/faculty/syllabus-course-info.js',
                'resources/js/faculty/syllabus-sdg.js',
                'resources/js/faculty/syllabus-textbook.js',
                'resources/js/faculty/syllabus-tla.js',
                'resources/js/faculty/syllabus-tla-ai.js',
                'resources/js/faculty/syllabus-ilo.js',
                'resources/js/faculty/syllabus-so.js',
                'resources/js/faculty/syllabus-assessment-mapping.js',
                'resources/js/faculty/syllabus-cdio.js',

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
                'resources/js/superadmin/manage-accounts/manage-accounts.js'
            ],
            refresh: true,
        }),
        tailwindcss(),
    ],
});
