import { Link, usePage } from '@inertiajs/react';
import type { AgChartOptions } from 'ag-charts-community';
import { AlertTriangle, ArrowRight, FileClock, Shirt, ShieldAlert, ShieldCheck, Truck, UserCog, UserRound, X } from 'lucide-react';
import { useMemo, useState, type ReactNode } from 'react';

import { PanelOrdenable } from '@/components/app/panel-ordenable';
import { TarjetaGrafico } from '@/components/app/grafico';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { usePermisos } from '@/hooks/use-permisos';
import AppLayout from '@/layouts/app-layout';
import { formatoBs, formatoNumero, formatoUsd } from '@/lib/formato';
import { cn } from '@/lib/utils';

/** Espejo de HomeController::index() (lo verifica DashboardPaginaTest). */
export interface PaginaDashboard {
    kpis: { por_entregar: number; insumos_alerta: number; cotizaciones_por_vencer: number };
    maestros: { clientes: number; productos: number; empleados: number; proveedores: number };
    pedidos: { estado: string; total: number }[];
    tendencia: { mes: string; pedidos: number; monto: number }[];
    alertaRecuperacion: { fecha: string; ip: string | null; resultado: string; tipo: string } | null;
    urls: Record<'pedidos' | 'cotizaciones' | 'alertas' | 'clientes' | 'productos' | 'empleados' | 'proveedores', string>;
}

const COLOR_PEDIDO: Record<string, string> = { Pendiente: '#f7b84b', Procesando: '#299cdb', Completado: '#0ab39c', Cancelado: '#f06548' };

/** Enlace a un módulo: Inertia si ya migró; recarga completa si sigue en Blade; texto si no hay permiso. */
function Enlace({ url, blade, permitido, className, children }: { url: string; blade?: boolean; permitido: boolean; className?: string; children: ReactNode }) {
    if (!permitido) return <div className={className}>{children}</div>;
    return blade ? <a href={url} className={className}>{children}</a> : <Link href={url} className={className}>{children}</Link>;
}

function Kpi({ titulo, valor, detalle, icono, tono }: { titulo: string; valor: number; detalle: string; icono: ReactNode; tono: 'warning' | 'destructive' | 'sky' }) {
    const colores = { warning: 'bg-warning/12 text-warning', destructive: 'bg-destructive/10 text-destructive', sky: 'bg-sky-500/12 text-sky-600 dark:text-sky-300' };
    return (
        <Card className="h-full py-5 transition-shadow group-hover:shadow-md">
            <CardContent className="flex items-center gap-4">
                <div className="flex-1">
                    <p className="text-muted-foreground text-xs font-medium tracking-wide uppercase">{titulo}</p>
                    <p className="text-2xl font-semibold tabular">{formatoNumero(valor)}</p>
                    <p className="text-muted-foreground text-xs">{detalle}</p>
                </div>
                <span className={cn('grid size-11 place-items-center rounded-lg [&_svg]:size-5', colores[tono])}>{icono}</span>
            </CardContent>
        </Card>
    );
}

export default function Dashboard({ kpis, maestros, pedidos, tendencia, alertaRecuperacion, urls }: PaginaDashboard) {
    const { puede } = usePermisos();
    const { tasaBcv } = usePage().props;
    const [alertaVisible, setAlertaVisible] = useState(Boolean(alertaRecuperacion));
    const totalPedidos = pedidos.reduce((s, p) => s + p.total, 0);

    const donut = useMemo<Omit<AgChartOptions, 'theme'>>(() => {
        const datos = pedidos.filter((p) => p.total > 0);
        return {
            data: datos,
            series: [{
                type: 'donut', angleKey: 'total', calloutLabelKey: 'estado', sectorLabelKey: 'total', innerRadiusRatio: 0.62, cornerRadius: 4,
                fills: datos.map((d) => COLOR_PEDIDO[d.estado] ?? '#74788d'),
                innerLabels: [{ text: String(totalPedidos), fontSize: 26, fontWeight: 'bold' }, { text: 'pedidos', fontSize: 12, spacing: 4 }],
            }],
            legend: { position: 'bottom' },
        };
    }, [pedidos, totalPedidos]);

    const area = useMemo<Omit<AgChartOptions, 'theme'>>(() => ({
        data: tendencia,
        series: [{
            type: 'area', xKey: 'mes', yKey: 'pedidos', yName: 'Pedidos', fill: '#405189', fillOpacity: 0.25, stroke: '#405189', strokeWidth: 2, marker: { enabled: true },
            tooltip: {
                renderer: ({ datum }: { datum: { mes: string; pedidos: number; monto: number } }) => ({
                    heading: '',
                    title: datum.mes,
                    data: [
                        { label: 'Pedidos', value: String(datum.pedidos) },
                        { label: 'Monto', value: tasaBcv ? `${formatoUsd(datum.monto)} · ${formatoBs(datum.monto * tasaBcv.valor)}` : formatoUsd(datum.monto) },
                    ],
                }),
            },
        }],
        axes: { x: { type: 'category', position: 'bottom' }, y: { type: 'number', position: 'left', title: { text: 'Pedidos' } } },
    }) as Omit<AgChartOptions, 'theme'>, [tendencia, tasaBcv]);

    const r = alertaRecuperacion;
    const exito = r?.resultado === 'exito';

    return (
        <AppLayout titulo="Inicio">
            <div className="grid grid-cols-1 gap-4">
                {r && alertaVisible && (
                    <div role="alert" className={cn('flex items-start gap-3 rounded-lg border p-4 text-sm', exito ? 'border-success/30 bg-success/8' : 'border-warning/30 bg-warning/10')}>
                        {exito ? <ShieldCheck className="text-success mt-0.5 size-5 shrink-0" /> : <ShieldAlert className="text-warning mt-0.5 size-5 shrink-0" />}
                        <div className="flex-1">
                            <p className="font-medium">{exito ? 'Recuperación de contraseña exitosa' : 'Intento de recuperación detectado'}</p>
                            <p className="text-muted-foreground">
                                {r.fecha} · {r.tipo === 'preguntas' ? 'Preguntas de seguridad' : 'Correo electrónico'}{r.ip && <> · IP <code>{r.ip}</code></>}
                            </p>
                            <p className="text-muted-foreground">Si no fuiste tú, contacta al administrador de inmediato.</p>
                        </div>
                        <Button variant="ghost" size="icon" onClick={() => setAlertaVisible(false)} aria-label="Cerrar aviso"><X /></Button>
                    </div>
                )}

                <section className="grid gap-4 md:grid-cols-3" aria-label="Pendientes">
                    <Enlace url={urls.pedidos} blade permitido={puede('pedidos.ver')} className="group">
                        <Kpi titulo="Entregas esta semana" valor={kpis.por_entregar} detalle="pedidos por entregar (7 días)" icono={<Truck />} tono="warning" />
                    </Enlace>
                    <Enlace url={urls.alertas} permitido={puede('movimiento-insumo.ver')} className="group">
                        <Kpi titulo="Insumos en alerta" valor={kpis.insumos_alerta} detalle="en o bajo su existencia mínima" icono={<AlertTriangle />} tono="destructive" />
                    </Enlace>
                    <Enlace url={urls.cotizaciones} blade permitido={puede('cotizaciones.ver')} className="group">
                        <Kpi titulo="Cotizaciones por vencer" valor={kpis.cotizaciones_por_vencer} detalle="validez en 7 días o menos" icono={<FileClock />} tono="sky" />
                    </Enlace>
                </section>

                <PanelOrdenable
                    clave="sgpmrja-dashboard-layout"
                    widgets={[
                        { id: 'estados', titulo: 'Estado de pedidos', render: (agarre) => <TarjetaGrafico titulo="Estado de pedidos" archivo="estado-de-pedidos" opciones={donut} vacio={totalPedidos === 0} agarre={agarre} /> },
                        { id: 'tendencia', titulo: 'Pedidos por mes', render: (agarre) => <TarjetaGrafico titulo="Pedidos por mes (últimos 6)" archivo="pedidos-por-mes" opciones={area} vacio={!tendencia.some((t) => t.pedidos > 0)} agarre={agarre} /> },
                    ]}
                />

                <Card className="py-4">
                    <CardContent className="grid grid-cols-2 gap-4 md:grid-cols-4">
                        {([
                            ['clientes', 'Clientes', <UserRound key="c" />, 'clientes.ver'],
                            ['productos', 'Productos', <Shirt key="p" />, 'productos.ver'],
                            ['empleados', 'Empleados', <UserCog key="e" />, 'empleados.ver'],
                            ['proveedores', 'Proveedores', <Truck key="v" />, 'proveedores.ver'],
                        ] as const).map(([clave, etiqueta, icono, permiso]) => (
                            <Enlace key={clave} url={urls[clave]} permitido={puede(permiso)} className="hover:bg-muted/50 group grid justify-items-center gap-1 rounded-md p-2 text-center">
                                <span className="text-primary [&_svg]:size-5">{icono}</span>
                                <span className="text-xl font-semibold tabular">{formatoNumero(maestros[clave])}</span>
                                <span className="text-muted-foreground flex items-center gap-1 text-xs uppercase">{etiqueta}{puede(permiso) && <ArrowRight className="size-3 opacity-0 transition-opacity group-hover:opacity-100" />}</span>
                            </Enlace>
                        ))}
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
}
