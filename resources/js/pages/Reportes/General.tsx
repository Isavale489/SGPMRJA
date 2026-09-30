import { Link } from '@inertiajs/react';
import { AlertTriangle, ChevronRight, FileText, Hammer, ChartColumn, ShoppingBag } from 'lucide-react';

import { Icono } from '@/components/app/icono';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import AppLayout from '@/layouts/app-layout';
import { formatoNumero } from '@/lib/formato';
import { cn } from '@/lib/utils';

import type { GrupoHub, KpisHub } from './tipos';

// Ícono con el color de la sección vigente (la de la página o la del grupo que lo envuelve).
const ICONO_SECCION = 'bg-seccion-acento/12 text-seccion';

function Kpi({ valor, etiqueta, icono, alerta }: { valor: number; etiqueta: string; icono: React.ReactNode; alerta?: boolean }) {
    return (
        <Card className="py-4">
            <CardContent className="flex items-center gap-3">
                <span className={cn('grid size-10 place-items-center rounded-lg [&_svg]:size-5', alerta === undefined ? ICONO_SECCION : alerta ? 'bg-destructive/10 text-destructive' : 'bg-success/12 text-success')}>{icono}</span>
                <div>
                    <p className="text-2xl font-semibold tabular">{formatoNumero(valor)}</p>
                    <p className="text-muted-foreground text-xs">{etiqueta}</p>
                </div>
            </CardContent>
        </Card>
    );
}

/** Hub de reportes: el catálogo sale de config/reportes.php, ya filtrado por permisos. */
export default function ReportesGeneral({ grupos, kpis }: { grupos: GrupoHub[]; kpis: KpisHub }) {
    return (
        <AppLayout titulo="Reportes generales">
            <div className="grid gap-6">
                <section className="grid gap-3 sm:grid-cols-2 xl:grid-cols-4" aria-label="Resumen del mes">
                    <Kpi valor={kpis.pedidos_mes} etiqueta="Pedidos este mes" icono={<ShoppingBag />} />
                    <Kpi valor={kpis.cotizaciones_mes} etiqueta="Cotizaciones este mes" icono={<FileText />} />
                    <Kpi valor={kpis.ordenes_activas} etiqueta="Órdenes activas" icono={<Hammer />} />
                    <Kpi valor={kpis.insumos_criticos} etiqueta="Insumos bajo mínimo" icono={<AlertTriangle />} alerta={kpis.insumos_criticos > 0} />
                </section>

                {grupos.length === 0 && <p className="text-muted-foreground rounded-lg border border-dashed p-8 text-center text-sm">No tienes acceso a ningún reporte.</p>}

                {grupos.map((g) => (
                    // Cada grupo con el color de la sección de origen de sus reportes.
                    <Card key={g.titulo} data-seccion={g.seccion}>
                        <CardHeader>
                            <div className="flex items-center gap-3">
                                <span className={cn('grid size-9 shrink-0 place-items-center rounded-lg', ICONO_SECCION)}><Icono nombre={g.icono} className="size-5" /></span>
                                <div className="grid gap-1">
                                    <CardTitle className="text-base">{g.titulo}</CardTitle>
                                    <CardDescription>{g.descripcion}</CardDescription>
                                </div>
                            </div>
                        </CardHeader>
                        <CardContent>
                            <ul className="grid gap-3 md:grid-cols-2 2xl:grid-cols-3">
                                {g.reportes.map((r) => {
                                    const contenido = (
                                        <>
                                            <span className={cn('grid size-9 shrink-0 place-items-center rounded-md', ICONO_SECCION)}><Icono nombre={r.icono} className="size-4" /></span>
                                            <span className="grid min-w-0 flex-1 gap-0.5">
                                                <span className="font-medium">{r.titulo}</span>
                                                <span className="text-muted-foreground text-xs">{r.descripcion}</span>
                                            </span>
                                            <span className="text-muted-foreground flex shrink-0 items-center gap-1 text-xs">
                                                {r.formato === 'pdf' ? <><FileText className="size-3.5" /> PDF</> : <><ChartColumn className="size-3.5" /> Vista</>}
                                                <ChevronRight className="size-4" />
                                            </span>
                                        </>
                                    );
                                    const clase = 'hover:border-primary hover:bg-muted/40 flex items-center gap-3 rounded-lg border p-3 text-sm transition-colors';
                                    return (
                                        <li key={r.ruta}>
                                            {r.formato === 'pdf'
                                                ? <a href={r.url} target="_blank" rel="noopener" className={clase}>{contenido}</a>
                                                : <Link href={r.url} className={clase}>{contenido}</Link>}
                                        </li>
                                    );
                                })}
                            </ul>
                        </CardContent>
                    </Card>
                ))}
            </div>
        </AppLayout>
    );
}
