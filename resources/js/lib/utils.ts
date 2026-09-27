import { clsx, type ClassValue } from 'clsx';
import { twMerge } from 'tailwind-merge';

/** Une clases condicionales y resuelve conflictos de Tailwind (p. ej. `px-2` vs `px-4`). */
export function cn(...inputs: ClassValue[]) {
    return twMerge(clsx(inputs));
}
