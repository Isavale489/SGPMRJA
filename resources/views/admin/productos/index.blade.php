@extends('admin.layouts.app')

@push('styles')
    <!-- Sweet Alert css-->
    <link href="{{ URL::asset('assets/libs/sweetalert2/sweetalert2.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" rel="stylesheet" type="text/css" />
    <link href="https://cdn.datatables.net/responsive/2.2.9/css/responsive.bootstrap.min.css" rel="stylesheet"
        type="text/css" />
    <link href="https://cdn.datatables.net/buttons/2.2.2/css/buttons.dataTables.min.css" rel="stylesheet" type="text/css" />
    {{-- Grid responsivo para filtros: 1 col mobile → 4 cols desktop --}}
    <style>
        @media (min-width: 768px) {
            .navy-filter-grid {
                grid-template-columns: repeat(2, 1fr) !important;
            }
        }
    </style>
@endpush

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0">Gestión de Productos</h4>

                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="javascript: void(0);">Gestión General</a></li>
                        <li class="breadcrumb-item active">Productos</li>
                    </ol>
                </div>

            </div>
        </div>
    </div>

    {{-- Estilos en public/assets/css/custom.css — sección "MÓDULO MAESTROS — Productos" --}}
    <div class="row">
        <div class="col-lg-12">
            <div class="card card-maestros">
                <div class="card-header">
                    <div class="d-flex align-items-center">
                        <h5 class="card-title mb-0 flex-grow-1">Catálogo de Productos (Tipos)</h5>
                        <div class="flex-shrink-0 d-flex align-items-center gap-3">
                            <!-- Toggle Historial -->
                            @if($historial)
                                <a href="{{ route('productos.index') }}" class="btn-historial btn-historial-volver">
                                    <i class="ri-arrow-left-line"></i> Solo Activos
                                </a>
                            @else
                                <a href="{{ route('productos.index', ['historial' => true]) }}" class="btn-historial btn-historial-ver">
                                    <i class="ri-archive-line"></i> Inhabilitados
                                </a>
                            @endif
                            <div class="d-flex gap-2 align-items-center">
                                <a href="{{ route('productos.reporte.pdf', $historial ? ['historial' => true] : []) }}"
                                    class="btn btn-danger" target="_blank">
                                    <i class="ri-file-pdf-fill align-bottom me-1"></i> Exportar PDF
                                </a>
                                @if(!$historial)
                                <button type="button" class="btn btn-success add-btn" data-bs-toggle="modal"
                                    id="create-btn" data-bs-target="#addTipoModal">
                                    <i class="ri-add-line align-bottom me-1"></i> Agregar Tipo
                                </button>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    {{-- FEAT-003: el catálogo se define por Tipo de Producto (sus telas y
                         atributos). Las combinaciones se configuran al vuelo en la cotización
                         y NO generan filas acá. Esta tabla lista los SKUs individuales
                         (productos legacy o creados explícitamente). --}}
                    <div class="alert alert-info d-flex align-items-start gap-2 py-2 px-3 mb-3" role="alert">
                        <i class="ri-information-line fs-5 lh-1 mt-1"></i>
                        <div class="small">
                            El <strong>catálogo se define por Tipo de Producto</strong>: cada tipo lleva sus
                            <strong>telas permitidas</strong>, sus <strong>atributos</strong> (manga, cuello…) y su
                            precio de confección. Al <strong>cotizar</strong>, el cliente elige la tela y las
                            variaciones que requiere — <strong>sin crear un producto por combinación</strong>.
                        </div>
                    </div>
                    {{-- ============================================================
                         FILTROS — Patrón Maestro S-07 (Colapsable)
                         Filtros server-side: ajax.reload() con param filter_tipo_producto_id.
                         CSS genérico en custom.css: .navy-filter-*
                         ============================================================ --}}
                    <div class="advanced-filters-wrapper navy-theme" id="advanced-filters">
                        {{-- Header unificado: búsqueda global + trigger de filtros --}}
                        <div class="navy-filter-header is-collapsed">
                            {{-- Búsqueda global (siempre visible) --}}
                            <div class="navy-header-search">
                                <i class="ri-search-line"></i>
                                <input type="text" id="custom-search-input"
                                    class="navy-search-input"
                                    placeholder="Buscar tipo de producto..."
                                    autocomplete="off">
                            </div>
                            <div class="navy-header-divider"></div>
                            <button class="navy-filter-btn collapsed" type="button"
                                data-bs-toggle="collapse" data-bs-target="#filters-collapse-body"
                                aria-expanded="false" aria-controls="filters-collapse-body">
                                <i class="ri-filter-3-line"></i>
                                <span>Filtros</span>
                                <span class="navy-filter-badge d-none" id="active-filter-count"></span>
                                <i class="ri-arrow-down-s-line navy-filter-chevron"></i>
                            </button>
                        </div>
                        {{-- Body: colapsable, oculto (filtros de producto no aplican a Tipos) --}}
                        <div class="collapse" id="filters-collapse-body">
                            <div class="navy-filter-body">
                                <div style="display: grid; grid-template-columns: 1fr; gap: 0.75rem;" class="navy-filter-grid">
                                    {{-- Filtro 1: Producción (fabricado vs reventa) --}}
                                    <div>
                                        <label class="navy-filter-label" for="filter-produccion">
                                            <i class="ri-hammer-line"></i> Producción
                                        </label>
                                        <select class="form-select navy-filter-select" id="filter-produccion">
                                            <option value="">Todos</option>
                                            <option value="1">Fabricado</option>
                                            <option value="0">Reventa</option>
                                        </select>
                                    </div>
                                    {{-- Filtro 2: ¿Requiere tela? --}}
                                    <div>
                                        <label class="navy-filter-label" for="filter-requiere-tela">
                                            <i class="ri-shirt-line"></i> ¿Requiere tela?
                                        </label>
                                        <select class="form-select navy-filter-select" id="filter-requiere-tela">
                                            <option value="">Todas</option>
                                            <option value="1">Sí</option>
                                            <option value="0">No</option>
                                        </select>
                                    </div>
                                </div>
                                {{-- Botón limpiar: estilo ghost con icono de escoba --}}
                                <div class="d-flex justify-content-end mt-2">
                                    <button type="button" class="btn btn-sm" id="btn-clear-filters"
                                        style="background: transparent; color: #8a9bb5; border: none; font-size: 0.8rem; transition: all 0.2s ease;"
                                        onmouseover="this.style.color='#ef4444'; this.style.textDecoration='underline';"
                                        onmouseout="this.style.color='#8a9bb5'; this.style.textDecoration='none';">
                                        <i class='bx bx-broom' style="margin-right: 4px; font-size: 1rem; vertical-align: middle;"></i>Limpiar filtros
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    {{-- FIN FILTROS --}}

                    <table id="productos-table" class="table table-bordered table-striped table-sm align-middle table-operativa table-maestro">
                        <thead>
                            <tr>
                                <th>Imagen</th>
                                <th>Tipo</th>
                                <th>Prefijo</th>
                                <th>Precio Confección</th>
                                <th>Telas</th>
                                <th>Atributos</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para agregar/editar Tipo de Producto -->
    <div class="modal fade atlantico-modal" id="addTipoModal" tabindex="-1" aria-hidden="true"
        data-guard-id-field="tipo-id-field">
        <div class="modal-dialog modal-dialog-centered modal-xl">
            <div class="modal-content">
                <div class="modal-header bg-light p-3">
                    <h5 class="modal-title" id="tipoModalTitle">Agregar Tipo de Producto
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="tipoForm" novalidate>
                    <div class="modal-body">
                        <input type="hidden" id="tipo-id-field" />
                        <div class="row g-4">
                        <div class="col-lg-6">
                        <div class="modal-form-section mb-0">
                            <div class="modal-form-section-title"><i class="ri-list-settings-line"></i>Datos del Tipo de
                                Producto</div>

                            <div class="mb-3">
                                <label for="tipo-nombre-field" class="form-label required">Nombre del Tipo</label>
                                <input type="text" id="tipo-nombre-field" name="nombre" class="form-control"
                                    placeholder="Ej: Chemise, Franela, Pantalón" required />
                                <div id="tipo-nombre-error" class="invalid-feedback"></div>
                                <small class="text-muted d-block">Categoría de prenda del catálogo. Agrupa todas las variantes que comparten telas y atributos.</small>
                            </div>
                            <div class="mb-3">
                                <label for="tipo-prefijo-field" class="form-label required">Prefijo de Código</label>
                                <input type="text" id="tipo-prefijo-field" name="prefijo" class="form-control"
                                    placeholder="Ej: CHM, FRN, PNT (máx 5 letras)" maxlength="5" required
                                    style="text-transform: uppercase;" />
                                <div id="tipo-prefijo-error" class="invalid-feedback"></div>
                                <small class="text-muted">2–5 letras que identifican al tipo en sus códigos (CHM → CHM-001). Se sugiere desde el nombre; evita cambiarlo después de crear productos.</small>
                            </div>
                            <div class="mb-3">
                                <label for="tipo-descripcion-field" class="form-label">Descripción
                                    <span class="text-muted small">(opcional)</span></label>
                                <textarea id="tipo-descripcion-field" name="descripcion" class="form-control" rows="2"
                                    placeholder="Notas internas del tipo (uso, detalles)…"></textarea>
                                <div id="tipo-descripcion-error" class="invalid-feedback"></div>
                            </div>

                            <div class="mb-3">
                                <label for="tipo-imagen-field" class="form-label">Imagen del catálogo
                                    <span class="text-muted small">(opcional)</span></label>
                                <input type="file" id="tipo-imagen-field" name="imagen" class="form-control"
                                    accept="image/*" />
                                <small class="text-muted d-block mt-1">Foto que se mostrará al navegar el catálogo en la cotización.</small>
                                <div id="tipo-imagen-preview" class="mt-2 text-center" style="display:none;">
                                    <img src="" alt="Vista previa" class="img-fluid rounded" style="max-width: 160px;">
                                </div>
                            </div>

                            <div class="row g-2 mb-0">
                                <div class="col-md-7">
                                    <label for="tipo-precio-confeccion" class="form-label">Precio de Confección</label>
                                    <div class="input-group">
                                        <span class="input-group-text">$</span>
                                        <input type="number" id="tipo-precio-confeccion" name="precio_confeccion"
                                            class="form-control" step="0.01" min="0" max="99999.99" placeholder="0.00" />
                                    </div>
                                    <small class="text-muted">Mano de obra + insumos secundarios. Se suma al precio de la tela para sugerir el precio final del producto.</small>
                                </div>
                                <div class="col-md-5 d-flex flex-column justify-content-end gap-2">
                                    <div class="form-check form-switch w-100">
                                        <input class="form-check-input" type="checkbox" id="tipo-requiere-tela" name="requiere_tela" checked>
                                        <label class="form-check-label" for="tipo-requiere-tela">
                                            Requiere tela
                                        </label>
                                        <div class="text-muted small">Actívalo si se confecciona con tela. Desactívalo para tipos sin tela (gorra, accesorio, servicio).</div>
                                    </div>
                                    <div class="form-check form-switch w-100">
                                        <input class="form-check-input" type="checkbox" id="tipo-requiere-produccion" name="requiere_produccion" checked>
                                        <label class="form-check-label" for="tipo-requiere-produccion">
                                            Requiere producción
                                        </label>
                                        <div class="text-muted small">Actívalo si se fabrica. Desactívalo para productos de reventa (se venden pero no entran a Órdenes de Producción).</div>
                                    </div>
                                </div>
                            </div>

                            {{-- Consumo de tela por unidad (visible solo si requiere tela) --}}
                            <div class="row g-2 mt-2 mb-0" id="tipo-consumo-tela-row">
                                <div class="col-md-7">
                                    <label for="tipo-consumo-tela" class="form-label">Consumo de tela por unidad</label>
                                    <div class="input-group">
                                        <input type="number" id="tipo-consumo-tela" name="consumo_tela_por_unidad"
                                            class="form-control" step="0.01" min="0" max="9999.99" placeholder="0.00" />
                                        <span class="input-group-text">por unidad</span>
                                    </div>
                                    <small class="text-muted">
                                        Cantidad de tela por cada unidad producida (ej: 2 m para una camisa).
                                        Al crear la orden se prellena con (consumo × cantidad), usando la tela específica del producto.
                                    </small>
                                </div>
                            </div>
                        </div>{{-- /modal-form-section Datos+Costos --}}
                        </div>{{-- /col-lg-6 izquierda --}}

                        <div class="col-lg-6">
                            <div class="modal-form-section mb-0">
                            {{-- Telas permitidas para este tipo (FEAT-003) — visible si requiere tela --}}
                            <div class="mt-0" id="tipo-telas-section">
                                <div class="d-flex align-items-center justify-content-between mb-1">
                                    <label class="form-label mb-0"><i class="ri-shirt-line me-1"></i>Telas permitidas</label>
                                    <span class="badge bg-light text-dark border" id="tipo-telas-count">0 seleccionadas</span>
                                </div>
                                <p class="text-muted small mb-2">
                                    Marca las telas en las que se puede confeccionar este tipo. Son las que ofrecerá el
                                    selector de variante en la cotización (no hace falta crear un producto por combinación).
                                </p>
                                @if(!$telasDisponibles->isEmpty())
                                    <input type="text" class="form-control form-control-sm mb-2" id="tipo-telas-search"
                                        placeholder="Buscar tela…" autocomplete="off">
                                @endif
                                <div class="tipo-check-scroll">
                                    <div class="row g-1" id="tipo-telas-list">
                                        @foreach($telasDisponibles as $tela)
                                            <div class="col-md-6 tipo-tela-item"
                                                data-search="{{ strtolower($tela->nombre . ' ' . $tela->codigo) }}">
                                                <div class="form-check">
                                                    <input class="form-check-input tipo-tela-check" type="checkbox"
                                                        value="{{ $tela->id }}" id="tipo-tela-{{ $tela->id }}">
                                                    <label class="form-check-label" for="tipo-tela-{{ $tela->id }}">
                                                        {{ $tela->nombre }}@if($tela->codigo) <span class="text-muted">[{{ $tela->codigo }}]</span>@endif
                                                    </label>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                    <div class="text-muted small text-center py-2 d-none" id="tipo-telas-noresult">
                                        <i class="ri-search-line me-1"></i>Sin coincidencias.
                                    </div>
                                    @if($telasDisponibles->isEmpty())
                                        <div class="text-muted small"><i class="ri-information-line me-1"></i>No hay telas registradas (insumos tipo Tela).</div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        {{-- Sección: Atributos de confección asociados --}}
                        <div class="modal-form-section mb-0 mt-3">
                            <div class="d-flex align-items-center justify-content-between">
                                <div class="modal-form-section-title mb-0">
                                    <i class="ri-list-check-2"></i>Atributos de confección
                                </div>
                                <span class="badge bg-light text-dark border" id="tipo-atributos-count">0 seleccionados</span>
                            </div>
                            <p class="text-muted small mb-2 mt-2">
                                Selecciona qué variaciones definen una variante de este tipo (ej: Manga, Cuello).
                                El <strong>orden</strong> define cómo se concatenan en el código del producto.
                            </p>
                            <input type="text" class="form-control form-control-sm mb-2" id="tipo-atributos-search"
                                placeholder="Buscar atributo…" autocomplete="off">
                            <div class="tipo-check-scroll">
                                <div id="tipo-atributos-list" class="d-flex flex-column gap-2">
                                    {{-- Render dinámico vía JS --}}
                                    <div class="text-center text-muted py-2" id="tipo-atributos-empty">
                                        <span class="spinner-border spinner-border-sm me-2"></span>Cargando atributos…
                                    </div>
                                </div>
                                <div class="text-muted small text-center py-2 d-none" id="tipo-atributos-noresult">
                                    <i class="ri-search-line me-1"></i>Sin coincidencias.
                                </div>
                            </div>
                        </div>

                        {{-- Sección: Insumos por defecto (template para órdenes de producción) --}}
                        <div class="modal-form-section mb-0 mt-3">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <div class="modal-form-section-title mb-0">
                                    <i class="ri-tools-line"></i>Insumos por defecto
                                </div>
                                <button type="button" class="btn btn-sm btn-soft-primary py-0 px-2" id="tipo-insumo-add-btn">
                                    <i class="ri-add-line"></i> Agregar insumo
                                </button>
                            </div>
                            <p class="text-muted small mb-2">
                                Materiales <strong>fijos</strong> que lleva todo producto de este tipo —
                                <strong>hilo, botón, cierre, etiqueta, broche…</strong> — y cuánto se consume
                                <strong>por cada unidad producida</strong> (ej: 8 botones por camisa, 1 etiqueta por chemise).
                                Al crear una orden de producción se prellenan automáticamente (consumo × cantidad).
                            </p>
                            <div class="alert alert-light border d-flex align-items-start gap-2 py-2 px-2 mb-2 small" role="alert">
                                <i class="ri-information-line mt-1"></i>
                                <div>
                                    <strong>Aquí NO van telas</strong> (se eligen al cotizar y varían por variante).
                                    Solo se listan insumos <strong>activos</strong>; si falta alguno, regístralo en
                                    <a href="{{ url('insumos') }}" target="_blank">Insumos</a>.
                                </div>
                            </div>
                            <div id="tipo-insumos-list" class="d-flex flex-column gap-1">
                                {{-- Render dinámico vía JS --}}
                            </div>
                            <div id="tipo-insumos-empty" class="text-center text-muted py-2 small" style="display:none;">
                                <i class="ri-inbox-line me-1"></i>Sin insumos por defecto. Agrega los que se usan al producir.
                            </div>
                        </div>
                        </div>{{-- /col-lg-6 derecha --}}
                        </div>{{-- /row --}}
                    </div>
                    <div class="modal-footer bg-light border-0">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                            <i class="ri-close-line me-1"></i>Cancelar
                        </button>
                        <button type="submit" class="btn btn-success" id="save-tipo-btn">
                            <i class="ri-save-line me-1"></i>Guardar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- ═══════════ MODAL VER — Detalle del Tipo (solo lectura) ═══════════ --}}
    <div class="modal fade atlantico-modal" id="viewTipoModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="ri-eye-line me-2"></i>Detalle del Tipo</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body p-3 p-md-4 vt-body">
                    {{-- Hero: imagen + identidad --}}
                    <div class="vt-hero">
                        <div class="vt-hero-media">
                            <img id="vt-imagen" src="" alt="Imagen del tipo" class="vt-img">
                            <div id="vt-imagen-empty" class="vt-img-empty" hidden>
                                <i class="ri-image-line"></i><span>Sin imagen</span>
                            </div>
                        </div>
                        <div class="vt-hero-info">
                            <div class="vt-title-row">
                                <h3 class="vt-name" id="vt-nombre">—</h3>
                                <span class="vt-prefijo" id="vt-prefijo">—</span>
                                <span class="badge rounded-pill" id="vt-estatus">—</span>
                            </div>
                            <div id="vt-descripcion-wrap" class="vt-desc">
                                <span id="vt-descripcion">—</span>
                            </div>

                            {{-- Stat tiles --}}
                            <div class="vt-stats">
                                <div class="vt-stat">
                                    <span class="vt-stat-ic"><i class="ri-price-tag-3-line"></i></span>
                                    <div><span class="vt-stat-label">Precio confección</span>
                                        <span class="vt-stat-val" id="vt-precio">—</span></div>
                                </div>
                                <div class="vt-stat">
                                    <span class="vt-stat-ic"><i class="ri-hammer-line"></i></span>
                                    <div><span class="vt-stat-label">Producción</span>
                                        <span class="vt-stat-val" id="vt-produccion">—</span></div>
                                </div>
                                <div class="vt-stat">
                                    <span class="vt-stat-ic"><i class="ri-shirt-line"></i></span>
                                    <div><span class="vt-stat-label">¿Requiere tela?</span>
                                        <span class="vt-stat-val" id="vt-requiere-tela">—</span></div>
                                </div>
                                <div class="vt-stat" id="vt-consumo-wrap">
                                    <span class="vt-stat-ic"><i class="ri-ruler-line"></i></span>
                                    <div><span class="vt-stat-label">Consumo de tela / u</span>
                                        <span class="vt-stat-val" id="vt-consumo">—</span></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Secciones --}}
                    <div class="row g-3 mt-1">
                        <div class="col-md-6">
                            <div class="vt-section h-100">
                                <div class="vt-section-head"><i class="ri-shirt-line"></i>Telas permitidas <span class="vt-section-count" id="vt-telas-count"></span></div>
                                <div id="vt-telas" class="d-flex flex-wrap gap-1"></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="vt-section h-100">
                                <div class="vt-section-head"><i class="ri-list-settings-line"></i>Atributos <span class="vt-section-count" id="vt-atributos-count"></span></div>
                                <div id="vt-atributos" class="d-flex flex-wrap gap-1"></div>
                            </div>
                        </div>
                    </div>

                    <div class="vt-section mt-3">
                        <div class="vt-section-head"><i class="ri-tools-line"></i>Insumos por defecto <span class="vt-section-count" id="vt-insumos-count"></span></div>
                        <div id="vt-insumos-empty" class="text-muted small">Sin insumos por defecto.</div>
                        <div id="vt-insumos-wrap" hidden>
                            <table class="vt-ins-table">
                                <thead><tr><th>Insumo</th><th class="text-end">Cantidad</th></tr></thead>
                                <tbody id="vt-insumos"></tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light border-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal"><i class="ri-close-line me-1"></i>Cerrar</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.2.9/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.2/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.print.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.html5.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
    <script src="{{ URL::asset('assets/libs/sweetalert2/sweetalert2.min.js') }}"></script>

    <script>
        // Configurar pdfMake para evitar errores de fuentes
        if (typeof pdfMake !== 'undefined' && typeof pdfFonts !== 'undefined') {
            pdfMake.vfs = pdfFonts.pdfMake.vfs;
        }

        // Configuración alternativa para evitar errores de fuentes
        if (typeof pdfMake !== 'undefined') {
            pdfMake.fonts = {
                Roboto: {
                    normal: 'Roboto-Regular.ttf',
                    bold: 'Roboto-Medium.ttf',
                    italics: 'Roboto-Italic.ttf',
                    bolditalics: 'Roboto-MediumItalic.ttf'
                }
            };
        }

        $(document).ready(function () {
            var esHistorial = {{ $historial ? 'true' : 'false' }};

            // El catálogo se gestiona por Tipo de Producto. La tabla principal
            // lista TIPOS (client-side). Los botones reutilizan los handlers
            // .edit-tipo-btn / .delete-tipo-btn / .restore-tipo-btn (delegados a document).
            var table = $('#productos-table').DataTable({
                processing: true,
                autoWidth: false,
                ajax: {
                    url: "{{ route('tipo-productos.index') }}" + (esHistorial ? '?historial=true' : ''),
                    // Más reciente primero (por fecha de creación). El backend ordena por nombre,
                    // pero aquí mostramos el catálogo con los últimos tipos arriba.
                    dataSrc: function (json) {
                        return (json || []).sort(function (a, b) {
                            return String(b.created_at || '').localeCompare(String(a.created_at || ''));
                        });
                    }
                },
                columns: [
                    {
                        data: 'imagen_url',
                        orderable: false,
                        searchable: false,
                        render: function (data) {
                            return data
                                ? '<img src="' + data + '" alt="Imagen" class="img-thumbnail" width="44" style="height:44px; object-fit:cover;">'
                                : '<span class="text-muted small">Sin imagen</span>';
                        }
                    },
                    {
                        data: 'nombre',
                        render: function (data) {
                            return '<span class="badge badge-tipo badge-tipo-producto" title="' + data + '"><i class="ri-price-tag-3-line"></i> ' + data + '</span>';
                        }
                    },
                    {
                        data: 'prefijo',
                        render: function (data) {
                            return '<span class="badge bg-secondary">' + (data || '—') + '</span>';
                        }
                    },
                    {
                        data: 'precio_confeccion',
                        render: function (data) {
                            return '$ ' + parseFloat(data || 0).toFixed(2);
                        }
                    },
                    {
                        data: null,
                        orderable: false,
                        searchable: false,
                        render: function (data, type, row) {
                            if (!row.requiere_tela) {
                                return '<span class="text-muted small">No usa tela</span>';
                            }
                            var n = row.telas_count || 0;
                            var cls = n > 0 ? 'bg-light text-dark border' : 'bg-warning-subtle text-warning border border-warning';
                            return '<span class="badge ' + cls + '"><i class="ri-shirt-line me-1"></i>' + n + ' tela' + (n === 1 ? '' : 's') + '</span>';
                        }
                    },
                    {
                        data: 'atributos_count',
                        orderable: false,
                        searchable: false,
                        render: function (data) {
                            var n = data || 0;
                            return '<span class="badge bg-info">' + n + '</span>';
                        }
                    },
                    {
                        data: 'id',
                        orderable: false,
                        searchable: false,
                        render: function (data) {
                            var sVer = '<button class="btn btn-sm btn-soft-info view-tipo-btn" data-id="' + data + '" title="Ver"><i class="ri-eye-fill"></i></button>';
                            var items = esHistorial
                                ? '<li><button type="button" class="dropdown-item act-item act-restore restore-tipo-btn" data-id="' + data + '"><span class="act-ic"><i class="ri-arrow-go-back-line"></i></span>Habilitar</button></li>'
                                : '<li><button type="button" class="dropdown-item act-item act-edit edit-tipo-btn" data-id="' + data + '"><span class="act-ic"><i class="ri-pencil-fill"></i></span>Editar</button></li>' +
                                  '<li><button type="button" class="dropdown-item act-item act-warn delete-tipo-btn" data-id="' + data + '"><span class="act-ic"><i class="ri-forbid-line"></i></span>Inhabilitar</button></li>';
                            var menu = '<div class="dropdown d-inline-block">' +
                                '<button class="btn btn-sm btn-soft-secondary" type="button" data-bs-toggle="dropdown" aria-expanded="false" title="Más acciones"><i class="ri-more-2-fill"></i></button>' +
                                '<ul class="dropdown-menu dropdown-menu-end actions-menu">' + items + '</ul>' +
                                '</div>';
                            return '<div class="d-flex gap-1 justify-content-center align-items-center">' + sVer + menu + '</div>';
                        }
                    }
                ],
                order: [], // preserva el orden del servidor (más reciente primero)
                dom: 'rtip',
                language: lenguajeData
            });

            // ══════════════════════════════════════════════════════
            // BÚSQUEDA + FILTROS — Patrón Maestro S-07
            // Header unificado: búsqueda global + panel colapsable
            // Filtros: server-side (ajax.reload con filter_tipo_producto_id)
            // ══════════════════════════════════════════════════════

            // ── Badge: contador de filtros activos ──
            function updateFilterBadge() {
                var count = 0;
                if ($('#filter-produccion').val() !== '')     count++;
                if ($('#filter-requiere-tela').val() !== '')  count++;
                var $badge = $('#active-filter-count');
                if (count > 0) { $badge.text(count).removeClass('d-none'); }
                else           { $badge.addClass('d-none'); }
            }

            // ── Filtro client-side por Producción y Requiere tela (sobre #productos-table) ──
            $.fn.dataTable.ext.search.push(function (settings, searchData, dataIndex, rowData) {
                if (settings.nTable.id !== 'productos-table') return true;
                var prod = $('#filter-produccion').val();
                var tela = $('#filter-requiere-tela').val();
                if (prod !== '') {
                    var esFabricado = rowData.requiere_produccion !== false && rowData.requiere_produccion != 0;
                    if (prod === '1' && !esFabricado) return false;
                    if (prod === '0' &&  esFabricado) return false;
                }
                if (tela !== '') {
                    var reqTela = rowData.requiere_tela === true || rowData.requiere_tela == 1;
                    if (tela === '1' && !reqTela) return false;
                    if (tela === '0' &&  reqTela) return false;
                }
                return true;
            });

            // ── Sincronizar clase is-collapsed con el collapse ──
            $('#filters-collapse-body').on('show.bs.collapse', function () {
                $('.navy-filter-header').removeClass('is-collapsed');
            }).on('hidden.bs.collapse', function () {
                $('.navy-filter-header').addClass('is-collapsed');
            });

            // ── Búsqueda global (debounce 300ms) ──
            var searchTimeout = null;
            $('#custom-search-input').on('keyup', function () {
                clearTimeout(searchTimeout);
                var val = this.value;
                searchTimeout = setTimeout(function () {
                    table.search(val).draw();
                }, 300);
            });

            // ── Filtros de select: redibujar al cambiar (client-side) ──
            $('.navy-filter-select').on('change', function () {
                table.draw();
                updateFilterBadge();
            });

            // ── Botón limpiar: resetea búsqueda + filtros ──
            $('#btn-clear-filters').on('click', function () {
                $('#filter-produccion').val('');
                $('#filter-requiere-tela').val('');
                $('#custom-search-input').val('');
                updateFilterBadge();
                table.search('').draw();
            });

            // Sincronizar switch de estado con hidden input
            $("#estado-switch").on('change', function() {
                var isChecked = $(this).is(':checked');
                $("#estado-hidden-field").val(isChecked ? '1' : '0');
                $("#estado-label").text(isChecked ? 'Activo' : 'Inactivo');
            });

            // Vista previa de imagen
            $("#imagen-field").change(function () {
                if (this.files && this.files[0]) {
                    var reader = new FileReader();
                    reader.onload = function (e) {
                        $("#imagen-preview img").attr('src', e.target.result);
                        $("#imagen-preview").show();
                    }
                    reader.readAsDataURL(this.files[0]);
                }
            });

            function validarFormularioTipo() {
                let esValido = true;

                let $nombre = $('#tipo-nombre-field');
                let nombre = $nombre.val().trim();
                if (!nombre) {
                    marcarInvalido($nombre, 'El nombre del tipo es obligatorio.');
                    esValido = false;
                } else if (nombre.length < 2) {
                    marcarInvalido($nombre, 'El nombre debe tener al menos 2 caracteres.');
                    esValido = false;
                } else { marcarValido($nombre); }

                let $prefijo = $('#tipo-prefijo-field');
                let prefijo = $prefijo.val().trim();
                if (!prefijo) {
                    marcarInvalido($prefijo, 'El código prefijo es obligatorio.');
                    esValido = false;
                } else if (!/^[a-zA-Z]+$/.test(prefijo)) {
                    marcarInvalido($prefijo, 'El código prefijo solo puede contener letras.');
                    esValido = false;
                } else { marcarValido($prefijo); }

                return esValido;
            }
            let tiposHistorial = false;
            let tiposTable = null;

            // ===============================
            // Funciones para Tipos de Producto
            // ===============================

            // Cargar tipos al abrir modal de gestión
            $("#tiposModal").on("show.bs.modal", function () {
                actualizarVistaTipos();
                recargarTipos();
            });

            $("#tiposModal").on("shown.bs.modal", function () {
                if (tiposTable) {
                    tiposTable.columns.adjust().draw(false);
                }
            });

            function tiposUrl() {
                return "{{ route('tipo-productos.index') }}" + (tiposHistorial ? '?historial=true' : '');
            }

            function actualizarVistaTipos() {
                $("#btn-tipos-activos").toggleClass('active', !tiposHistorial);
                $("#btn-tipos-historial").toggleClass('active', tiposHistorial);
                $("#add-tipo-btn").toggle(!tiposHistorial);
            }

            function inicializarTiposTable() {
                tiposTable = $("#tipos-table").DataTable({
                    processing: true,
                    autoWidth: false,
                    scrollX: false,
                    ajax: {
                        url: tiposUrl(),
                        dataSrc: ''
                    },
                    columns: [
                        { data: 'nombre' },
                        {
                            data: 'prefijo',
                            render: function (data) {
                                return `<span class="badge bg-secondary">${data}</span>`;
                            }
                        },
                        {
                            data: 'productos_count',
                            render: function (data) {
                                return `<span class="badge bg-info">${data}</span>`;
                            }
                        },
                        {
                            data: null,
                            orderable: false,
                            searchable: false,
                            render: function (data, type, row) {
                                if (tiposHistorial) {
                                    return `
                                        <button class="btn btn-sm btn-outline-success restore-tipo-btn" data-id="${row.id}" title="Restaurar">
                                            <i class="ri-refresh-line"></i>
                                        </button>
                                    `;
                                }

                                return `
                                    <button class="btn btn-sm btn-outline-primary edit-tipo-btn" 
                                        data-id="${row.id}" 
                                        data-nombre="${row.nombre}" 
                                        data-prefijo="${row.prefijo}"
                                        data-descripcion="${row.descripcion || ''}">
                                        <i class="ri-pencil-line"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-danger delete-tipo-btn" data-id="${row.id}" title="Inhabilitar">
                                        <i class="ri-delete-bin-line"></i>
                                    </button>
                                `;
                            }
                        }
                    ],
                    order: [[0, 'asc']],
                    dom: 'rtip',
                    language: lenguajeData
                });
            }

            function recargarTipos() {
                if (!tiposTable) {
                    inicializarTiposTable();
                    return;
                }

                tiposTable.ajax.url(tiposUrl()).load(function () {
                    tiposTable.columns.adjust().draw(false);
                });
            }

            $("#btn-tipos-activos").on('click', function () {
                tiposHistorial = false;
                actualizarVistaTipos();
                recargarTipos();
            });

            $("#btn-tipos-historial").on('click', function () {
                tiposHistorial = true;
                actualizarVistaTipos();
                recargarTipos();
            });

            // ══════════════════════════════════════════════════════
            // VALIDACIONES ONBLUR — Formulario Producto principal
            // ══════════════════════════════════════════════════════

            // Tipo de Producto — select obligatorio
            $(document).on('blur', '#tipo-producto-field', function () {
                if (!$(this).val()) {
                    marcarInvalido($(this), 'El tipo de producto es obligatorio.');
                } else {
                    marcarValido($(this));
                }
            });

            // Descripción — mín. 10 chars
            $(document).on('blur', '#descripcion-field', function () {
                let val = $(this).val().trim();
                if (!val) {
                    marcarInvalido($(this), 'La descripción es obligatoria.');
                } else if (val.length < 10) {
                    marcarInvalido($(this), 'Mínimo 10 caracteres.');
                } else {
                    marcarValido($(this));
                }
            });

            // Precio Base — mayor a cero
            $(document).on('blur', '#precio-base-field', function () {
                let val = parseFloat($(this).val());
                if (isNaN(val) || val <= 0) {
                    marcarInvalido($(this), 'El precio base debe ser mayor a cero.');
                } else {
                    marcarValido($(this));
                }
            });

            // Imagen — formato y tamaño (solo en creación)
            $(document).on('change', '#imagen-field', function () {
                let file = this.files[0];
                if (!file) {
                    marcarValido($(this));
                    return;
                }
                let tiposPermitidos = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp', 'image/bmp', 'image/avif'];
                if (!tiposPermitidos.includes(file.type)) {
                    marcarInvalido($(this), 'Formato no permitido. Use JPG, PNG, GIF, WEBP, BMP o AVIF.');
                    return;
                }
                if (file.size > 10240 * 1024) {
                    marcarInvalido($(this), 'La imagen no puede superar 10MB.');
                    return;
                }
                marcarValido($(this));
            });

            // ══════════════════════════════════════════════════════
            // VALIDACIONES AJAX onblur — Tipos de Producto

            // 1. Nombre
            // Sugiere un prefijo a partir del nombre: primera letra + consonantes (máx 3).
            // Ej: Chemise→CHM, Franela→FRN, Pantalón→PNT. Es solo una sugerencia editable.
            function sugerirPrefijo(nombre) {
                var letras = (nombre || '').normalize('NFD').replace(/[̀-ͯ]/g, '')
                    .toUpperCase().replace(/[^A-Z]/g, '');
                if (!letras) return '';
                var vocales = 'AEIOU';
                var out = letras.charAt(0);
                for (var i = 1; i < letras.length && out.length < 3; i++) {
                    if (vocales.indexOf(letras.charAt(i)) === -1) out += letras.charAt(i);
                }
                if (out.length < 3) out = letras.substring(0, 3); // pocas consonantes → primeras letras
                return out;
            }

            $('#tipo-nombre-field').on('blur', function () {
                let value = $(this).val().trim();
                let $input = $(this);
                let isEdit = $('#tipo-id-field').val() !== '';

                if (value.length === 0) {
                    marcarInvalido($input, 'El nombre del tipo es obligatorio.');
                    return;
                }
                if (value.length < 2) {
                    marcarInvalido($input, 'El nombre debe tener al menos 2 caracteres.');
                    return;
                }

                // Sugerencia editable de prefijo (solo al crear y si el campo está vacío)
                if (!isEdit) {
                    var $pref = $('#tipo-prefijo-field');
                    if (!$pref.val().trim()) {
                        var sug = sugerirPrefijo(value);
                        if (sug) $pref.val(sug).trigger('blur');
                    }
                }

                if (isEdit) {
                    marcarValido($input);
                    return;
                }
                $.get("{{ route('tipo-productos.check-nombre') }}", { nombre: value }, function (res) {
                    if (res.exists) {
                        marcarInvalido($input, 'Este nombre ya está registrado.');
                        $('#save-tipo-btn').prop('disabled', true);
                    } else {
                        marcarValido($input);
                        $('#save-tipo-btn').prop('disabled', false);
                    }
                });
            });

            // 2. Prefijo — sanitizar a mayúsculas en tiempo real
            $(document).on('input', '#tipo-prefijo-field', function () {
                this.value = this.value.replace(/[^a-zA-Z]/g, '').toUpperCase();
            });

            $('#tipo-prefijo-field').on('blur', function () {
                let $input = $(this);
                let value = $input.val().trim();
                let isEdit = $('#tipo-id-field').val() !== '';

                if (!value) {
                    marcarInvalido($input, 'El código prefijo es obligatorio.');
                    return;
                }
                if (!/^[a-zA-Z]+$/.test(value)) {
                    marcarInvalido($input, 'El código prefijo solo puede contener letras.');
                    return;
                }
                if (isEdit) { marcarValido($input); return; }

                $.get("{{ route('tipo-productos.check-codigo') }}", { codigo: value }, function (res) {
                    if (res.exists) {
                        marcarInvalido($input, 'Este prefijo ya está registrado.');
                        $('#save-tipo-btn').prop('disabled', true);
                    } else {
                        marcarValido($input);
                        $('#save-tipo-btn').prop('disabled', false);
                    }
                });
            });

            // Abrir addTipoModal sin data-bs-toggle para no cerrar el padre
            $('#btn-add-tipo-inline, #add-tipo-btn').on('click', function () {
                $('#addTipoModal').modal('show');
            });

            // ==== Atributos disponibles (cache para el modal de Tipo) ====
            var atributosDisponibles = [];

            function cargarAtributosDisponibles() {
                return $.getJSON("{{ route('atributos.index') }}").done(function (rows) {
                    atributosDisponibles = rows;
                });
            }

            function renderAtributosLista(seleccionados) {
                // seleccionados: array [{id, orden}] | undefined
                var seleccionadosMap = {};
                (seleccionados || []).forEach(function (s) {
                    seleccionadosMap[s.id] = s.orden;
                });

                if (!atributosDisponibles.length) {
                    $('#tipo-atributos-list').html(
                        '<div class="text-muted small text-center py-2">No hay atributos definidos. ' +
                        'Crea atributos en <a href="{{ url('atributos') }}" target="_blank">/atributos</a> antes de asociarlos.</div>'
                    );
                    return;
                }

                var html = atributosDisponibles.map(function (a) {
                    var checked = seleccionadosMap.hasOwnProperty(a.id);
                    var orden = checked ? seleccionadosMap[a.id] : '';
                    return '' +
                        '<div class="d-flex align-items-center gap-2 p-2 border rounded tipo-atr-row" data-atr-id="' + a.id + '" data-search="' + escapeHtml((a.nombre + ' ' + (a.codigo || '')).toLowerCase()) + '">' +
                            '<div class="form-check flex-grow-1 mb-0">' +
                                '<input class="form-check-input tipo-atr-check" type="checkbox" id="tipo-atr-' + a.id + '"' + (checked ? ' checked' : '') + '>' +
                                '<label class="form-check-label" for="tipo-atr-' + a.id + '">' +
                                    '<strong>' + escapeHtml(a.nombre) + '</strong> ' +
                                    '<span class="badge bg-light text-muted ms-1">' + escapeHtml(a.codigo) + '</span>' +
                                '</label>' +
                            '</div>' +
                            '<div style="width: 90px;">' +
                                '<input type="number" class="form-control form-control-sm tipo-atr-orden" min="1" max="99" placeholder="orden" value="' + orden + '"' + (checked ? '' : ' disabled') + '>' +
                            '</div>' +
                        '</div>';
                }).join('');

                $('#tipo-atributos-list').html(html);
                actualizarContadorAtributos();
                // Reaplicar filtro de búsqueda activo (si lo hay)
                $('#tipo-atributos-search').trigger('input');
            }

            // Toggle del input de orden cuando se marca/desmarca
            $(document).on('change', '.tipo-atr-check', function () {
                var $row = $(this).closest('.tipo-atr-row');
                var $orden = $row.find('.tipo-atr-orden');
                if (this.checked) {
                    $orden.prop('disabled', false);
                    if (!$orden.val()) {
                        // Auto-asignar siguiente orden disponible
                        var ordenes = $('.tipo-atr-check:checked').map(function () {
                            return parseInt($(this).closest('.tipo-atr-row').find('.tipo-atr-orden').val()) || 0;
                        }).get();
                        var max = Math.max.apply(null, [0].concat(ordenes));
                        $orden.val(max + 1);
                    }
                } else {
                    $orden.prop('disabled', true).val('');
                }
            });

            // ── Contadores de seleccionados ──
            function actualizarContadorTelas() {
                var n = $('.tipo-tela-check:checked').length;
                $('#tipo-telas-count').text(n + (n === 1 ? ' seleccionada' : ' seleccionadas'));
            }
            function actualizarContadorAtributos() {
                var n = $('.tipo-atr-check:checked').length;
                $('#tipo-atributos-count').text(n + (n === 1 ? ' seleccionado' : ' seleccionados'));
            }
            $(document).on('change', '.tipo-tela-check', actualizarContadorTelas);
            $(document).on('change', '.tipo-atr-check', actualizarContadorAtributos);

            // ── Buscadores con filtrado en vivo ──
            $(document).on('input', '#tipo-telas-search', function () {
                var q = this.value.toLowerCase().trim();
                var any = false;
                $('#tipo-telas-list > .tipo-tela-item').each(function () {
                    var match = !q || ($(this).attr('data-search') || '').indexOf(q) !== -1;
                    $(this).toggleClass('d-none', !match);
                    if (match) any = true;
                });
                $('#tipo-telas-noresult').toggleClass('d-none', !q || any);
            });
            $(document).on('input', '#tipo-atributos-search', function () {
                var q = this.value.toLowerCase().trim();
                var any = false;
                $('#tipo-atributos-list > .tipo-atr-row').each(function () {
                    var match = !q || ($(this).attr('data-search') || '').indexOf(q) !== -1;
                    $(this).toggleClass('d-none', !match);
                    if (match) any = true;
                });
                $('#tipo-atributos-noresult').toggleClass('d-none', !q || any);
            });

            function recolectarAtributosSeleccionados() {
                var sel = [];
                $('.tipo-atr-check:checked').each(function () {
                    var $row = $(this).closest('.tipo-atr-row');
                    var id = parseInt($row.data('atr-id'));
                    var orden = parseInt($row.find('.tipo-atr-orden').val()) || 1;
                    sel.push({ id: id, orden: orden });
                });
                return sel;
            }

            function escapeHtml(s) {
                return String(s == null ? '' : s).replace(/[&<>"']/g, function (c) {
                    return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
                });
            }

            // ════════════════════════════════════════════════════════════
            // Insumos por defecto (template para órdenes de producción)
            // ════════════════════════════════════════════════════════════
            // Catálogo de insumos disponibles (passed from controller)
            var insumosCatalogo = @json($insumosDisponibles ?? []);
            var tipoInsumosState = []; // [{ id, nombre, unidad, cantidad }]

            function renderTipoInsumos() {
                var $list  = $('#tipo-insumos-list');
                var $empty = $('#tipo-insumos-empty');
                if (!tipoInsumosState.length) {
                    $list.empty();
                    $empty.show();
                    return;
                }
                $empty.hide();
                var optionsHtml = insumosCatalogo.map(function (i) {
                    return '<option value="' + i.id + '" data-nombre="' + escapeHtml(i.nombre) + '" data-unidad="' + escapeHtml(i.unidad_medida || '') + '">' +
                           escapeHtml(i.nombre) + ' (' + escapeHtml(i.unidad_medida || '') + ')</option>';
                }).join('');
                $list.html(tipoInsumosState.map(function (it, idx) {
                    return '<div class="row g-2 align-items-center tipo-insumo-row" data-idx="' + idx + '">' +
                        '<div class="col-md-7">' +
                            '<select class="form-select form-select-sm tipo-insumo-select">' +
                                '<option value="">Seleccione un insumo (hilo, botón, cierre…)</option>' + optionsHtml +
                            '</select>' +
                        '</div>' +
                        '<div class="col-md-3">' +
                            '<input type="number" class="form-control form-control-sm tipo-insumo-cantidad" step="0.01" min="0.01" placeholder="Por unidad" title="Cantidad por unidad producida" value="' + (it.cantidad || '') + '">' +
                        '</div>' +
                        '<div class="col-md-2 d-grid">' +
                            '<button type="button" class="btn btn-sm btn-soft-danger tipo-insumo-remove" title="Quitar"><i class="ri-delete-bin-line"></i></button>' +
                        '</div>' +
                    '</div>';
                }).join(''));
                // Setear los valores seleccionados después de render
                tipoInsumosState.forEach(function (it, idx) {
                    $('.tipo-insumo-row[data-idx="' + idx + '"] .tipo-insumo-select').val(it.id || '');
                });
            }

            // Sincroniza el estado desde los inputs visibles (antes de submit/cambios)
            function sincronizarTipoInsumosState() {
                tipoInsumosState = [];
                $('#tipo-insumos-list .tipo-insumo-row').each(function () {
                    var $row = $(this);
                    var $sel = $row.find('.tipo-insumo-select');
                    var id   = parseInt($sel.val(), 10);
                    var cant = parseFloat($row.find('.tipo-insumo-cantidad').val());
                    if (!id || isNaN(cant) || cant <= 0) return; // descartar filas vacías
                    var $opt = $sel.find('option:selected');
                    tipoInsumosState.push({
                        id: id,
                        nombre: $opt.data('nombre') || '',
                        unidad: $opt.data('unidad') || '',
                        cantidad: cant
                    });
                });
            }

            // Agregar fila vacía
            $(document).on('click', '#tipo-insumo-add-btn', function () {
                tipoInsumosState.push({ id: null, nombre: '', unidad: '', cantidad: '' });
                renderTipoInsumos();
                // foco en el último select
                $('#tipo-insumos-list .tipo-insumo-row:last .tipo-insumo-select').trigger('focus');
            });

            // Quitar fila
            $(document).on('click', '.tipo-insumo-remove', function () {
                var idx = parseInt($(this).closest('.tipo-insumo-row').data('idx'), 10);
                tipoInsumosState.splice(idx, 1);
                renderTipoInsumos();
            });

            // Cambios in-place (no rerenderear para no perder foco)
            $(document).on('change', '.tipo-insumo-select', function () {
                var idx = parseInt($(this).closest('.tipo-insumo-row').data('idx'), 10);
                var $opt = $(this).find('option:selected');
                tipoInsumosState[idx].id     = parseInt($(this).val(), 10) || null;
                tipoInsumosState[idx].nombre = $opt.data('nombre') || '';
                tipoInsumosState[idx].unidad = $opt.data('unidad') || '';
            });
            $(document).on('input', '.tipo-insumo-cantidad', function () {
                var idx = parseInt($(this).closest('.tipo-insumo-row').data('idx'), 10);
                tipoInsumosState[idx].cantidad = parseFloat($(this).val()) || '';
            });

            // Toggle visibilidad de consumo_tela y telas permitidas según requiere_tela
            function aplicarTipoConsumoTelaVisibility() {
                var on = $('#tipo-requiere-tela').is(':checked');
                $('#tipo-consumo-tela-row').toggle(on);
                $('#tipo-telas-section').toggle(on);
            }
            $(document).on('change', '#tipo-requiere-tela', aplicarTipoConsumoTelaVisibility);

            // Cargar atributos al abrir el modal (siempre refresca por si se agregaron en otra pestaña)
            $("#addTipoModal").on("show.bs.modal", function () {
                $('#tipo-atributos-empty').show();
                aplicarTipoConsumoTelaVisibility();
                cargarAtributosDisponibles().then(function () {
                    var idEdit = $("#tipo-id-field").val();
                    // En modo crear: render con cero seleccionados; en edición se rellena en el handler.
                    if (!idEdit) {
                        renderAtributosLista([]);
                        tipoInsumosState = [];
                        renderTipoInsumos();
                    }
                });
            });

            // Limpiar validaciones al cerrar modal de tipo
            $("#addTipoModal").on("hidden.bs.modal", function () {
                $('#tipoForm')[0].reset();
                $('#tipo-id-field').val('');
                $('#tipo-precio-confeccion').val('');
                $('#tipo-requiere-tela').prop('checked', true);
                $('#tipo-requiere-produccion').prop('checked', true);
                $('#tipo-consumo-tela').val('');
                $('.tipo-tela-check').prop('checked', false);
                aplicarTipoConsumoTelaVisibility();
                $('#tipo-atributos-list').html('');
                $('#tipo-imagen-preview').hide().find('img').attr('src', '');
                // Reset buscadores + contadores de las listas
                $('#tipo-telas-search, #tipo-atributos-search').val('').trigger('input');
                actualizarContadorTelas();
                actualizarContadorAtributos();
                tipoInsumosState = [];
                renderTipoInsumos();
                $('#tipoModalTitle').html('<i class="ri-add-line me-2"></i>Agregar Tipo de Producto');
                $('.is-invalid').removeClass('is-invalid');
                $('.is-valid').removeClass('is-valid');
                $('.invalid-feedback').hide();
                $('#save-tipo-btn').prop('disabled', false);
            });

            // Editar tipo: cargar datos completos vía GET (incluye atributos asociados)
            $(document).on("click", ".edit-tipo-btn", function () {
                var id = $(this).data("id");

                $.getJSON("{{ url('tipo-productos') }}/" + id, function (tipo) {
                    $("#tipo-id-field").val(tipo.id);
                    $("#tipo-nombre-field").val(tipo.nombre);
                    $("#tipo-prefijo-field").val(tipo.prefijo);
                    $("#tipo-descripcion-field").val(tipo.descripcion || '');
                    $("#tipo-precio-confeccion").val(tipo.precio_confeccion || 0);
                    $("#tipo-requiere-tela").prop('checked', !!tipo.requiere_tela);
                    $("#tipo-requiere-produccion").prop('checked', tipo.requiere_produccion == null ? true : !!tipo.requiere_produccion);
                    $("#tipo-consumo-tela").val(tipo.consumo_tela_por_unidad || 0);

                    if (tipo.imagen_url) {
                        $('#tipo-imagen-preview img').attr('src', tipo.imagen_url);
                        $('#tipo-imagen-preview').show();
                    } else {
                        $('#tipo-imagen-preview').hide();
                    }

                    // Telas permitidas del tipo (FEAT-003)
                    var telaIds = (tipo.telas || []).map(function (t) { return String(t.id); });
                    $('.tipo-tela-check').each(function () {
                        $(this).prop('checked', telaIds.indexOf(String(this.value)) !== -1);
                    });
                    actualizarContadorTelas();

                    aplicarTipoConsumoTelaVisibility();
                    $("#tipoModalTitle").html('<i class="ri-pencil-line me-2"></i>Editar Tipo de Producto');

                    var asociados = (tipo.atributos || []).map(function (a) {
                        return { id: a.id, orden: a.pivot ? a.pivot.orden : 1 };
                    });

                    // Hidratar insumos por defecto desde el tipo
                    tipoInsumosState = (tipo.insumos_default || []).map(function (i) {
                        return {
                            id: i.id,
                            nombre: i.nombre,
                            unidad: i.unidad_medida || '',
                            cantidad: parseFloat(i.pivot && i.pivot.cantidad_estimada) || ''
                        };
                    });

                    $("#tiposModal").modal('hide');
                    $("#addTipoModal").modal('show');

                    // Esperar a que termine la carga de atributos disponibles antes de renderizar
                    cargarAtributosDisponibles().then(function () {
                        renderAtributosLista(asociados);
                        renderTipoInsumos();
                    });
                });
            });

            // Ver tipo (detalle de solo lectura)
            function vtEsc(s) {
                return String(s == null ? '' : s).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
            }
            $(document).on("click", ".view-tipo-btn", function () {
                var id = $(this).data("id");
                $.getJSON("{{ url('tipo-productos') }}/" + id, function (tipo) {
                    if (tipo.imagen_url) {
                        $('#vt-imagen').attr('src', tipo.imagen_url);
                        $('#vt-imagen-wrap').show();
                        $('#vt-imagen-empty').attr('hidden', true);
                    } else {
                        $('#vt-imagen-wrap').hide();
                        $('#vt-imagen-empty').removeAttr('hidden');
                    }
                    $('#vt-nombre').text(tipo.nombre || '—');
                    $('#vt-prefijo').text(tipo.prefijo || '—');

                    var trashed = !!tipo.deleted_at || ((typeof esHistorial !== 'undefined') && esHistorial);
                    $('#vt-estatus').text(trashed ? 'Inhabilitado' : 'Activo')
                        .removeClass('badge-soft-success badge-soft-danger')
                        .addClass(trashed ? 'badge-soft-danger' : 'badge-soft-success');

                    $('#vt-precio').text('$ ' + parseFloat(tipo.precio_confeccion || 0).toFixed(2));
                    $('#vt-produccion').html(tipo.requiere_produccion === false
                        ? '<span class="badge badge-soft-warning">Reventa</span>'
                        : '<span class="badge badge-soft-success">Fabricado</span>');

                    var reqTela = !!tipo.requiere_tela;
                    $('#vt-requiere-tela').text(reqTela ? 'Sí' : 'No');
                    $('#vt-consumo-wrap').toggle(reqTela);
                    $('#vt-consumo').text(parseFloat(tipo.consumo_tela_por_unidad || 0).toFixed(2) + ' por unidad');

                    if (tipo.descripcion) {
                        $('#vt-descripcion').text(tipo.descripcion);
                        $('#vt-descripcion-wrap').show();
                    } else {
                        $('#vt-descripcion-wrap').hide();
                    }

                    var telas = tipo.telas || [];
                    $('#vt-telas-count').text('(' + telas.length + ')');
                    $('#vt-telas').html(telas.length
                        ? telas.map(function (t) { return '<span class="vt-chip vt-chip--tela"><i class="ri-shirt-line"></i>' + vtEsc(t.nombre) + '</span>'; }).join('')
                        : '<span class="text-muted small">' + (reqTela ? 'Sin telas asignadas' : 'No usa tela') + '</span>');

                    var attrs = tipo.atributos || [];
                    $('#vt-atributos-count').text('(' + attrs.length + ')');
                    $('#vt-atributos').html(attrs.length
                        ? attrs.map(function (a) { return '<span class="vt-chip vt-chip--attr"><i class="ri-price-tag-3-line"></i>' + vtEsc(a.nombre) + '</span>'; }).join('')
                        : '<span class="text-muted small">Sin atributos</span>');

                    var ins = tipo.insumos_default || [];
                    $('#vt-insumos-count').text('(' + ins.length + ')');
                    if (ins.length) {
                        $('#vt-insumos').html(ins.map(function (i) {
                            var cant = i.pivot ? parseFloat(i.pivot.cantidad_estimada || 0).toFixed(2) : '—';
                            return '<tr><td>' + vtEsc(i.nombre) + '</td><td class="text-end">' + cant + ' ' + vtEsc(i.unidad_medida || '') + '</td></tr>';
                        }).join(''));
                        $('#vt-insumos-wrap').removeAttr('hidden');
                        $('#vt-insumos-empty').hide();
                    } else {
                        $('#vt-insumos-wrap').attr('hidden', true);
                        $('#vt-insumos-empty').show();
                    }

                    $('#viewTipoModal').modal('show');
                });
            });

            // Eliminar tipo
            $(document).on("click", ".delete-tipo-btn", function () {
                var id = $(this).data("id");

                Swal.fire({
                    title: '¿Seguro que desea inhabilitar?',
                    text: "Solo se puede inhabilitar si no tiene productos asociados",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Sí, inhabilitar',
                    cancelButtonText: 'Cancelar',
                    customClass: {
                        confirmButton: 'btn btn-primary w-xs me-2',
                        cancelButton: 'btn btn-danger w-xs'
                    },
                    buttonsStyling: false,
                    showCloseButton: true
                }).then(function (result) {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: "{{ url('tipo-productos') }}/" + id,
                            method: "DELETE",
                            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                            success: function (response) {
                                if (tiposTable) {
                                    tiposTable.ajax.reload(null, false);
                                }
                                actualizarSelectTipos();
                                Swal.fire({
                                    title: 'Inhabilitado',
                                    text: response.message,
                                    icon: 'success',
                                    customClass: {
                                        confirmButton: 'btn btn-primary w-xs me-2',
                                        cancelButton: 'btn btn-danger w-xs'
                                    },
                                    buttonsStyling: false,
                                    showCloseButton: true
                                });
                            },
                            error: function (xhr) {
                                Swal.fire({
                                    title: 'Error',
                                    text: xhr.responseJSON.message,
                                    icon: 'error',
                                    customClass: {
                                        confirmButton: 'btn btn-primary w-xs me-2',
                                        cancelButton: 'btn btn-danger w-xs'
                                    },
                                    buttonsStyling: false,
                                    showCloseButton: true
                                });
                            }
                        });
                    }
                });
            });

            // Restaurar tipo
            $(document).on("click", ".restore-tipo-btn", function () {
                var id = $(this).data("id");

                Swal.fire({
                    title: '¿Seguro que desea restaurar este registro?',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Sí, restaurar',
                    cancelButtonText: 'Cancelar',
                    customClass: {
                        confirmButton: 'btn btn-primary w-xs me-2',
                        cancelButton: 'btn btn-danger w-xs'
                    },
                    buttonsStyling: false,
                    showCloseButton: true
                }).then(function (result) {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: "{{ url('tipo-productos') }}/" + id + "/restore",
                            method: "PATCH",
                            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                            success: function (response) {
                                if (tiposTable) {
                                    tiposTable.ajax.reload();
                                }
                                actualizarSelectTipos();
                                Swal.fire({
                                    title: 'Restaurado',
                                    text: response.message,
                                    icon: 'success',
                                    customClass: {
                                        confirmButton: 'btn btn-primary w-xs me-2',
                                        cancelButton: 'btn btn-danger w-xs'
                                    },
                                    buttonsStyling: false,
                                    showCloseButton: true
                                });
                            },
                            error: function (xhr) {
                                Swal.fire({
                                    title: 'Error',
                                    text: (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'No se pudo restaurar el tipo de producto',
                                    icon: 'error',
                                    customClass: {
                                        confirmButton: 'btn btn-primary w-xs me-2',
                                        cancelButton: 'btn btn-danger w-xs'
                                    },
                                    buttonsStyling: false,
                                    showCloseButton: true
                                });
                            }
                        });
                    }
                });
            });

            // Guardar tipo
            // Preview en vivo de la imagen del tipo
            $(document).on('change', '#tipo-imagen-field', function () {
                var file = this.files[0];
                if (!file) { return; }
                var tipos = ['image/jpeg','image/jpg','image/png','image/gif','image/webp','image/bmp','image/avif'];
                if (tipos.indexOf(file.type) === -1) {
                    marcarInvalido($(this), 'Formato no permitido. Use JPG, PNG, GIF, WEBP, BMP o AVIF.');
                    return;
                }
                if (file.size > 10240 * 1024) {
                    marcarInvalido($(this), 'La imagen no puede superar 10MB.');
                    return;
                }
                marcarValido($(this));
                var reader = new FileReader();
                reader.onload = function (e) {
                    $('#tipo-imagen-preview img').attr('src', e.target.result);
                    $('#tipo-imagen-preview').show();
                };
                reader.readAsDataURL(file);
            });

            $("#tipoForm").on("submit", function (e) {
                e.preventDefault();

                if (!validarFormularioTipo()) {
                    return;
                }

                var id = $("#tipo-id-field").val();
                var url = id ? "{{ url('tipo-productos') }}/" + id : "{{ route('tipo-productos.store') }}";

                // Sincronizar estado de insumos desde los inputs visibles antes de enviar
                sincronizarTipoInsumosState();

                // FormData para poder adjuntar la imagen del catálogo.
                var fd = new FormData();
                if (id) fd.append('_method', 'PUT');
                fd.append('nombre', $("#tipo-nombre-field").val());
                fd.append('prefijo', $("#tipo-prefijo-field").val().toUpperCase());
                fd.append('descripcion', $("#tipo-descripcion-field").val());
                fd.append('precio_confeccion', parseFloat($("#tipo-precio-confeccion").val()) || 0);
                fd.append('requiere_tela', $("#tipo-requiere-tela").is(':checked') ? 1 : 0);
                fd.append('requiere_produccion', $("#tipo-requiere-produccion").is(':checked') ? 1 : 0);
                fd.append('consumo_tela_por_unidad', parseFloat($("#tipo-consumo-tela").val()) || 0);

                recolectarAtributosSeleccionados().forEach(function (a, i) {
                    fd.append('atributos[' + i + '][id]', a.id);
                    fd.append('atributos[' + i + '][orden]', a.orden);
                });
                tipoInsumosState.forEach(function (it, i) {
                    fd.append('insumos_default[' + i + '][id]', it.id);
                    fd.append('insumos_default[' + i + '][cantidad_estimada]', it.cantidad);
                });
                if ($("#tipo-requiere-tela").is(':checked')) {
                    $('.tipo-tela-check:checked').each(function () {
                        fd.append('telas[]', this.value);
                    });
                }
                var imgFile = $('#tipo-imagen-field')[0].files[0];
                if (imgFile) fd.append('imagen', imgFile);

                $.ajax({
                    url: url,
                    method: "POST",
                    data: fd,
                    processData: false,
                    contentType: false,
                    headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                    success: function (response) {
                        $("#addTipoModal").modal('hide');

                        if (tiposTable) {
                            tiposTable.ajax.reload(null, false);
                        }

                        // Actualizar select de tipos
                        actualizarSelectTipos();

                        Swal.fire({
                            icon: 'success',
                            title: '¡Éxito!',
                            text: response.message,
                            showConfirmButton: false,
                            customClass: {
                                confirmButton: 'btn btn-primary w-xs me-2',
                                cancelButton: 'btn btn-danger w-xs'
                            },
                            buttonsStyling: false,
                            showCloseButton: true,
                            timer: 1500
                        });
                    },
                    error: function (xhr) {
                        var errors = xhr.responseJSON.errors || {};
                        var message = xhr.responseJSON.message || 'Error al guardar';
                        Swal.fire({
                            title: 'Error',
                            text: message,
                            icon: 'error',
                            customClass: {
                                confirmButton: 'btn btn-primary w-xs me-2',
                                cancelButton: 'btn btn-danger w-xs'
                            },
                            buttonsStyling: false,
                            showCloseButton: true
                        });
                    }
                });
            });

            // Actualizar select de tipos después de agregar uno nuevo
            function actualizarSelectTipos() {
                // Refrescar la tabla principal del catálogo (lista de Tipos — Fase 3).
                if (typeof table !== 'undefined' && table) {
                    table.ajax.reload(null, false);
                }
                $.get("{{ route('tipo-productos.index') }}", function (tipos) {
                    var select = $("#tipo-producto-field");
                    select.find("option:not(:first)").remove();

                    tipos.forEach(function (tipo) {
                        select.append(`<option value="${tipo.id}" data-prefijo="${tipo.prefijo}">${tipo.nombre}</option>`);
                    });
                });
            }

            // Limpiar modal de tipo al cerrar
            $("#addTipoModal").on("hidden.bs.modal", function () {
                $("#tipoForm")[0].reset();
                $("#tipo-id-field").val("");
                $("#tipoModalTitle").html('<i class="ri-add-line me-2"></i>Agregar Tipo de Producto');
            });
        });
    </script>
@endpush