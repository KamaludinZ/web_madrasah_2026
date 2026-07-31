import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
            ],
            refresh: [
                'resources/views/**',
                'routes/**',
            ],
        }),
    ],
    server: {
        watch: {
            ignored: [
                '**/vendor/**',
                '**/node_modules/**',
                '**/storage/**',
                '**/.git/**',
            ],
        },
    },
    build: {
        rollupOptions: {
            output: {
                manualChunks(id) {
                    if (id.includes('alpinejs') || id.includes('swiper') || id.includes('aos')) {
                        return 'vendor';
                    }
                },
            },
        },
    },
});
