/**
 * Formato es-VE centralizado. Toda cifra, monto o fecha visible pasa por aquí.
 */

// narrowSymbol → "$4.820,00" (como el resto del sistema), no "USD 4.820,00".
const usd = new Intl.NumberFormat('es-VE', { style: 'currency', currency: 'USD', currencyDisplay: 'narrowSymbol', minimumFractionDigits: 2 });
const bs = new Intl.NumberFormat('es-VE', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
const numero = new Intl.NumberFormat('es-VE', { maximumFractionDigits: 2 });

export function formatoUsd(monto: number): string {
    return usd.format(monto);
}

/** "Bs 1.234,56" — el BCV publica en bolívares con coma decimal. */
export function formatoBs(monto: number): string {
    return `Bs ${bs.format(monto)}`;
}

export function formatoNumero(valor: number): string {
    return numero.format(valor);
}

/**
 * Fecha "AAAA-MM-DD" (como la guarda Laravel) → "dd/mm/aaaa".
 * Se parsea a mano, SIN `new Date('2026-09-27')`: eso es medianoche UTC y en
 * Venezuela (UTC−4) se muestra como el día anterior.
 */
export function formatoFecha(iso: string): string {
    const [a, m, d] = iso.slice(0, 10).split('-');
    return `${d}/${m}/${a}`;
}

/** Hoy en hora local como "AAAA-MM-DD" (para <input type="date">). No usar toISOString(): es UTC. */
export function hoyLocalIso(): string {
    const f = new Date();
    const m = String(f.getMonth() + 1).padStart(2, '0');
    const d = String(f.getDate()).padStart(2, '0');
    return `${f.getFullYear()}-${m}-${d}`;
}
