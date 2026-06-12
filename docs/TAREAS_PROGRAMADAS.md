# Tareas programadas (scheduler) y configuración del cron

Este proyecto usa el **scheduler de Laravel** para ejecutar tareas automáticas.
Las tareas están **definidas en el código** (`app/Console/Kernel.php`), pero para
que se ejecuten solas hace falta **un paso de configuración en el servidor**: una
entrada de cron que dispare el scheduler cada minuto.

> ⚠️ Importante: el cron **NO está en el repositorio** (es configuración del
> sistema operativo, no del código). Subir el código **no** lo configura. Cada
> servidor donde se despliegue el sistema debe configurarlo **una sola vez**.

---

## Tareas definidas actualmente

| Tarea | Comando | Cuándo corre (hora Venezuela) |
|---|---|---|
| Actualizar tasa BCV | `bcv:actualizar` | Cada hora, de **5:00 PM a 10:00 PM** |
| Marcar cotizaciones vencidas | (closure) | Diario a las **00:05** |

**Tasa BCV:** se reintenta cada hora entre las 5 y 10 PM porque el BCV publica su
tasa en la tarde; así se asegura tomar la del día aunque la API aún no la haya
publicado a las 5:00 en punto. El comando es idempotente (`updateOrCreate` por
fecha), así que repetir no causa problemas.

Respaldo adicional: si la tasa guardada no es de hoy, al abrir cualquier vista del
panel admin el sistema la actualiza al momento (ver `AppServiceProvider`). Por eso
**en desarrollo local no es obligatorio** configurar el cron.

---

## Configurar el cron (servidor de producción)

Una sola vez, en el servidor, editar el crontab del usuario que sirve la app:

```bash
crontab -e
```

Y agregar **esta línea** (ajustar la ruta del proyecto y la de `php` según el servidor):

```cron
* * * * * cd /ruta/al/proyecto && /usr/bin/php artisan schedule:run >> /ruta/al/proyecto/storage/logs/scheduler.log 2>&1
```

- `* * * * *` = cada minuto. Laravel decide internamente qué tarea toca según la hora.
- El `cd /ruta/al/proyecto &&` es **obligatorio**: sin él, `php artisan` no encuentra el archivo `artisan`.

Confirmar que el servicio cron esté activo:

```bash
systemctl is-active cron      # Debian/Ubuntu (o 'crond' en RHEL/CentOS)
```

---

## Verificar que funciona

```bash
# Ver las tareas y su próxima ejecución
php artisan schedule:list

# Ejecutar el scheduler manualmente (como lo hace el cron cada minuto)
php artisan schedule:run

# Probar solo la tasa BCV de inmediato (ignora la hora programada)
php artisan bcv:actualizar
```

Si el cron está corriendo, `storage/logs/scheduler.log` se irá escribiendo cada
minuto (normalmente con "No scheduled commands are ready to run." fuera de los
horarios programados).

---

## Resumen para el despliegue

1. `git pull` → trae el código con las tareas definidas (`Kernel.php`).
2. Configurar la línea de cron de arriba en el servidor (una sola vez).
3. Verificar con `php artisan schedule:list`.

Eso deja la tasa del BCV actualizándose sola todos los días de 5 a 10 PM.
