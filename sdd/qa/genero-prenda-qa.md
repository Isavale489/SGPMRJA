# QA — Género de prenda (Dama / Caballero / Unisex)

> Checklist de validación manual en navegador para la feature de **género por línea**.
> El cliente elige el género al cotizar (grilla talla × género); el género se propaga
> cotización → pedido → orden de producción y aparece en PDFs.

| Campo | Valor |
|---|---|
| Feature | Género de prenda (catálogo `genero` + FK por línea) |
| Rama / entorno | `enmanuel` |
| Build / commit | `3d95536` (o posterior) |
| Responsable QA | _____________________ |
| Fecha | _____________________ |

**Marca cada paso:** ✅ OK · ❌ Falla (anota qué viste) · ⏭️ N/A

---

## 0. Preparación

| # | Acción | Resultado esperado | Resultado |
|---|---|---|---|
| 0.1 | `git pull` en `enmanuel` | Baja los commits de género | ☐ |
| 0.2 | `php artisan migrate` | Corren 3 migraciones: `create_genero_table`, `add_genero_id_to_detalle_cotizacion_table`, `add_genero_id_to_detalle_pedido_table` | ☐ |
| 0.3 | Verificar catálogo: tabla `genero` tiene 3 filas (Dama, Caballero, Unisex) | 3 registros activos | ☐ |
| 0.4 | **Hard refresh del navegador (Ctrl+F5)** | `custom.css` cambió; sin esto la grilla se ve con el estilo viejo | ☐ |
| 0.5 | Iniciar sesión (usuario con acceso a Cotizaciones/Pedidos/Producción) | Acceso normal | ☐ |

---

## A. Configurador de cotización — grilla talla × género (núcleo)

Cotizaciones → **Nueva cotización** → elegir cliente → paso **Productos** → **Explorar catálogo** → elegir un tipo (ej. **Chemise**) → seleccionar variante (tela/atributos) → se abre el **configurador**.

| # | Acción | Resultado esperado | Resultado |
|---|---|---|---|
| A.1 | Observar la sección **Tallas y cantidades** | Arriba un **control segmentado horizontal** a todo el ancho: `Letras · Numéricas · Única` (default **Letras**). NO debe verse vertical ni pegado a la izquierda | ☐ |
| A.2 | Observar la matriz | Filas = tallas de la escala activa (~6, no las 15); columnas = **Dama / Caballero / Unisex** con su ícono | ☐ |
| A.3 | Inputs vacíos | Muestran placeholder `0` tenue (gris), sin un "0" duro escrito | ☐ |
| A.4 | Escribir cantidades (ej. **M**: Dama=6, Caballero=4) | Al cargar valor, la celda se resalta (texto en negrita + tinte verde) | ☐ |
| A.5 | Usar los **steppers `+` / `−`** de una celda | Suman/restan de a 1; al llegar a 0 la celda vuelve a estado vacío | ☐ |
| A.6 | Enfocar un input (Tab o click) | **Focus ring** elegante (borde/anillo primario) en la celda | ☐ |
| A.7 | Cambiar a la pestaña **Numéricas** | La matriz muestra otras tallas; lo cargado en Letras **se conserva** | ☐ |
| A.8 | Volver a **Letras** | Las cantidades cargadas en A.4 siguen ahí | ☐ |
| A.9 | Con datos en 2 escalas, mirar el segmented control | La(s) escala(s) no visibles con datos muestran un **badge** con el nº de unidades | ☐ |
| A.10 | Botón **Distribuir uniforme** | Pregunta **para qué género**, luego el total; reparte solo entre las tallas de la **escala visible** | ☐ |
| A.11 | Revisar **Total unidades** | Es la suma de **todas** las celdas (todas las escalas y géneros) | ☐ |
| A.12 | **Agregar al carrito** → confirmar carrito | Se crean líneas; en la lista agrupada cada talla muestra su **chip de género** (ej. `M·Dama ×6`) | ☐ |
| A.13 | Guardar la cotización | Toast de éxito; la cotización aparece en el listado | ☐ |

---

## B. Ver y editar cotización

| # | Acción | Resultado esperado | Resultado |
|---|---|---|---|
| B.1 | Abrir **Ver** de la cotización de A | En las líneas, los chips de talla incluyen el género | ☐ |
| B.2 | **Editar** → editar el bloque de producto | El configurador reabre con la escala que tiene datos seleccionada y las cantidades por género restauradas | ☐ |
| B.3 | Cambiar una cantidad y guardar | Persiste el cambio sin perder el género de las otras celdas | ☐ |

---

## C. Convertir cotización a pedido (propagación)

| # | Acción | Resultado esperado | Resultado |
|---|---|---|---|
| C.1 | Aprobar la cotización de A y **Convertir a pedido** | Pedido creado | ☐ |
| C.2 | Abrir el pedido → revisar líneas | Cada línea conserva el **mismo género** que la cotización | ☐ |
| C.3 | (Opcional BD) `SELECT genero_id FROM detalle_pedido WHERE pedido_id = <id>` | Coincide con los géneros elegidos (no NULL) | ☐ |

---

## D. Pedido — alta manual y edición

| # | Acción | Resultado esperado | Resultado |
|---|---|---|---|
| D.1 | Nuevo pedido → paso Productos → **Agregar producto** | El modal tiene un select **Género** (Dama/Caballero/Unisex), default **Unisex**, marcado como obligatorio | ☐ |
| D.2 | Guardar producto sin elegir género (si se pudiera vaciar) | Aviso "Género requerido" | ☐ |
| D.3 | Agregar producto con género y guardar el pedido | El resumen muestra el chip de género en la línea | ☐ |
| D.4 | Editar un pedido existente | Las líneas recuperan su género | ☐ |

---

## E. Orden de producción

| # | Acción | Resultado esperado | Resultado |
|---|---|---|---|
| E.1 | Producción → generar OP desde un pedido con género | En la selección de líneas, cada línea muestra un **chip de género** | ☐ |
| E.2 | Ver una OP / su listado | El **nombre del producto incluye el género** (ej. `Chemise · Dama · Piqué · Manga Larga`) | ☐ |
| E.3 | Reporte PDF de órdenes | El producto en el PDF muestra el género | ☐ |

---

## F. PDFs / Facturas

| # | Acción | Resultado esperado | Resultado |
|---|---|---|---|
| F.1 | Descargar **factura PDF de la cotización** | Cada línea muestra `Género: X` en el detalle del producto | ☐ |
| F.2 | Descargar **factura PDF del pedido** | Igual: el género aparece en la línea | ☐ |

---

## G. Estética / UX del rediseño

| # | Acción | Resultado esperado | Resultado |
|---|---|---|---|
| G.1 | Revisar el configurador completo | Stack vertical: segmented control arriba, matriz **limpia** debajo (sin bordes pesados por celda, separadores sutiles por fila) | ☐ |
| G.2 | (Si hay modo oscuro) alternar tema | Segmented control, matriz, inputs, steppers y chips se ven correctos en **dark mode** | ☐ |
| G.3 | Pantalla angosta / responsive | La grilla y el segmented control no se desbordan ni colisionan | ☐ |

---

## H. Regresión (no debe romperse)

| # | Acción | Resultado esperado | Resultado |
|---|---|---|---|
| H.1 | Cotizaciones existentes (previas a la feature) | Se ven con género **Unisex** (backfill); abren/editan sin error | ☐ |
| H.2 | Color y talla siguen funcionando igual | Sin cambios de comportamiento | ☐ |
| H.3 | Bordados, precios y totales | Calculan igual que antes | ☐ |
| H.4 | Productos de reventa (no producción) | Siguen sin generar OP | ☐ |

---

## Notas / hallazgos

_Anota aquí cualquier ❌, con módulo, paso (#) y qué viste:_

-
-
