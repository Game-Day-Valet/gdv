import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/scss/icons.scss',
                'resources/scss/app.scss',
                'node_modules/swiper/swiper-bundle.min.css',
                'node_modules/glightbox/dist/css/glightbox.min.css',
                'node_modules/flatpickr/dist/flatpickr.min.css',
                'node_modules/quill/dist/quill.core.css',
                'node_modules/quill/dist/quill.snow.css',
                'node_modules/quill/dist/quill.bubble.css', 
                'node_modules/datatables.net-bs5/css/dataTables.bootstrap5.min.css',
                'node_modules/datatables.net-buttons-bs5/css/buttons.bootstrap5.min.css',
                'node_modules/datatables.net-keytable-bs5/css/keyTable.bootstrap5.min.css',
                'node_modules/datatables.net-responsive-bs5/css/responsive.bootstrap5.min.css',
                'node_modules/datatables.net-select-bs5/css/select.bootstrap5.min.css',
                'node_modules/jsvectormap/dist/jsvectormap.min.css',
                'resources/js/app.js',
                'resources/js/echo.js',
                'resources/js/pages/analytics-dashboard.init.js',
                'resources/js/pages/ecommerce-dashboard.init.js',
                'resources/js/pages/widgets.init.js',
                'resources/js/pages/glightbox.init.js',
                'resources/js/pages/form-picker.js',
                'resources/js/pages/quilljs.init.js',
                'resources/js/pages/datatable.init.js',
                'resources/js/pages/coming-soon.init.js',
                'resources/js/pages/apexcharts-line.init.js',
                'resources/js/pages/apexcharts-area.init.js', 
                'resources/js/pages/apexcharts-column.init.js',
                'resources/js/pages/apexcharts-bar.init.js',
                'resources/js/pages/apexcharts-mixed.init.js',
                'resources/js/pages/apexcharts-boxplot.init.js',
                'resources/js/pages/apexcharts-bubble.init.js',
                'resources/js/pages/apexcharts-candlestick.init.js',
                'resources/js/pages/apexcharts-funnel.init.js',
                'resources/js/pages/apexcharts-heatmap.init.js',
                'resources/js/pages/apexcharts-pie.init.js',
                'resources/js/pages/apexcharts-polararea.init.js',
                'resources/js/pages/apexcharts-radar.init.js',
                'resources/js/pages/apexcharts-radialbar.init.js',
                'resources/js/pages/apexcharts-range-area.init.js',
                'resources/js/pages/apexcharts-scatter.init.js',
                'resources/js/pages/apexcharts-treemap.init.js',
                'resources/js/pages/apexcharts-timeline.init.js',
                'resources/js/pages/vector-maps.init.js',
                'resources/js/pages/google-maps-init.js',
                'resources/js/pages/demo.calendar.js',
                'resources/js/pages/iraq-map.js',
                'resources/js/pages/canada-map.js',
                'resources/js/pages/russia-map.js',
                'resources/js/pages/spain-map.js',
                'resources/js/pages/us-mill-en-map.js',
                'resources/js/pages/us-lcc-en-map.js',
                'resources/js/pages/us-merc-en-map.js',
                'resources/js/pages/vector-maps.init.js',
            ],
            refresh: true,
        }),
    ],
    define: {
        'import.meta.env.VITE_PUSHER_APP_KEY': JSON.stringify(process.env.PUSHER_APP_KEY || '018b37a23cdaa32a0f1c'),
        'import.meta.env.VITE_PUSHER_APP_CLUSTER': JSON.stringify(process.env.PUSHER_APP_CLUSTER || 'ap2'),
    },
    build: {
        outDir: 'public/build',
        manifest: true,
        rollupOptions: {
            output: {
                entryFileNames: ({ name }) => {
                    if (name === 'echo') {
                        return 'js/echo.js';
                    }
                    return 'assets/[name]-[hash].js';
                },
                chunkFileNames: 'assets/[name]-[hash].js',
                assetFileNames: 'assets/[name]-[hash][extname]',
            },
        },
    },
});