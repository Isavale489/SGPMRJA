import type { ReactNode } from 'react';

/** Dato de una ficha "Ver" (ícono + etiqueta + valor), dentro de un <dl>. */
export function Dato({ icono, etiqueta, children }: { icono: ReactNode; etiqueta: string; children: ReactNode }) {
    return (
        <div className="flex gap-3">
            <span className="bg-secondary text-secondary-foreground grid size-8 shrink-0 place-items-center rounded-md [&_svg]:size-4">{icono}</span>
            <div className="min-w-0">
                <dt className="text-muted-foreground text-xs">{etiqueta}</dt>
                <dd className="text-sm break-words">{children}</dd>
            </div>
        </div>
    );
}
