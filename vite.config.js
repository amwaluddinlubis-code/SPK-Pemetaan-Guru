// Konfigurasi Vite untuk Laravel.
// Plugin @tailwindcss/vite mengompilasi Tailwind + daisyUI dari resources/css/app.css.

import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true, // auto-reload browser saat file Blade berubah
        }),
        tailwindcss(),
    ],
});
