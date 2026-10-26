import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import { bunny } from 'laravel-vite-plugin/fonts';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js', 'resources/js/client.js', 'resources/js/kds.js', 'resources/js/pos.js', 'resources/js/admin.js', 'resources/js/restaurant.js', 'resources/js/landing.js'],
            refresh: true,
            fonts: [
                bunny('Plus Jakarta Sans', {
                    weights: [400, 500, 600, 700],
                }),
            ],
        }),
        tailwindcss(),
    ],
    server: {
        host: '0.0.0.0',
        cors: true,
        hmr: {
            host: '192.168.1.67',
        },
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
