// vite.config.js
import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css', // Mantenha se você usa
                'resources/js/app.js',   // Mantenha se você usa
                // NADA MAIS AQUI
            ],
            refresh: true,
        }),
    ],
});