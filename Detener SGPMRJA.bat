@echo off
TITLE DETENIENDO SISTEMA SGPMRJA (LIMPIEZA TOTAL) 🧹
COLOR 0C

echo ==========================================
echo      APAGANDO MOTORES Y LIMPIANDO...
echo ==========================================
echo.

:: 1. MATAR PROCESOS INTERNOS (Los Motores)
echo [-] Deteniendo Laravel (PHP)...
taskkill /F /IM php.exe /T >NUL 2>&1

echo [-] Deteniendo Base de Datos (MySQL)...
taskkill /F /IM mysqld.exe /T >NUL 2>&1

echo [-] Deteniendo Servidor Web (Apache)...
taskkill /F /IM httpd.exe /T >NUL 2>&1

:: 2. CERRAR VENTANAS BASURA (La Limpieza Visual)
echo [-] Cerrando ventanas de comandos...

:: Cierra la ventana de Apache (Buscamos por el título que vimos en tu foto)
taskkill /F /FI "WINDOWTITLE eq Apache - apache_start.bat" >NUL 2>&1
:: Por si acaso el título es diferente (wildcard)
taskkill /F /FI "WINDOWTITLE eq Apache Server*" >NUL 2>&1

:: Cierra la ventana de MySQL
taskkill /F /FI "WINDOWTITLE eq MySQL Server - mysql_start.bat" >NUL 2>&1
:: Por si acaso (wildcard)
taskkill /F /FI "WINDOWTITLE eq MySQL Server*" >NUL 2>&1

:: Cierra la ventana principal del Sistema (Panel de Control)
taskkill /F /FI "WINDOWTITLE eq Sistema SGPMRJA - Panel de Control*" >NUL 2>&1


:: 3. LIMPIEZA DE SESIONES (Seguridad)
echo [-] Limpiando sesiones activas...
pushd "C:\Users\Santi\Project\SGPMRJA\storage\framework\sessions" 2>NUL
if "%ERRORLEVEL%"=="0" (
    for %%i in (*) do if not "%%~nxi"==".gitignore" del "%%i" >NUL 2>&1
    popd
)

echo.
echo ==========================================
echo      SISTEMA APAGADO Y ESCRITORIO LIMPIO.
echo      NOS VEMOS, BRO. 👋
echo ==========================================
timeout /t 2 >NUL
exit