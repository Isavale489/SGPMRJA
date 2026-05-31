# Guía de pruebas — Pedidos (Pago y Estado automático)

> Para la persona que va a probar. No necesitas saber de programación.
> Trabaja en el sistema en `127.0.0.1:8000`, sección **Gestión de Pedidos**.
> Si algo no funciona como dice el "✅ resultado esperado", anótalo (ver el final).

---

## ¿Qué se cambió? (en simple)

Se mejoraron **dos cosas** dentro del módulo de Pedidos:

1. **Paso "Pago" del formulario de pedido** — ahora se pueden registrar **varios pagos**
   (varias transferencias y/o pagos móviles + un efectivo) para abonar un mismo pedido,
   con un resumen visual de cuánto se abonó y cuánto falta.

2. **Estado del pedido (Pendiente / Procesando / Completado)** — antes se cambiaba a mano.
   **Ahora se actualiza solo** según el avance de las **órdenes de producción**:
   - Sin producción iniciada → **Pendiente**
   - Producción en marcha → **Procesando**
   - Todo producido → **Completado**
   - **Cancelado** sigue siendo manual (botón Cancelar).
   Además, un pedido **ya no se puede editar/eliminar si tiene producción iniciada**
   (para no romper las órdenes).

---

## Antes de empezar
- Entra al sistema con un usuario **Administrador**.
- Ten a mano un cliente y, si puedes, prueba en **modo claro y modo oscuro** (el botón de tema).

---

## PARTE A — Paso "Pago" del pedido

> Recuerda: un pedido nace de una **cotización aprobada**. Si no tienes una,
> primero crea una cotización y márcala como Aprobada.

1. Entra a **Pedidos** → **Agregar Pedido** → elige una cotización aprobada.
2. Avanza hasta el **Paso 3: Pago**.

**A1. Vista inicial**
- ✅ Arriba ves un recuadro "Resumen de pago" con **Total / Abonado / Restante** y una **barra**.
- ✅ Una tarjeta **"Métodos de pago"** con botones **+ Efectivo**, **+ Transferencia**, **+ Pago móvil**.
- ✅ Como no hay pagos aún, aparece un mensaje de "Aún no registras pagos".

**A2. Agregar pagos**
- Haz clic en **+ Transferencia** → aparece una fila con: banco, referencia y monto.
- ✅ Llena banco + referencia + un monto. La barra y el "Abonado/Restante" se actualizan solos.
- Agrega **otra + Transferencia** y un **+ Pago móvil**.
- ✅ Se permiten **varias** transferencias y varios pagos móviles.
- Haz clic en **+ Efectivo** y pon un monto.
- ✅ Tras agregar Efectivo, el botón **+ Efectivo se desactiva** (solo se permite uno).

**A3. Botón "Resto"**
- En cualquier fila, haz clic en **Resto**.
- ✅ El monto de esa fila se completa con lo que falta para llegar al Total.

**A4. Barra y estados**
- ✅ Si abonas menos que el total → dice **"Resta $X"** (ámbar).
- ✅ Si abonas exactamente el total → dice **"Pagado completo"** (verde).
- ✅ Si abonas de más → dice **"Excede el total"** (rojo).

**A5. Quitar y validaciones**
- Usa el ícono de **basurita** para quitar un pago. ✅ Se actualiza el resumen.
- Deja una fila con monto vacío o en 0 y pulsa **Continuar**.
  - ✅ No te deja avanzar y te avisa (monto requerido).
- En transferencia/pago móvil, deja banco o referencia vacíos y **Continuar**.
  - ✅ No te deja avanzar y te avisa.
- ✅ La lista de pagos tiene scroll propio si agregas muchos (no se desborda la ventana).

**A6. Guardar y revisar**
- Completa el pedido y **Guarda**.
- Abre el pedido con el ojito (**Ver**) → ✅ los pagos aparecen listados con su banco/referencia/monto.
- Edita el pedido (lápiz) → ✅ los pagos se vuelven a cargar tal cual los guardaste.
- ✅ Repite revisando en **modo oscuro** que todo se vea bien.

---

## PARTE B — Estado automático del pedido

> El estado se maneja desde **Órdenes de Producción**. Para esta parte vas a crear
> órdenes para las líneas del pedido y registrar avances.

**B1. Pedido nuevo = Pendiente**
- Crea/guarda un pedido. En el listado de Pedidos, ✅ su estado es **Pendiente**.

**B2. Crear órdenes NO cambia el estado todavía**
- Ve a **Órdenes de Producción** y crea las órdenes para las líneas de ese pedido
  (sin registrar avance aún).
- Vuelve a Pedidos → ✅ el pedido **sigue en Pendiente** (las órdenes existen pero no han arrancado).

**B3. Registrar avance → Procesando**
- En una orden de ese pedido, **registra un avance** (una cantidad producida).
- Vuelve a Pedidos → ✅ el pedido pasó a **Procesando** automáticamente.

**B4. Terminar todo → Completado**
- Registra avances hasta **finalizar todas las órdenes** de **todas las líneas** del pedido.
- Vuelve a Pedidos → ✅ el pedido pasó a **Completado** automáticamente.

**B5. El estado NO se edita a mano**
- Abre el pedido en editar (cuando aún se pueda) → en la sección "Estado del pedido"
  ✅ ves el estado como una **etiqueta de solo lectura** (no botones para cambiarlo),
  con el texto "Se actualiza automáticamente según el avance de producción".

---

## PARTE C — Bloqueo de edición con producción iniciada

**C1. Sin producción: se puede editar/eliminar**
- En un pedido **sin órdenes**, en el listado ✅ aparecen los botones **Editar** (lápiz) y **Eliminar** (basurita).

**C2. Con producción: se bloquea**
- Crea **una orden** para ese pedido (aunque no registres avance).
- Refresca el listado → ✅ los botones **Editar y Eliminar desaparecen** de ese pedido.
  Quedan **Ver**, **PDF** y **Cancelar**.

**C3. Se desbloquea al quitar las órdenes**
- Borra las órdenes de ese pedido (las que estén en estado *Pendiente* se pueden eliminar).
- Refresca → ✅ vuelven a aparecer Editar y Eliminar.

---

## PARTE D — Cancelar / Reactivar (manual)

**D1. Cancelar**
- En un pedido en Pendiente o Procesando, en el listado pulsa el botón **Cancelar** (círculo con X).
- Confirma → ✅ el pedido queda en **Cancelado**.

**D2. Reactivar**
- En un pedido **Cancelado**, pulsa **Reactivar** (flechas).
- Confirma → ✅ el pedido sale de Cancelado y su estado se recalcula según la producción
  (queda Pendiente/Procesando/Completado según corresponda).

**D3. Reglas**
- ✅ Un pedido **Completado** no ofrece botón Cancelar.
- ✅ Un pedido **Cancelado** no se puede editar ni eliminar (solo Reactivar / Ver / PDF).

---

## (Opcional) PARTE E — Chips de cliente y creador en el wizard
*(Esto ya se probó antes, pero si quieres confirmar:)*
- En el formulario de Cotización y de Pedido, al avanzar de paso ✅ arriba a la izquierda
  se ve **"Para: (cliente)"** y a la derecha **"Creada/Creado por: (tu usuario)"**.
- El nombre del cliente debe verse **completo** (no cortado).

---

## Cómo reportar lo que encuentres
Para cada problema, anota:
1. **Dónde** estabas (ej: "Pedidos → Paso Pago" o "listado de Pedidos").
2. **Qué hiciste** (los pasos).
3. **Qué esperabas** y **qué pasó** en su lugar.
4. Si puedes, una **captura de pantalla**.
5. Si salió un mensaje de error, **cópialo tal cual**.

¡Gracias por probar! 🙌
