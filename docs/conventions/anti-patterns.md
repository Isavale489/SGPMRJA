# Anti-Patrones y Lecciones Aprendidas

> Cosas que parecieron buenas ideas en su momento pero NO funcionan en este sistema. Leer antes de implementar algo similar.

## Fullscreen persistente en navegación multi-página

**No es posible** restaurar `requestFullscreen()` automáticamente al navegar entre páginas Blade.

### Por qué no funciona
- El browser (Chrome y otros) sale del fullscreen **sincrónicamente** cuando el usuario hace clic en un link, **antes** de cualquier evento JS (`beforeunload`, `fullscreenchange`).
- `requestFullscreen()` lanza excepción si se llama sin gesto explícito del usuario — no se puede llamar en `DOMContentLoaded` ni en `beforeunload`.

### La solución correcta (ya implementada)
**Toast de restauración**, no auto-restauración. Patrón:
1. Guardar `'true'` en `localStorage` al ENTRAR en fullscreen.
2. Guardar `'false'` solo en salidas intencionales del usuario (botón o tecla Esc) usando una flag `userExiting`.
3. Al cargar la página, si `localStorage = 'true'`, mostrar toast con botón "Restaurar" + barra de progreso de 8s.
4. La X del toast guarda `'false'` para no molestar de nuevo.

Implementación final: `app.blade.php` (script IIFE) + `custom.css` (animación `fs-progress-shrink`).

### Si en el futuro alguien pide persistencia real de fullscreen
**Ir directo al toast.** NO perder tiempo con `beforeunload` ni listeners de "primer clic". Si el sistema migra a SPA (Livewire navigation, Inertia.js), fullscreen persiste nativamente sin este workaround — esa sería la solución de fondo.

---

*Si encuentras otros anti-patrones que el equipo debería evitar, añádelos aquí con la misma estructura: problema → por qué falla → cómo resolverlo bien.*
