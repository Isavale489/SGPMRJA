import '../css/plataforma.css';

import { createInertiaApp } from '@inertiajs/react';
import { createRoot } from 'react-dom/client';
import { StrictMode } from 'react';

import { vigilarHistorial } from '@/hooks/use-guard-cambios';

const nombreApp = import.meta.env.VITE_APP_NAME || 'SGPMRJA';

// Antes de que Inertia registre su propio popstate (Atrás con cambios sin guardar).
vigilarHistorial();

createInertiaApp({
    title: (titulo) => (titulo ? `${titulo} · ${nombreApp}` : nombreApp),
    resolve: (nombre) => {
        const paginas = import.meta.glob('./pages/**/*.tsx');
        const pagina = paginas[`./pages/${nombre}.tsx`];
        if (!pagina) {
            throw new Error(`Página Inertia inexistente: ${nombre}`);
        }
        return pagina() as Promise<never>;
    },
    setup({ el, App, props }) {
        if (!el) return;
        createRoot(el).render(
            <StrictMode>
                <App {...props} />
            </StrictMode>,
        );
    },
    progress: { color: '#2a5298', delay: 150 },
});
