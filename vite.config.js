import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import react from '@vitejs/plugin-react';
import tailwindcss from '@tailwindcss/vite';
import { fileURLToPath, URL } from 'node:url';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                // Páginas públicas y layouts Blade de Breeze (Alpine + axios).
                'resources/js/app.js',
                // Plataforma Inertia + React (páginas migradas).
                'resources/js/inertia.tsx',
            ],
            refresh: true,
        }),
        react(),
        // Tailwind v4 procesa TODA hoja CSS del build: por eso Bootstrap no se
        // empaqueta aquí (se enlaza estático desde public/assets). La única hoja
        // del build es resources/css/plataforma.css, importada por inertia.tsx.
        tailwindcss(),
    ],
    resolve: {
        alias: { '@': fileURLToPath(new URL('./resources/js', import.meta.url)) },
    },
});
