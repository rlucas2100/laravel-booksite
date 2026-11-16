import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig(({ command }) => ({
    // Relative addresses inside the built CSS, so the fonts still load when the
    // site is installed in a sub-folder (example.com/bookshop/).
    base: command === 'build' ? './' : undefined,
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
        tailwindcss(),
    ],
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**', '**/_original-2023/**'],
        },
    },
}));
