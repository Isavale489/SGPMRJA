import { FileDown } from 'lucide-react';

import { Monto } from '@/components/app/monto';
import { PaginaCatalogo } from '@/components/app/pagina-catalogo';
import { Button } from '@/components/ui/button';
import { formatoNumero } from '@/lib/formato';
import type { Paginado } from '@/types';

import { FormularioTipo } from './formulario-tipo';

/** Espejo de ProductoController::index() (lo verifica ProductosPaginaTest). */
export interface TipoProductoFila {
    id: number;
    nombre: string;
    prefijo: string;
    descripcion: string | null;
    imagen: string | null;
    precio_confeccion: number;
    requiere_tela: boolean;
    requiere_produccion: boolean;
    consumo_tela_por_unidad: number;
    atributos: { id: number; nombre: string; codigo: string; orden: number }[];
    telas: { id: number; nombre: string; codigo: string | null }[];
    insumos: { id: number; nombre: string; unidad: string; cantidad: number }[];
    productos: number;
    inhabilitado: boolean;
}

export interface CatalogoTipos {
    atributos: { id: number; nombre: string; codigo: string; valores: number }[];
    telas: { id: number; nombre: string; codigo: string | null }[];
    insumos: { id: number; nombre: string; unidad: string }[];
}

interface Props {
    registros: Paginado<TipoProductoFila>;
    filtros: { buscar?: string; historial?: string };
    catalogo: CatalogoTipos;
    urls: { index: string; tipos: string; reportePdf: string };
}

function Imagen({ tipo }: { tipo: TipoProductoFila }) {
    return tipo.imagen ? (
        <img src={tipo.imagen} alt="" className="border-border size-10 shrink-0 rounded-md border object-cover" />
    ) : (
        <span className="bg-secondary text-secondary-foreground grid size-10 shrink-0 place-items-center rounded-md font-mono text-xs">{tipo.prefijo}</span>
    );
}

export default function ProductosIndex({ registros, filtros, catalogo, urls }: Props) {
    return (
        <PaginaCatalogo
            titulo="Catálogo de productos"
            recurso="tipo de producto"
            permiso="tipo-productos.gestionar"
            registros={registros}
            filtros={filtros}
            url={urls.index}
            urlMutaciones={urls.tipos}
            avisoInhabilitar="Solo se puede inhabilitar si no tiene productos registrados. Deja de ofrecerse en cotizaciones nuevas; los documentos existentes conservan su snapshot."
            accionesExtra={
                <Button variant="outline" asChild>
                    <a href={`${urls.reportePdf}${filtros.historial ? '?historial=1' : ''}`} target="_blank" rel="noopener">
                        <FileDown /> Exportar PDF
                    </a>
                </Button>
            }
            columnas={[
                {
                    id: 'tipo',
                    encabezado: 'Tipo',
                    celda: (t) => (
                        <span className="flex items-center gap-3">
                            <Imagen tipo={t} />
                            <span className="grid">
                                <span className="font-medium">{t.nombre}</span>
                                <span className="text-muted-foreground text-xs">
                                    {t.requiere_produccion ? 'Se fabrica' : 'Reventa'}
                                    {!t.requiere_tela
                                        ? ' · Sin tela'
                                        : t.consumo_tela_por_unidad > 0
                                          ? ` · ${formatoNumero(t.consumo_tela_por_unidad)} de tela por unidad`
                                          : ' · Lleva tela (consumo sin definir)'}
                                </span>
                            </span>
                        </span>
                    ),
                },
                { id: 'prefijo', encabezado: 'Prefijo', celda: (t) => <code className="font-mono text-xs">{t.prefijo}</code> },
                { id: 'precio', encabezado: 'Confección', celda: (t) => <Monto usd={t.precio_confeccion} /> },
                {
                    id: 'telas',
                    encabezado: 'Telas',
                    celda: (t) =>
                        t.telas.length ? (
                            <span className="text-muted-foreground text-sm" title={t.telas.map((x) => x.nombre).join(', ')}>
                                {t.telas.length} {t.telas.length === 1 ? 'tela' : 'telas'}
                            </span>
                        ) : (
                            <span className="text-muted-foreground">—</span>
                        ),
                },
                {
                    id: 'atributos',
                    encabezado: 'Atributos',
                    celda: (t) =>
                        t.atributos.length ? (
                            <span className="flex flex-wrap gap-1">
                                {t.atributos.map((a) => (
                                    <span key={a.id} className="bg-secondary text-secondary-foreground rounded px-1.5 py-0.5 text-xs">{a.nombre}</span>
                                ))}
                            </span>
                        ) : (
                            <span className="text-muted-foreground">—</span>
                        ),
                },
            ]}
            formulario={(p) => <FormularioTipo key={p.apertura} {...p} catalogo={catalogo} url={urls.tipos} />}
        />
    );
}
