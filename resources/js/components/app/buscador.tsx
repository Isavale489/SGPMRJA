import { Loader2, Search } from 'lucide-react';
import { useEffect, useId, useRef, useState, type ReactNode } from 'react';

import { Input } from '@/components/ui/input';
import { cn } from '@/lib/utils';

interface Props<T> {
    /** Nombre accesible del campo (si no hay <label> visible). */
    etiqueta: string;
    placeholder: string;
    /** Resultados para el texto: filtra una lista ya cargada, o (con `remoto`) hace una petición. */
    buscar: (texto: string) => T[] | Promise<T[]>;
    /** La búsqueda va al servidor: espera 300 ms de pausa y descarta respuestas viejas. */
    remoto?: boolean;
    clave: (item: T) => string | number;
    opcion: (item: T) => ReactNode;
    onElegir: (item: T) => void;
    /** Lo que se muestra cuando no hay resultados (p. ej. un botón para crear). */
    vacio?: (texto: string) => ReactNode;
    /** Pie fijo bajo los resultados (p. ej. «Nuevo proveedor»). */
    pie?: (texto: string) => ReactNode;
    /** Con 0 caracteres también busca (útil para listar recientes). */
    buscarVacio?: boolean;
    id?: string;
    className?: string;
    'aria-invalid'?: boolean;
    'aria-describedby'?: string;
}

/**
 * Combobox de búsqueda (patrón WAI-ARIA): flechas para moverse, Enter para
 * elegir, Escape para cerrar.
 */
export function Buscador<T>({ etiqueta, placeholder, buscar, remoto, clave, opcion, onElegir, vacio, pie, buscarVacio, id, className, ...aria }: Props<T>) {
    const idLista = useId();
    const [texto, setTexto] = useState('');
    const [abierto, setAbierto] = useState(false);
    const [items, setItems] = useState<T[]>([]);
    const [activo, setActivo] = useState(0);
    const [cargando, setCargando] = useState(false);
    const turno = useRef(0);
    const contenedor = useRef<HTMLDivElement>(null);

    useEffect(() => {
        if (!abierto || (!texto.trim() && !buscarVacio)) {
            setItems([]);
            return;
        }
        const mio = ++turno.current;
        const aplicar = (r: T[]) => { if (mio === turno.current) { setItems(r); setActivo(0); } };
        if (!remoto) {
            aplicar(buscar(texto.trim()) as T[]);
            return;
        }
        setCargando(true);
        const t = setTimeout(() => {
            Promise.resolve(buscar(texto.trim()))
                .then(aplicar)
                .catch(() => aplicar([]))
                .finally(() => { if (mio === turno.current) setCargando(false); });
        }, 300);
        return () => clearTimeout(t);
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [texto, abierto]);

    // Cerrar al hacer clic fuera.
    useEffect(() => {
        if (!abierto) return;
        const fuera = (e: MouseEvent) => { if (!contenedor.current?.contains(e.target as Node)) setAbierto(false); };
        document.addEventListener('mousedown', fuera);
        return () => document.removeEventListener('mousedown', fuera);
    }, [abierto]);

    const elegir = (item: T) => {
        onElegir(item);
        setTexto('');
        setAbierto(false);
    };

    const teclado = (e: React.KeyboardEvent) => {
        if (e.key === 'ArrowDown') { e.preventDefault(); setAbierto(true); setActivo((a) => Math.min(a + 1, items.length - 1)); }
        else if (e.key === 'ArrowUp') { e.preventDefault(); setActivo((a) => Math.max(a - 1, 0)); }
        else if (e.key === 'Enter' && abierto && items[activo]) { e.preventDefault(); elegir(items[activo]); }
        else if (e.key === 'Escape') setAbierto(false);
    };

    const idOpcion = (i: number) => `${idLista}-${i}`;
    const mostrar = abierto && (texto.trim() !== '' || buscarVacio);

    return (
        <div ref={contenedor} className={cn('relative', className)}>
            <Search className="text-muted-foreground pointer-events-none absolute top-1/2 left-2.5 size-4 -translate-y-1/2" />
            <Input
                id={id}
                role="combobox"
                aria-label={etiqueta}
                aria-expanded={Boolean(mostrar)}
                aria-controls={idLista}
                aria-autocomplete="list"
                aria-activedescendant={mostrar && items[activo] ? idOpcion(activo) : undefined}
                autoComplete="off"
                value={texto}
                placeholder={placeholder}
                onChange={(e) => { setTexto(e.target.value); setAbierto(true); }}
                onFocus={() => setAbierto(true)}
                onKeyDown={teclado}
                className="pr-8 pl-8"
                {...aria}
            />
            {cargando && <Loader2 className="text-muted-foreground absolute top-1/2 right-2.5 size-4 -translate-y-1/2 animate-spin" aria-hidden />}
            {mostrar && (
                <div className="bg-popover text-popover-foreground absolute z-50 mt-1 w-full overflow-hidden rounded-md border shadow-md">
                    <ul id={idLista} role="listbox" aria-label={etiqueta} className="max-h-72 overflow-y-auto p-1">
                        {items.map((item, i) => (
                            <li
                                key={clave(item)}
                                id={idOpcion(i)}
                                role="option"
                                aria-selected={i === activo}
                                onMouseEnter={() => setActivo(i)}
                                onMouseDown={(e) => { e.preventDefault(); elegir(item); }}
                                className={cn('cursor-pointer rounded-sm px-2 py-1.5 text-sm', i === activo && 'bg-accent text-accent-foreground')}
                            >
                                {opcion(item)}
                            </li>
                        ))}
                    </ul>
                    {!cargando && items.length === 0 && vacio && <div className="text-muted-foreground p-3 text-sm">{vacio(texto.trim())}</div>}
                    {pie && <div className="border-t p-1">{pie(texto.trim())}</div>}
                </div>
            )}
        </div>
    );
}
