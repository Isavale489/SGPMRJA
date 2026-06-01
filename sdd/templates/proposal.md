---
type: feature        # feature | fix
base_branch: enmanuel
status: discussion   # discussion | review | accepted | rejected
---

# Proposal: <Título de la feature>

**Fecha**: YYYY-MM-DD
**Autor**: <nombre>
**Origen**: profesor | equipo | usuario final

---

## Origen

> Cita verbatim del pedido original (mensaje del profesor, observación del equipo, etc.).

> «<texto verbatim>»

---

## Resumen (1 párrafo)

<!-- Reformulación en nuestras palabras de lo que se va a construir. ≤ 6 oraciones. -->

---

## Motivación

<!-- ¿Por qué hacer esto? ¿Qué problema resuelve? -->

---

## Alcance propuesto

### Dentro de alcance
- Cosa 1
- Cosa 2

### Fuera de alcance
- Cosa 3 (sería una feature separada)

---

## Componentes afectados

| Componente | Tipo de impacto |
|---|---|
| `app/Models/...` | nuevo / modificar |
| `routes/web.php` | añadir grupo |
| Sidebar | añadir ítem |
| BD | nueva tabla / nueva columna |

---

## Riesgos y consideraciones

- Riesgo 1 — mitigación propuesta
- Riesgo 2 — mitigación propuesta

---

## Próximos pasos

- [ ] Revisar proposal con el equipo (Vanessa, Santiago)
- [ ] Si se aprueba → ejecutar `/sdd-spec <slug>` para crear el spec formal
- [ ] Si se rechaza → marcar `status: rejected` y mover a archivo
