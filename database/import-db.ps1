<#
    import-db.ps1  —  Import ESTÁNDAR de la BD (Windows)

    Crea la base de datos (si no existe) e importa database/sistema_atlantico.sql.
    Lee la configuración desde el .env del proyecto.

    Requisito: MySQL 8 instalado y `mysql` accesible (en el PATH).

    Uso:   powershell -ExecutionPolicy Bypass -File database\import-db.ps1
#>

$ErrorActionPreference = 'Stop'

$ProjectRoot = Split-Path $PSScriptRoot -Parent
$EnvFile     = Join-Path $ProjectRoot '.env'
$SqlFile     = Join-Path $PSScriptRoot 'sistema_atlantico.sql'

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

if (-not (Test-Path $SqlFile)) {
    Write-Error "No existe $SqlFile. Hacé git pull primero."
    exit 1
}

$MysqlExe = (Get-Command mysql -ErrorAction SilentlyContinue).Source
if (-not $MysqlExe) {
    $candidate = 'C:\Program Files\MySQL\MySQL Server 8.0\bin\mysql.exe'
    if (Test-Path $candidate) { $MysqlExe = $candidate }
}
if (-not $MysqlExe) {
    Write-Error "No se encontró mysql. Instalá MySQL 8 y agregá su carpeta bin al PATH."
    exit 1
}

if ($DbPass -ne '') { $env:MYSQL_PWD = $DbPass }

$baseArgs = @("--host=$DbHost", "--port=$DbPort", "--user=$DbUser")

# 1) Crear la BD si no existe (el .sql es agnóstico al nombre)
Write-Host "Asegurando base de datos '$DbName'..." -ForegroundColor Cyan
& $MysqlExe @baseArgs -e "CREATE DATABASE IF NOT EXISTS ``$DbName`` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# 2) Importar (SOURCE maneja bien la ruta con / ; el dump ya desactiva FK checks)
$srcPath = $SqlFile -replace '\\', '/'
Write-Host "Importando $SqlFile -> '$DbName'..." -ForegroundColor Cyan
& $MysqlExe @baseArgs $DbName -e "SOURCE $srcPath"
$exit = $LASTEXITCODE

if ($env:MYSQL_PWD) { Remove-Item Env:\MYSQL_PWD }

if ($exit -ne 0) {
    Write-Error "La importación falló (código $exit)."
    exit $exit
}
Write-Host "OK. Base de datos '$DbName' importada." -ForegroundColor Green
