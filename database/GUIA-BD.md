# Guía: subir y bajar la base de datos (MySQL 8)

> **Estándar del equipo:** todos usamos **MySQL 8** local (igual que producción) y
> exportamos/importamos la BD **solo con los scripts** de la carpeta `database/`.
> **No** usar phpMyAdmin ni el `mysqldump` de XAMPP/MariaDB para los dumps del repo
> (generan SQL incompatible que falla al importar en MySQL 8).

---

## 1. Requisitos (configurar una sola vez por equipo)

### 1.1 Instalar MySQL 8
- Descargar e instalar **MySQL Community Server 8.0** (https://dev.mysql.com/downloads/mysql/).
- Durante la instalación, anotá la **contraseña de `root`** que definas.
- Agregá la carpeta `bin` de MySQL al **PATH** del sistema. Suele ser:
  `C:\Program Files\MySQL\MySQL Server 8.0\bin`
  (Si no lo agregás al PATH, los scripts igual lo buscan ahí como respaldo.)

> **¿Tenés XAMPP con MySQL/MariaDB?** XAMPP ocupa el puerto **3306**. Tenés dos opciones:
> - **(Recomendado)** Parar el MySQL de XAMPP desde el panel y **destildar su autostart**,
>   y dejar MySQL 8 en el 3306.
> - O dejar XAMPP en 3306 y poner tu MySQL 8 en otro puerto (ej. 3308) — en ese caso
>   ajustá `DB_PORT` en el `.env` (ver abajo). Los scripts respetan el puerto del `.env`.

### 1.2 Verificar que MySQL 8 responde
```powershell
mysql --user=root -p -e "SELECT VERSION();"
```
Debe imprimir `8.0.xx`. Si imprime `10.x-MariaDB`, estás conectando a XAMPP, no a MySQL 8
(revisá el puerto / parar XAMPP).

### 1.3 Configurar el `.env`
Abrí el archivo `.env` de la raíz del proyecto y dejá la sección de BD así:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306              # el puerto de TU MySQL 8 (3306 si reemplazaste a XAMPP)
DB_DATABASE=sistema_atlantico
DB_USERNAME=root
DB_PASSWORD=tu_password   # la contraseña de root de MySQL 8
```
> El `.env` es **local de cada máquina** y no se sube al repo, así que cada uno pone
> su propio puerto/contraseña sin afectar a los demás.

---

## 2. Bajar la BD desde el repo (importar)

Cada vez que quieras la base de datos al día:

```powershell
git pull

# Windows
powershell -ExecutionPolicy Bypass -File database\import-db.ps1

# Linux / Mac
bash database/import-db.sh
```

El script:
1. Crea la base `sistema_atlantico` si no existe.
2. Importa `database/sistema_atlantico.sql`.

Después, levantá la app:
```powershell
php artisan config:clear
php artisan serve
```

### Verificar que quedó bien
```powershell
php artisan db:show
```
Debe decir **MySQL 8**, host `127.0.0.1`, la base `sistema_atlantico` y la cantidad de tablas.

---

## 3. Subir la BD al repo (exportar)

Cuando hagas cambios en los datos/esquema y quieras compartirlos:

```powershell
# Windows
powershell -ExecutionPolicy Bypass -File database\export-db.ps1

# Linux / Mac
bash database/export-db.sh

# luego
git add database/sistema_atlantico.sql
git commit -m "chore(db): actualizar dump"
git push
```

El script genera `database/sistema_atlantico.sql` en formato **MySQL 8 nativo**, idéntico
sin importar quién lo corra.

---

## 4. Solución de problemas

| Síntoma | Causa probable | Solución |
|---|---|---|
| `mysql no se reconoce…` / `command not found` | MySQL 8 no está en el PATH | Agregá `…\MySQL Server 8.0\bin` al PATH y abrí una terminal nueva. Los scripts igual lo buscan ahí. |
| `SELECT VERSION()` devuelve `10.x-MariaDB` | Estás pegándole a XAMPP, no a MySQL 8 | Pará XAMPP MySQL o corregí `DB_PORT` en el `.env`. |
| `Access denied for user 'root'` | Contraseña incorrecta en el `.env` | Poné la contraseña real de root de MySQL 8 en `DB_PASSWORD`. |
| `Field 'id' doesn't have a default value` al usar la app | Importaste un dump viejo de phpMyAdmin | Volvé a importar con `import-db.ps1` el dump del repo (es MySQL 8 nativo). |
| El puerto 3306 está ocupado al iniciar MySQL 8 | XAMPP MySQL tomó el 3306 primero | Pará XAMPP MySQL y destildá su autostart, o usá otro puerto para MySQL 8. |
| Error 500 al entrar al dashboard tras `git pull` | Falta regenerar el autoload de Composer | `composer dump-autoload` + `php artisan optimize:clear`. |

---

## 5. Alternativa sin importar dump (opcional)

Si preferís reconstruir la BD desde cero con las migraciones y los datos de catálogo:
```powershell
php artisan migrate:fresh --seed
```
Esto crea el esquema desde las migraciones y carga los seeders, sin depender del `.sql`.
