import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css', 
                'resources/css/syllaverse-colors.css',
                'resources/css/admin/admin-sidebar.css',
                'resources/css/faculty/manage-accounts/manage-accounts.css',
                'resources/css/superadmin/manage-accounts/manage-accounts.css',
                'resources/js/app.js',
                'resources/js/admin/courses/courses.js',
                'resources/js/faculty/courses/courses.js',
                'resources/js/faculty/master-data/ilo-simple.js',
                'resources/js/faculty/master-data/ilo-sortable.js',
                'resources/js/faculty/master-data/shared-crud.js',
                'resources/js/superadmin/manage-accounts/manage-accounts.js'
            ],
            refresh: true,
        }),
        tailwindcss(),
    ],
});
