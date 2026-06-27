{{-- ═══════════════════════════════════════════════════════════════════
     WIZARD ORDEN DE PRODUCCIÓN — showModal
     Fusiona la selección de pedido/líneas + la configuración de la(s)
     orden(es). Escala de 1 a N líneas en un mismo flujo:
       Paso 1 · Pedido    → buscar pedido y elegir 1+ líneas a producir
       Paso 2 · Asignación → empleado + cronograma (por línea, con "aplicar a todas")
       Paso 3 · Insumos    → insumos requeridos por línea (precargados del Tipo)
       Paso 4 · Resumen    → confirmar las órdenes a crear
     Lógica JS en: ordenes/scripts/main.blade.php (IIFE ordWiz)
     ═══════════════════════════════════════════════════════════════════ --}}
<div class="modal fade atlantico-modal atlantico-modal--op wiz-modal" id="showModal" tabindex="-1"
    aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false"
    data-guard-id-field="ord-wiz-id-field" data-guard-save-btn="ord-wiz-submit-btn">
    <div class="modal-dialog modal-dialog-centered modal-xl modal-fullscreen-sm-down">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">Nueva Orden de Producción</h5>
                <div class="wiz-dt-slot me-2">
                    @include('admin.partials.wizard-datetime-bar', ['prefix' => 'ord', 'modalId' => 'showModal', 'sm' => true])
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>

            {{-- Stepper visual — 4 pasos --}}
            <div class="wiz-stepper-wrapper">
                {{-- Chip cliente persistente — gutter izquierdo (pasos 2+) --}}
                <div class="wiz-stepper-side wiz-stepper-side--left">
                    <div class="wiz-client-banner" id="ord-cliente-banner" hidden aria-hidden="true"
                        title="Cliente del pedido">
                        <span class="wiz-client-banner-label">Cliente:</span>
                        <div class="wiz-client-banner-avatar" id="ord-banner-avatar">—</div>
                        <div class="wiz-client-banner-main">
                            <span class="wiz-client-banner-name" id="ord-banner-name">—</span>
                            <div class="wiz-client-banner-sub">
                                <span class="wiz-client-banner-doc" id="ord-banner-doc">—</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="wiz-stepper" role="tablist" aria-label="Pasos de la orden">
                    <button type="button" class="wiz-step-marker is-active" data-step="1"
                        role="tab" aria-selected="true" aria-controls="ord-wiz-step-1">
                        <span class="wiz-step-dot">1</span>
                        <span class="wiz-step-label">Pedido</span>
                    </button>
                    <span class="wiz-step-line"><span class="wiz-step-line-fill" data-line="1"></span></span>
                    <button type="button" class="wiz-step-marker" data-step="2"
                        role="tab" aria-selected="false" aria-controls="ord-wiz-step-2">
                        <span class="wiz-step-dot">2</span>
                        <span class="wiz-step-label">Asignación</span>
                    </button>
                    <span class="wiz-step-line"><span class="wiz-step-line-fill" data-line="2"></span></span>
                    <button type="button" class="wiz-step-marker" data-step="3"
                        role="tab" aria-selected="false" aria-controls="ord-wiz-step-3">
                        <span class="wiz-step-dot">3</span>
                        <span class="wiz-step-label">Insumos</span>
                    </button>
                    <span class="wiz-step-line"><span class="wiz-step-line-fill" data-line="3"></span></span>
                    <button type="button" class="wiz-step-marker" data-step="4"
                        role="tab" aria-selected="false" aria-controls="ord-wiz-step-4">
                        <span class="wiz-step-dot">4</span>
                        <span class="wiz-step-label">Resumen</span>
                    </button>
                </div>
                {{-- Chip "Creada por" — usuario logueado, gutter derecho --}}
                <div class="wiz-stepper-side wiz-stepper-side--right">
                    <div class="wiz-client-banner wiz-client-banner--creator" id="ord-creador-banner" hidden
                        aria-hidden="true" title="Creada por"
                        data-default-name="{{ Auth::user()->name }}"
                        data-default-avatar="{{ Auth::user()->avatar_url }}">
                        <img class="wiz-client-banner-avatar wiz-client-banner-avatar--img"
                            id="ord-creador-avatar" src="{{ Auth::user()->avatar_url }}" alt="" onerror="this.onerror=null;this.src=window.AMS_AVATAR_FALLBACK" />
                        <div class="wiz-client-banner-main">
                            <span class="wiz-client-banner-eyebrow">Creada por</span>
                            <span class="wiz-client-banner-name" id="ord-creador-name">{{ Auth::user()->name }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <form id="ordenForm" novalidate>
                @csrf
                <input type="hidden" id="ord-wiz-id-field" />

                {{-- Plantilla de opciones de empleado (clonada por línea en JS) --}}
                <select id="ord-empleados-tpl" class="d-none" tabindex="-1" aria-hidden="true">
                    <option value="">Seleccione empleado...</option>
                    @foreach($empleados as $emp)
                        <option value="{{ $emp->id }}">{{ $emp->name }}</option>
                    @endforeach
                </select>

                <div class="modal-body p-0 wiz-wizard-body">

                    {{-- ═══════════════════ PASO 1 — PEDIDO Y LÍNEAS ═══════════════════ --}}
                    <section class="wiz-step-content is-active" id="ord-wiz-step-1" data-step="1">
                        <div class="wiz-step-header">
                            <h4 class="wiz-step-title">Pedido y líneas a producir</h4>
                            <p class="wiz-step-desc">Busca el pedido y marca las líneas para las que vas a generar orden de producción.</p>
                        </div>

                        {{-- Modo edición: la línea queda fija (no se cambia el pedido) --}}
                        <div id="ord-edit-locked" class="px-3" hidden>
                            <div class="alert alert-info d-flex align-items-center gap-2 mb-0">
                                <i class="ri-lock-2-line fs-5"></i>
                                <div>
                                    <div class="fw-semibold">Estás editando una orden existente.</div>
                                    <small>El pedido y la línea no se pueden cambiar; ajusta la asignación, insumos y notas en los siguientes pasos.</small>
                                </div>
                            </div>
                        </div>

                        {{-- Selección de pedidos (modo crear) --}}
                        <div id="ord-select-wrap" class="px-3">
                            {{-- Búsqueda + filtros avanzados — Patrón Maestro S-07 --}}
                            <div class="advanced-filters-wrapper emerald-theme mb-3" id="pedord-advanced-filters">
                                <div class="navy-filter-header is-collapsed">
                                    <div class="navy-header-search">
                                        <i class="ri-search-line"></i>
                                        <input type="text" class="navy-search-input" id="pedord-search"
                                            placeholder="Buscar por cliente, documento o N° de pedido..." autocomplete="off">
                                    </div>
                                    <div class="navy-header-divider"></div>
                                    <button class="navy-filter-btn collapsed" type="button"
                                        data-bs-toggle="collapse" data-bs-target="#pedord-filters-collapse"
                                        aria-expanded="false" aria-controls="pedord-filters-collapse">
                                        <i class="ri-filter-3-line"></i>
                                        <span>Filtros</span>
                                        <span class="navy-filter-badge d-none" id="pedord-filter-count"></span>
                                        <i class="ri-arrow-down-s-line navy-filter-chevron"></i>
                                    </button>
                                </div>
                                <div class="collapse" id="pedord-filters-collapse">
                                    <div class="navy-filter-body">
                                        <div class="row g-3">
                                            <div class="col-12 col-md-6 col-lg-3">
                                                <label class="navy-filter-label" for="pedord-filter-estado">
                                                    <i class="ri-flag-line"></i> Estado del pedido
                                                </label>
                                                <select class="form-select navy-filter-select" id="pedord-filter-estado">
                                                    <option value="">Todos</option>
                                                    <option value="Pendiente">Pendiente</option>
                                                    <option value="Procesando">Procesando</option>
                                                </select>
                                            </div>
                                            <div class="col-12 col-md-6 col-lg-3">
                                                <label class="navy-filter-label" for="pedord-filter-orden">
                                                    <i class="ri-sort-asc"></i> Ordenar por
                                                </label>
                                                <select class="form-select navy-filter-select" id="pedord-filter-orden">
                                                    <option value="recientes">Más recientes</option>
                                                    <option value="entrega">Entrega más próxima</option>
                                                    <option value="pendientes">Más líneas sin orden</option>
                                                </select>
                                            </div>
                                            <div class="col-6 col-lg-3">
                                                <label class="navy-filter-label" for="pedord-filter-desde">
                                                    <i class="ri-calendar-line"></i> Pedido desde
                                                </label>
                                                <input type="date" class="form-control navy-filter-select" id="pedord-filter-desde">
                                            </div>
                                            <div class="col-6 col-lg-3">
                                                <label class="navy-filter-label" for="pedord-filter-hasta">
                                                    <i class="ri-calendar-2-line"></i> Pedido hasta
                                                </label>
                                                <input type="date" class="form-control navy-filter-select" id="pedord-filter-hasta">
                                            </div>
                                            <div class="col-12 col-md-6 col-lg-3">
                                                <label class="navy-filter-label" for="pedord-filter-cobertura">
                                                    <i class="ri-list-check-2"></i> Cobertura de órdenes
                                                </label>
                                                <select class="form-select navy-filter-select" id="pedord-filter-cobertura">
                                                    <option value="">Todos</option>
                                                    <option value="pendientes">Con líneas sin orden</option>
                                                    <option value="cubiertos">Completamente cubiertos</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="d-flex justify-content-end mt-2">
                                            <button type="button" class="btn btn-link" id="pedord-clear-filters">Limpiar filtros</button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Contenedor de pedidos --}}
                            <div id="pedidos-orden-container" style="max-height: 420px; overflow-y: auto;"></div>

                            {{-- Estado vacío --}}
                            <div id="pedidos-orden-empty" class="text-center py-5" style="display: none;">
                                <i class="ri-inbox-line" style="font-size: 4rem; color: #cbd5e1;"></i>
                                <p class="text-muted mt-3 mb-0">No hay pedidos disponibles para producir</p>
                                <small class="text-muted">Los pedidos cancelados o completados no aparecen aquí</small>
                            </div>

                            {{-- Loading --}}
                            <div id="pedidos-orden-loading" class="text-center py-5" style="display: none;">
                                <div class="spinner-border text-success" role="status">
                                    <span class="visually-hidden">Cargando...</span>
                                </div>
                                <p class="text-muted mt-3 mb-0">Cargando pedidos...</p>
                            </div>
                        </div>
                    </section>

                    {{-- ═══════════════════ PASO 2 — ASIGNACIÓN ═══════════════════ --}}
                    <section class="wiz-step-content" id="ord-wiz-step-2" data-step="2" hidden>
                        <div class="wiz-step-header">
                            <h4 class="wiz-step-title">Asignación y cronograma</h4>
                            <p class="wiz-step-desc" id="ord-asignacion-desc">Define quién produce cada orden y sus fechas.</p>
                        </div>

                        {{-- Barra "aplicar a todas" — solo visible con 2+ líneas; colapsada por defecto --}}
                        <div class="px-3" id="ord-apply-bar" hidden>
                            <div class="ord-apply-card ord-apply-card--collapsible mb-3">
                                <div class="ord-apply-head" role="button" tabindex="0"
                                     data-bs-toggle="collapse" data-bs-target="#ord-apply-collapse"
                                     aria-expanded="false" aria-controls="ord-apply-collapse">
                                    <span class="ord-apply-icon"><i class="ri-flashlight-line"></i></span>
                                    <div class="flex-grow-1">
                                        <div class="ord-apply-title">Aplicar a todas las órdenes</div>
                                        <div class="ord-apply-sub">Define empleados y fechas una vez y cópialos a cada orden.</div>
                                    </div>
                                    <i class="ri-arrow-down-s-line ord-apply-chevron"></i>
                                </div>
                                <div class="collapse" id="ord-apply-collapse">
                                <div class="row g-2 align-items-end pt-2">
                                    <div class="col-12">
                                        <label class="form-label form-label-sm mb-1"><i class="ri-team-line me-1"></i>Empleados</label>
                                        <div id="ord-default-empleado-wrap" class="ord-asig-emp-checks"></div>
                                    </div>
                                    <div class="col-md-4">
                                        <label for="ord-default-inicio" class="form-label form-label-sm mb-1"><i class="ri-calendar-event-line me-1"></i>Inicio</label>
                                        <input type="date" id="ord-default-inicio" class="form-control form-control-sm" />
                                    </div>
                                    <div class="col-md-4">
                                        <label for="ord-default-fin" class="form-label form-label-sm mb-1"><i class="ri-calendar-check-line me-1"></i>Fin estimado</label>
                                        <input type="date" id="ord-default-fin" class="form-control form-control-sm" />
                                    </div>
                                    <div class="col-md-4 d-grid align-self-end">
                                        <button type="button" class="btn btn-sm btn-atlantico-brand" id="ord-apply-defaults"
                                            title="Copia estos valores a todas las líneas">
                                            <i class="ri-arrow-down-double-line me-1"></i>Aplicar a todas
                                        </button>
                                    </div>
                                </div>
                                </div>
                            </div>
                        </div>

                        {{-- Separador: aquí empieza la asignación individual por línea --}}
                        <div class="px-3" id="ord-porlinea-sep" hidden>
                            <div class="ord-section-divider">
                                <span class="ord-section-divider-line"></span>
                                <span class="ord-section-divider-label">
                                    <i class="ri-list-check-2 me-1"></i>Asignación por orden
                                    <span class="ord-section-divider-count" id="ord-porlinea-count"></span>
                                </span>
                                <span class="ord-section-divider-line"></span>
                            </div>
                        </div>

                        {{-- Cards de asignación por línea (render JS) --}}
                        <div class="px-3" id="ord-asignacion-cards"></div>
                    </section>

                    {{-- ═══════════════════ PASO 3 — INSUMOS ═══════════════════ --}}
                    <section class="wiz-step-content" id="ord-wiz-step-3" data-step="3" hidden>
                        <div class="wiz-step-header">
                            <h4 class="wiz-step-title">Insumos requeridos</h4>
                            <p class="wiz-step-desc">Los insumos vienen precargados del tipo de producto. Ajústalos por línea si es necesario.</p>
                        </div>
                        {{-- Acordeón de insumos por línea (render JS) --}}
                        <div class="px-3" id="ord-insumos-acc"></div>
                    </section>

                    {{-- ═══════════════════ PASO 4 — RESUMEN ═══════════════════ --}}
                    <section class="wiz-step-content" id="ord-wiz-step-4" data-step="4" hidden>
                        <div class="wiz-step-header">
                            <h4 class="wiz-step-title">Resumen</h4>
                            <p class="wiz-step-desc" id="ord-resumen-desc">Revisa las órdenes que se van a crear antes de confirmar.</p>
                        </div>
                        <div class="px-3">
                            <div id="ord-resumen"></div>

                            {{-- Notas (compartidas para todas las órdenes del lote) --}}
                            <div class="cli-view-card cli-view-card--flat mt-2 mb-0">
                                <div class="cli-view-card-header">
                                    <i class="ri-sticky-note-line"></i>Notas <span class="text-muted fw-normal" id="ord-notas-scope"></span>
                                </div>
                                <div class="cli-view-card-body">
                                    <textarea id="ord-notas-global" class="form-control form-control-sm" rows="2"
                                        placeholder="Observaciones sobre la orden (opcional)..."></textarea>
                                </div>
                            </div>
                        </div>
                    </section>

                </div>{{-- /modal-body --}}

                <div class="modal-footer wiz-wizard-footer">
                    <div class="wiz-wizard-footer-info">
                        <span class="wiz-wizard-step-info">
                            Paso <span id="ord-step-current">1</span> de 4
                        </span>
                        <span class="ms-2 badge bg-success-subtle text-success d-none" id="ord-lineas-chip"></span>
                    </div>
                    <div class="wiz-wizard-footer-actions">
                        <button type="button" class="btn btn-light wiz-wizard-btn-prev" id="btn-ord-prev"
                            style="display:none;">
                            <i class="ri-arrow-left-line me-1"></i>Anterior
                        </button>
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                            <i class="ri-close-line me-1"></i>Cerrar
                        </button>
                        <button type="button" class="btn btn-atlantico-brand wiz-wizard-btn-next" id="btn-ord-next">
                            Continuar<i class="ri-arrow-right-line ms-1"></i>
                        </button>
                        <button type="submit" class="btn btn-success wiz-wizard-btn-submit" id="ord-wiz-submit-btn"
                            style="display:none;">
                            <i class="ri-check-line me-1"></i><span id="ord-wiz-submit-label">Crear Orden</span>
                        </button>
                    </div>
                </div>
            </form>

        </div>
    </div>
</div>
