import type { Seccion } from '@/types';

/** Espejos de ReportesController (los verifica ReportesPaginaTest). */

export interface ReporteHub {
    titulo: string;
    descripcion: string;
    icono: string;
    ruta: string;
    formato: 'pdf' | 'vista';
    url: string;
}

export interface GrupoHub {
    titulo: string;
    descripcion: string;
    icono: string;
    /** Sección de origen (config/secciones.php): da el color del grupo. */
    seccion: Seccion;
    reportes: ReporteHub[];
}

export interface KpisHub {
    pedidos_mes: number;
    cotizaciones_mes: number;
    ordenes_activas: number;
    insumos_criticos: number;
}

export interface MesProduccion {
    anio: number;
    mes: number;
    mes_nombre: string;
    producido: number;
    defectuoso: number;
    eficiencia: number | null;
}

export interface OrdenEficiencia {
    orden_id: number;
    producto: string;
    estado: string;
    solicitado: number;
    producido: number;
    defectuoso: number;
    eficiencia: number | null;
}

export interface PedidoEficiencia {
    pedido_id: number | null;
    cliente: string;
    total_ordenes: number;
    solicitado: number;
    producido: number;
    defectuoso: number;
    eficiencia: number | null;
    ordenes: OrdenEficiencia[];
}

export interface KpisEficiencia {
    eficiencia_global: number | null;
    producido: number;
    defectuoso: number;
    pedidos_total: number;
    pedidos_produccion: number;
}

export interface ConsumoInsumo {
    id: number;
    nombre: string;
    tipo: string;
    unidad: string;
    total: number;
    ordenes: number;
}

export interface RendimientoEmpleado {
    empleado_id: number;
    nombre: string;
    total_ordenes: number;
    total_asignado: number;
    total_producido: number;
    total_defectuoso: number;
    eficiencia: number | null;
}

export type Nivel = 'ok' | 'warn' | 'bad' | 'na';
export const nivel = (v: number | null): Nivel => (v === null ? 'na' : v >= 90 ? 'ok' : v >= 70 ? 'warn' : 'bad');
export const NIVEL: Record<Nivel, { etiqueta: string; clase: string }> = {
    ok: { etiqueta: 'Alta', clase: 'bg-success/12 text-success ring-success/25' },
    warn: { etiqueta: 'Media', clase: 'bg-info/12 text-info ring-info/25' },
    bad: { etiqueta: 'Baja', clase: 'bg-destructive/10 text-destructive ring-destructive/25' },
    na: { etiqueta: 'Sin producción', clase: 'bg-muted text-muted-foreground ring-border' },
};
