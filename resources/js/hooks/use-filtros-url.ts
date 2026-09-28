import { router } from '@inertiajs/react';
import { useEffect, useRef, useState } from 'react';

type Filtros = Record<string, string | undefined>;

/**
 * Filtros de una tabla guardados en la URL (?buscar=…&tipo=…). La URL es el
 * estado: recargar, compartir el enlace o volver atrás conserva la vista.
 *
 * Cada cambio pide al servidor SOLO las props de la tabla (`only`), sin
 * recargar la página ni perder el scroll. El texto espera 300 ms de pausa.
 */
export function useFiltrosUrl<T extends Filtros>(url: string, iniciales: T, props: string[]) {
    const [filtros, setFiltros] = useState<T>(iniciales);
    const [cargando, setCargando] = useState(false);
    const primera = useRef(true);
    const pendiente = useRef<ReturnType<typeof setTimeout> | undefined>(undefined);

    const visitar = (valores: T) => {
        const limpios = Object.fromEntries(Object.entries(valores).filter(([, v]) => v !== undefined && v !== ''));
        router.get(url, limpios, {
            only: props,
            preserveState: true,
            preserveScroll: true,
            replace: true,
            onStart: () => setCargando(true),
            onFinish: () => setCargando(false),
        });
    };

    useEffect(() => {
        if (primera.current) {
            primera.current = false;
            return;
        }
        clearTimeout(pendiente.current);
        pendiente.current = setTimeout(() => visitar(filtros), 300);
        return () => clearTimeout(pendiente.current);
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [filtros]);

    const cambiar = <K extends keyof T>(clave: K, valor: T[K]) => setFiltros((f) => ({ ...f, [clave]: valor }));
    const limpiar = (conservar: (keyof T)[] = []) =>
        setFiltros((f) => Object.fromEntries(conservar.map((k) => [k, f[k]])) as unknown as T);

    return { filtros, cambiar, limpiar, cargando };
}
