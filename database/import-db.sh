#!/usr/bin/env bash
#
# import-db.sh  —  Import ESTÁNDAR de la BD (Linux/Mac)
#
# Crea la base de datos (si no existe) e importa database/sistema_atlantico.sql.
# Lee la configuración desde el .env del proyecto.
#
# Requisito: MySQL 8 y `mysql` en el PATH.
#
# Uso:   bash database/import-db.sh
#
set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_ROOT="$(dirname "$SCRIPT_DIR")"
ENV_FILE="$PROJECT_ROOT/.env"
SQL_FILE="$SCRIPT_DIR/sistema_atlantico.sql"

get_env() {
  local key="$1" def="${2:-}" val
  if [[ -f "$ENV_FILE" ]]; then
    val="$(grep -E "^[[:space:]]*$key[[:space:]]*=" "$ENV_FILE" | head -n1 | sed -E "s/^[^=]*=//; s/^[\"']//; s/[\"']$//")"
  fi
  echo "${val:-$def}"
}

DB_HOST="$(get_env DB_HOST 127.0.0.1)"
DB_PORT="$(get_env DB_PORT 3306)"
DB_NAME="$(get_env DB_DATABASE sistema_atlantico)"
DB_USER="$(get_env DB_USERNAME root)"
DB_PASS="$(get_env DB_PASSWORD '')"

if [[ ! -f "$SQL_FILE" ]]; then
  echo "ERROR: no existe $SQL_FILE. Hacé git pull primero." >&2
  exit 1
fi

if ! command -v mysql >/dev/null 2>&1; then
  echo "ERROR: no se encontró mysql. Instalá MySQL 8." >&2
  exit 1
fi

[[ -n "$DB_PASS" ]] && export MYSQL_PWD="$DB_PASS"

echo "Asegurando base de datos '$DB_NAME'..."
mysql --host="$DB_HOST" --port="$DB_PORT" --user="$DB_USER" \
  -e "CREATE DATABASE IF NOT EXISTS \`$DB_NAME\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

echo "Importando $SQL_FILE -> '$DB_NAME'..."
mysql --host="$DB_HOST" --port="$DB_PORT" --user="$DB_USER" "$DB_NAME" < "$SQL_FILE"

unset MYSQL_PWD || true
echo "OK. Base de datos '$DB_NAME' importada."
