/**
 * Sistema de notificaciones del header — v1
 *
 * Fuente: GET /notificaciones/sistema (atributo data-notif-endpoint del dropdown)
 * Estado: derivado en cada poll (sin tabla read/unread). Dismiss en localStorage por sesión.
 * Polling: cada 60s + refresh inmediato al abrir el dropdown.
 */
(function () {
    'use strict';

    const POLL_INTERVAL_MS = 60_000;
    const STORAGE_KEY = 'sgp.notif.dismissed';

    const dropdown = document.getElementById('notificationDropdown');
    if (!dropdown) return;

    const endpoint = dropdown.dataset.notifEndpoint;
    if (!endpoint) return;

    const badge = document.getElementById('notif-badge');
    const list = document.getElementById('notif-list');
    const empty = document.getElementById('notif-empty');
    const loading = document.getElementById('notif-loading');
    const errorBox = document.getElementById('notif-error');
    const clearBtn = document.getElementById('notif-clear-dismissed');

    function getDismissed() {
        try {
            const raw = sessionStorage.getItem(STORAGE_KEY);
            return raw ? new Set(JSON.parse(raw)) : new Set();
        } catch (e) {
            return new Set();
        }
    }

    function saveDismissed(set) {
        try {
            sessionStorage.setItem(STORAGE_KEY, JSON.stringify([...set]));
        } catch (e) { /* storage lleno o privado */ }
    }

    function clearDismissed() {
        try { sessionStorage.removeItem(STORAGE_KEY); } catch (e) { /* noop */ }
        fetchAndRender();
    }

    function escapeHtml(str) {
        return String(str ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }

    function renderItem(item) {
        const severityMap = {
            danger:  { bg: 'bg-danger-subtle',  text: 'text-danger'  },
            warning: { bg: 'bg-warning-subtle', text: 'text-warning' },
            info:    { bg: 'bg-info-subtle',    text: 'text-info'    },
            success: { bg: 'bg-success-subtle', text: 'text-success' },
        };
        const sev = severityMap[item.severidad] || severityMap.info;
        const icono = item.icono || 'ri-notification-3-line';
        const href = item.url || '#';

        return `
            <div class="text-reset notification-item d-block dropdown-item position-relative" data-notif-id="${escapeHtml(item.id)}">
                <div class="d-flex">
                    <div class="avatar-xs me-3 flex-shrink-0">
                        <span class="avatar-title ${sev.bg} ${sev.text} rounded-circle fs-16">
                            <i class="${escapeHtml(icono)}"></i>
                        </span>
                    </div>
                    <div class="flex-grow-1">
                        <a href="${escapeHtml(href)}" class="stretched-link">
                            <h6 class="mt-0 mb-1 fs-13 fw-semibold">${escapeHtml(item.titulo)}</h6>
                        </a>
                        <div class="fs-12 text-muted">
                            <p class="mb-0">${escapeHtml(item.mensaje)}</p>
                        </div>
                    </div>
                    <div class="px-2 fs-15 position-relative" style="z-index:2;">
                        <button type="button" class="btn btn-sm btn-icon btn-ghost-secondary" data-notif-dismiss="${escapeHtml(item.id)}" title="Ocultar en esta sesión">
                            <i class="ri-close-line"></i>
                        </button>
                    </div>
                </div>
            </div>
        `;
    }

    function setBadge(count) {
        if (!badge) return;
        if (count > 0) {
            badge.textContent = count > 99 ? '99+' : String(count);
            badge.classList.remove('d-none');
        } else {
            badge.classList.add('d-none');
        }
    }

    function render(items) {
        const dismissed = getDismissed();
        const visible = items.filter(it => !dismissed.has(it.id));

        list.innerHTML = visible.map(renderItem).join('');
        setBadge(visible.length);

        loading.classList.add('d-none');
        errorBox.classList.add('d-none');
        empty.classList.toggle('d-none', visible.length > 0);
        list.classList.toggle('d-none', visible.length === 0);
    }

    function showError() {
        loading.classList.add('d-none');
        empty.classList.add('d-none');
        list.classList.add('d-none');
        errorBox.classList.remove('d-none');
    }

    let inFlight = false;
    function fetchAndRender() {
        if (inFlight) return;
        inFlight = true;
        fetch(endpoint, {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            credentials: 'same-origin',
        })
            .then(r => {
                if (!r.ok) throw new Error('HTTP ' + r.status);
                return r.json();
            })
            .then(data => render(Array.isArray(data.items) ? data.items : []))
            .catch(() => showError())
            .finally(() => { inFlight = false; });
    }

    // Dismiss individual (delegado)
    list.addEventListener('click', (e) => {
        const btn = e.target.closest('[data-notif-dismiss]');
        if (!btn) return;
        e.preventDefault();
        e.stopPropagation();
        const id = btn.getAttribute('data-notif-dismiss');
        const set = getDismissed();
        set.add(id);
        saveDismissed(set);
        const item = btn.closest('[data-notif-id]');
        if (item) item.remove();
        const remaining = list.querySelectorAll('[data-notif-id]').length;
        setBadge(remaining);
        if (remaining === 0) {
            list.classList.add('d-none');
            empty.classList.remove('d-none');
        }
    });

    // Restaurar
    if (clearBtn) {
        clearBtn.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();
            clearDismissed();
        });
    }

    // Refresh al abrir el dropdown
    dropdown.addEventListener('shown.bs.dropdown', () => fetchAndRender());

    // Inicial + polling
    document.addEventListener('DOMContentLoaded', fetchAndRender);
    if (document.readyState === 'interactive' || document.readyState === 'complete') {
        fetchAndRender();
    }
    setInterval(fetchAndRender, POLL_INTERVAL_MS);
})();
