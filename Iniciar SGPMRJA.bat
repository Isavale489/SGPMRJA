@echo off
TITLE Sistema SGPMRJA - Panel de Control 🚀
COLOR 0A

:: --- TUS RUTAS ---
set XAMPP_DIR=C:\xampp
set PROYECTO_DIR=C:\Users\Santi\Project\SGPMRJA
:: -----------------

echo ==========================================
echo    INICIANDO SISTEMA SGPMRJA (V7)
echo ==========================================
echo.

:: 1. GESTION DE SERVICIOS (XAMPP)
echo [1/4] 🔧 Verificando servicios de XAMPP...

:: Entramos a XAMPP para evitar errores de ruta
pushd "%XAMPP_DIR%"

:: -- MySQL --
tasklist /FI "IMAGENAME eq mysqld.exe" 2>NUL | find /I /N "mysqld.exe">NUL
if "%ERRORLEVEL%"=="0" (
    echo    - MySQL ya esta corriendo.
) else (
    echo    - Iniciando MySQL...
    start /min "MySQL Server" "mysql_start.bat"
)

:: -- Apache --
tasklist /FI "IMAGENAME eq httpd.exe" 2>NUL | find /I /N "httpd.exe">NUL
if "%ERRORLEVEL%"=="0" (
    echo    - Apache ya esta corriendo.
) else (
    echo    - Iniciando Apache...
    :: NOTA: Esta ventana se abrira, ES NORMAL. Solo minimizala.
    start /min "Apache Server" "apache_start.bat"
)

:: Volvemos al proyecto
popd
echo.

:: 2. IR AL PROYECTO
cd /d "%PROYECTO_DIR%"

:: 3. ACTUALIZAR
echo [2/4] 🔄 Buscando actualizaciones en GitHub...
call git pull origin main
echo.

:: 4. LIBRERIAS
echo [3/4] 📦 Verificando dependencias...
call composer install --no-interaction --prefer-dist
echo.

:: 5. LANZAMIENTO
echo [4/4] 🌐 Abriendo sistema...
start http://127.0.0.1:8000
echo.
echo    ---------------------------------------------------
echo    SISTEMA LISTO.
echo    1. Si ves ventanas de Apache/MySQL: MINIMIZALAS (No cerrar).
echo    2. Esta ventana debe quedarse abierta.
echo    ---------------------------------------------------
echo.
echo    Iniciando Laravel...
php artisan serve

:: --- FRENO DE MANO ---
:: Si el servidor se apaga o falla, el script llegara aqui.
echo.
echo !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
echo EL SERVIDOR SE DETUVO.
echo Revisa si hay errores arriba (texto rojo).
echo !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
pause