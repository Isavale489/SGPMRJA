import type { EnlaceNavegacion, GrupoNavegacion, ItemNavegacion } from '@/types';

export const esGrupo = (i: ItemNavegacion): i is GrupoNavegacion => 'items' in i;

/** ¿El enlace corresponde a la URL actual? También sus subpáginas (/compras/crear activa «Compras»). */
export function estaActivo(enlace: EnlaceNavegacion, url: string): boolean {
    const ruta = url.split('?')[0] ?? '';
    return enlace.url === '/' ? ruta === '/' : ruta === enlace.url || ruta.startsWith(`${enlace.url}/`);
}

export function contieneActivo(item: ItemNavegacion, url: string): boolean {
    return esGrupo(item) ? item.items.some((h) => contieneActivo(h, url)) : estaActivo(item, url);
}

/** Enlace del menú que corresponde a la URL actual (el más específico si hay varios), o null. */
export function enlaceActivo(items: ItemNavegacion[], url: string): EnlaceNavegacion | null {
    let mejor: EnlaceNavegacion | null = null;
    for (const item of items) {
        const candidato = esGrupo(item) ? enlaceActivo(item.items, url) : estaActivo(item, url) ? item : null;
        if (candidato && (!mejor || candidato.url.length > mejor.url.length)) mejor = candidato;
    }
    return mejor;
}
