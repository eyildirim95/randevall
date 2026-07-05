import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                // css
                'resources/scss/app.scss',
                'resources/scss/icons.scss',
                'node_modules/choices.js/public/assets/styles/choices.min.css',
                'node_modules/gridjs/dist/theme/mermaid.min.css',
                'node_modules/flatpickr/dist/flatpickr.min.css',

                // core js
                'resources/js/app.js',
                'resources/js/config.js',
                'resources/js/layout.js',

                // page js
                'resources/js/pages/panel-calendar.js',
                'resources/js/pages/panel-dashboard.js',
                'resources/js/pages/panel-reports.js',
                'resources/js/pages/superadmin-dashboard.js',

                // component js
                'resources/js/components/form-flatepicker.js',
            ],
            refresh: true,
        }),
    ],
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
