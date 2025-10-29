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
                'resources/css/faculty/manage-accounts/manage-accounts.css',
                
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
                'resources/js/faculty/courses/courses.js',
                'resources/js/faculty/master-data/ilo-simple.js',
                'resources/js/faculty/master-data/ilo-sortable.js',
                'resources/js/faculty/master-data/shared-crud.js',
                
                // Super Admin JS
                'resources/js/superadmin/superadmin-login.js',
                'resources/js/superadmin/layout.js',
                'resources/js/superadmin/departments.js',
                'resources/js/superadmin/alert-timer.js',
                'resources/js/superadmin/chair-requests.js',
                'resources/js/superadmin/appointments.js',
                'resources/js/superadmin/manage-accounts/manage-accounts.js',
                'resources/js/superadmin/master-data/sortable.js'
            ],
            refresh: true,
        }),
        tailwindcss(),
    ],
});
