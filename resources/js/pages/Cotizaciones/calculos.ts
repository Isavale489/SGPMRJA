import type { Bloque, BordadoLinea, CotizacionFila, GrupoCotizacion } from './tipos';

/** Redondeo a 2 decimales como PHP round() (la mitad se aleja del cero). */
export const redondear = (n: number) => {
    const x = Math.abs(n) * 100;
    return (Math.sign(n) * Math.round(x + x * Number.EPSILON * 4)) / 100;
};

/** Recargo del bordado por unidad: Σ precio × cantidad (BordadoPricingService). */
export const recargo = (bordados: BordadoLinea[]) => bordados.reduce((s, b) => s + b.precio_aplicado * Math.max(1, b.cantidad), 0);

/** Bordados por prenda: la SUMA de cantidades (lo que se compara con el máximo). */
export const bordadosPorPrenda = (bordados: Pick<BordadoLinea, 'cantidad'>[]) => bordados.reduce((s, b) => s + Math.max(1, b.cantidad), 0);

export const unidades = (b: Bloque) => b.tallas.reduce((s, t) => s + t.cantidad, 0);

export const precioFinal = (b: Bloque) => b.precio + recargo(b.bordados);

export const subtotalBloque = (b: Bloque) => unidades(b) * precioFinal(b);

/** Totales de la cotización: el IVA se calcula sobre el subtotal (como la vista y el PDF). */
export function totales(bloques: Bloque[], iva: number) {
    const subtotal = redondear(bloques.reduce((s, b) => s + subtotalBloque(b), 0));
    const montoIva = redondear((subtotal * iva) / 100);
    return { subtotal, iva: montoIva, total: redondear(subtotal + montoIva) };
}

let secuencia = 0;
export const nuevoId = () => `b${++secuencia}`;

/** Un grupo guardado (al editar) → bloque del asistente. */
export function bloqueDesdeGrupo(g: GrupoCotizacion): Bloque {
    return {
        id: nuevoId(),
        producto_id: g.producto_id,
        tipo_producto_id: g.tipo_producto_id,
        insumo_tela_id: g.insumo_tela_id,
        atributo_valor_ids: g.atributo_valor_ids,
        nombre: g.nombre,
        codigo: g.codigo,
        variante: g.variante,
        imagen: g.imagen,
        color_id: g.color?.id ?? null,
        precio: g.precio_base,
        precio_catalogo: g.precio_catalogo,
        bordados: g.bordados,
        tallas: g.tallas.map((t) => ({ talla_id: t.talla_id, genero_id: t.genero_id, cantidad: t.cantidad, descripcion: t.descripcion })),
    };
}

/** Bloques → `productos` del request (una línea por talla × género). */
export function lineasDe(bloques: Bloque[]) {
    return bloques.flatMap((b) =>
        b.tallas
            .filter((t) => t.cantidad > 0)
            .map((t) => ({
                ...(b.producto_id ? { producto_id: b.producto_id } : { tipo_producto_id: b.tipo_producto_id }),
                insumo_tela_id: b.producto_id ? null : b.insumo_tela_id,
                atributo_valor_ids: b.producto_id ? [] : b.atributo_valor_ids,
                color_id: b.color_id,
                talla_id: t.talla_id,
                genero_id: t.genero_id,
                cantidad: t.cantidad,
                precio_unitario: b.precio,
                descripcion: t.descripcion,
                lleva_bordado: b.bordados.length > 0 ? 1 : 0,
                // Sin bordado no se envía la lista (el servidor la ignora igual).
                ...(b.bordados.length
                    ? {
                          bordados: b.bordados.map((x, orden) => ({
                              ubicacion_bordado_id: x.ubicacion_bordado_id,
                              nombre_aplicado: x.nombre_aplicado,
                              logo_id: x.logo_id,
                              nombre_logo_aplicado: x.logo,
                              es_personalizada: x.es_personalizada ? 1 : 0,
                              precio_aplicado: x.precio_aplicado,
                              cantidad: Math.max(1, x.cantidad),
                              orden,
                          })),
                      }
                    : {}),
            })),
    );
}

/** Qué acciones ofrece cada fila (el servidor vuelve a validar cada una). */
export function accionesDe(c: Pick<CotizacionFila, 'estado'> & Partial<Pick<CotizacionFila, 'cliente_inhabilitado'>>, puede: (permiso: string) => boolean) {
    const editable = c.estado === 'Pendiente' || c.estado === 'Aprobada';
    const convertir = puede('cotizaciones.convertir');
    return {
        pdf: puede('cotizaciones.pdf'),
        editar: editable && puede('cotizaciones.gestionar'),
        eliminar: editable && puede('cotizaciones.gestionar'),
        aprobar: convertir && c.estado === 'Pendiente',
        pendiente: convertir && (c.estado === 'Aprobada' || c.estado === 'Cancelada'),
        cancelar: convertir && editable,
        reactivar: convertir && c.estado === 'Vencida',
        // Crear el pedido pide además gestionar pedidos (pedidos.create).
        // Sin cliente activo no hay pedido.
        convertirPedido: convertir && puede('pedidos.gestionar') && c.estado === 'Aprobada' && !c.cliente_inhabilitado,
    };
}
