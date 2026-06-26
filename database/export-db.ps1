<#
    export-db.ps1  —  Export ESTÁNDAR de la BD (Windows)

    Genera database/sistema_atlantico.sql en formato MySQL 8 NATIVO, idéntico
    sin importar quién lo corra. Lee la configuración desde el .env del proyecto.

    Requisito: MySQL 8 instalado y `mysqldump` accesible (en el PATH, o ajustar
    $MysqldumpExe abajo). NO usar phpMyAdmin ni el mysqldump de XAMPP/MariaDB.

    Uso:   powershell -ExecutionPolicy Bypass -File database\export-db.ps1
#>

$ErrorActionPreference = 'Stop'

# --- Resolver rutas ---
$ProjectRoot = Split-Path $PSScriptRoot -Parent
$EnvFile     = Join-Path $ProjectRoot '.env'
$OutFile     = Join-Path $PSScriptRoot 'sistema_atlantico.sql'

# --- Leer variables del .env ---
function Get-EnvValue {
    param([string]$Key, [string]$Default = '')
    if (-not (Test-Path $EnvFile)) { return $Default }
    foreach ($line in Get-Content $EnvFile) {
        if ($line -match "^\s*$Key\s*=\s*(.*)$") {
            return $Matches[1].Trim().Trim('"').Trim("'")
        }
    }
    return $Default
}

$DbHost = Get-EnvValue 'DB_HOST' '127.0.0.1'
$DbPort = Get-EnvValue 'DB_PORT' '3306'
$DbName = Get-EnvValue 'DB_DATABASE' 'sistema_atlantico'
$DbUser = Get-EnvValue 'DB_USERNAME' 'root'
$DbPass = Get-EnvValue 'DB_PASSWORD' ''

# --- Localizar mysqldump (MySQL 8) ---
$MysqldumpExe = (Get-Command mysqldump -ErrorAction SilentlyContinue).Source
if (-not $MysqldumpExe) {
    $candidate = 'C:\Program Files\MySQL\MySQL Server 8.0\bin\mysqldump.exe'
    if (Test-Path $candidate) { $MysqldumpExe = $candidate }
}
if (-not $MysqldumpExe) {
    Write-Error "No se encontró mysqldump. Instalá MySQL 8 y agregá su carpeta bin al PATH, o editá `$MysqldumpExe en este script."
    exit 1
}

# --- Pasar el password por variable de entorno (evita el warning de inseguridad) ---
if ($DbPass -ne '') { $env:MYSQL_PWD = $DbPass }

# --- Flags que garantizan un dump portable y MySQL-8 nativo ---
$args = @(
    "--host=$DbHost",
    "--port=$DbPort",
    "--user=$DbUser",
    '--single-transaction',        # snapshot consistente sin bloquear tablas
    '--no-tablespaces',            # evita exigir privilegio PROCESS
    '--set-gtid-purged=OFF',       # no inyecta GTID (rompe import sin privilegios)
    '--default-character-set=utf8mb4',
    '--add-drop-table',            # DROP TABLE IF EXISTS antes de cada CREATE
    "--result-file=$OutFile",      # NUNCA usar `>` en PowerShell (genera UTF-16/BOM)
    $DbName
)

Write-Host "Exportando '$DbName' ($DbHost`:$DbPort) -> $OutFile" -ForegroundColor Cyan
& $MysqldumpExe @args
$exit = $LASTEXITCODE

if ($env:MYSQL_PWD) { Remove-Item Env:\MYSQL_PWD }

if ($exit -ne 0) {
    Write-Error "mysqldump falló (código $exit)."
    exit $exit
}

Write-Host "OK. Dump generado. Ahora: git add database/sistema_atlantico.sql && git commit" -ForegroundColor Green
