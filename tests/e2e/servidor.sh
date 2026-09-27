#!/usr/bin/env bash
# Levanta la app para el smoke E2E contra una BD PROPIA (nunca la de desarrollo):
# la reconstruye con migrate:fresh + E2eSeeder y sirve en el puerto 8010.
# Playwright lo invoca solo (playwright.config.ts → webServer).
set -euo pipefail
cd "$(dirname "$0")/../.."

export DB_DATABASE="${E2E_DB_DATABASE:-sistema_atlantico_e2e}"
case "$DB_DATABASE" in
  *_e2e) ;;
  *) echo "Abortado: la BD '$DB_DATABASE' no es de E2E (debe terminar en _e2e)." >&2; exit 1 ;;
esac
export APP_ENV=local APP_DEBUG=true SESSION_DRIVER=file CACHE_DRIVER=array QUEUE_CONNECTION=sync MAIL_MAILER=array

php artisan config:clear >/dev/null
php artisan migrate:fresh --seed --seeder=Database\\Seeders\\E2eSeeder --force
exec php artisan serve --host=127.0.0.1 --port="${E2E_PORT:-8010}"
