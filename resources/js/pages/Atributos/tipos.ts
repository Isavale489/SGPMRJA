/**
 * Espejo de AtributoController::index(). AtributoPaginaTest falla si el
 * servidor manda una clave de más o de menos: al cambiar uno, cambia el otro.
 */
export interface AtributoFila {
    id: number;
    nombre: string;
    codigo: string;
    descripcion: string | null;
    valores: number;
    tipos_producto_ids: number[];
}

export interface ValorFila {
    id: number;
    nombre: string;
    codigo: string;
    orden: number;
    /** Productos (variantes) que usan el valor: si hay alguno, no se puede eliminar. */
    productos: number;
}

export interface Opcion {
    id: number;
    nombre: string;
}

export interface PaginaAtributos {
    atributos: AtributoFila[];
    seleccionado: number | null;
    valores: ValorFila[];
    tiposProducto: Opcion[];
    urls: { index: string };
}
