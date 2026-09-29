import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import react from '@vitejs/plugin-react';
import tailwindcss from '@tailwindcss/vite';
import { fileURLToPath, URL } from 'node:url';

export default defineConfig({
    plugins: [
        laravel({
            // Plataforma Inertia + React (todo el panel).
            input: ['resources/js/inertia.tsx'],
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
    build: {
        // AG Charts (~1,25 MB, ~380 KB comprimido; lo mismo que la versión que
        // cargaban las vistas Blade) va en su propio chunk y solo lo piden las
        // páginas con gráficos. El aviso por defecto (500 kB) sería ruido fijo.
        chunkSizeWarningLimit: 1400,
    },
});
