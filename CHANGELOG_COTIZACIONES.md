# Registro de Cambios Funcionales - Módulo de Cotizaciones

**Fecha:** 19 de Enero, 2026
**Responsable:** Agente de Desarrollo (Antigravity)

Este documento detalla las modificaciones, mejoras y correcciones implementadas en el módulo de Gestión de Cotizaciones del Sistema "Manufacturas R.J. Atlántico".

## 1. Sincronización del Modal de Selección de Productos

**Objetivo:** Unificar la experiencia de usuario entre los módulos de "Pedidos" y "Cotizaciones".

* **Nuevo Buscador Avanzado:** Se reemplazó el método anterior de selección por un **Modal de Búsqueda**, que permite filtrar productos por nombre, código o tipo antes de seleccionarlos.
* **Diseño Visual:** Se replicó el diseño del modal de productos del módulo de Pedidos, incluyendo la tabla de resultados y las tarjetas de filtros.

## 2. Refinamiento del Formulario de Productos

**Objetivo:** Adaptar los campos a las necesidades específicas de una cotización, eliminando datos innecesarios.

* **Eliminación de "Insumos":** Se retiró la sección de cálculo de insumos que estaba presente en Pedidos, ya que no aplica para el flujo de cotización inicial.
* **Eliminación de Campo "Color":** Se ocultó/eliminó el selector de colores a petición del usuario.
* **Ajuste de Campo "Talla":** Se rediseñó el selector de tallas para ocupar el ancho completo disponible tras la remoción del campo de color.
* **Lógica de Bordado:** Se implementó la visualización condicional de los campos de "Nombre de Logo" y detalles de bordado, activándose solo cuando el switch "Lleva Bordado/Logo" está encendido.

## 3. Optimización del Flujo de Usuario (UX)

**Objetivo:** Simplificar el proceso de agregar múltiples productos rápidamente.

* **Botón "Agregar Otro Producto":**
  * **Antes:** Abría el buscador inmediatamente.
  * **Ahora:** Añade una **tarjeta vacía** ("Nuevo Producto") al formulario inmediatamente.
* **Selección Diferida:** El usuario puede añadir múltiples líneas vacías y luego hacer clic en la barra de búsqueda ("Clic para buscar producto...") de cada tarjeta para seleccionar el ítem específico.

## 4. Correcciones Técnicas y Visuales (Bug Fixes)

**Objetivo:** Resolver conflictos de visualización crítica que impedían el uso del sistema.

* **Solución de Superposición (Z-Index):**
  * *Problema:* El modal de búsqueda aparecía *detrás* del formulario de cotización.
  * *Solución:* Se reestructuró el código HTML (moviendo el modal al final del DOM) y se forzó el nivel de capa (`z-index: 1060`) para garantizar que siempre aparezca en primer plano.
* **Corrección de Bloqueo de Pantalla (Backdrop Fantasma):**
  * *Problema:* Al cerrar el buscador, el fondo oscuro desaparecía o bloqueaba el formulario principal.
  * *Solución:* Se implementó una lógica inteligente de limpieza que detecta si el modal padre ("Agregar Cotización") sigue abierto, preservando su fondo oscuro y permitiendo la interacción continua con los campos.
* **Restauración de Funciones JavaScript:** Se reactivaron funciones críticas de renderizado que se habían desconectado accidentalmente, asegurando que la lista de productos cargue siempre.

---
**Estado Actual:** El módulo se encuentra funcional, con todas las validaciones de interfaz activas y los problemas de bloqueo resueltos.
