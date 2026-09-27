#!/usr/bin/env bash
#
# export-db.sh  —  Export ESTÁNDAR de la BD (Linux/Mac)
#
# Genera database/sistema_atlantico.sql en formato MySQL 8 NATIVO, idéntico
# sin importar quién lo corra. Lee la configuración desde el .env del proyecto.
#
# Requisito: MySQL 8 y `mysqldump` en el PATH. NO usar el de MariaDB.
#
# Uso:   bash database/export-db.sh
#
set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_ROOT="$(dirname "$SCRIPT_DIR")"
ENV_FILE="$PROJECT_ROOT/.env"
OUT_FILE="$SCRIPT_DIR/sistema_atlantico.sql"

get_env() {  # get_env CLAVE DEFAULT
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

if ! command -v mysqldump >/dev/null 2>&1; then
  echo "ERROR: no se encontró mysqldump. Instalá MySQL 8." >&2
  exit 1
fi

[[ -n "$DB_PASS" ]] && export MYSQL_PWD="$DB_PASS"

echo "Exportando '$DB_NAME' ($DB_HOST:$DB_PORT) -> $OUT_FILE"
mysqldump \
  --host="$DB_HOST" --port="$DB_PORT" --user="$DB_USER" \
  --single-transaction \
  --no-tablespaces \
  --set-gtid-purged=OFF \
  --default-character-set=utf8mb4 \
  --add-drop-table \
  --result-file="$OUT_FILE" \
  "$DB_NAME"

unset MYSQL_PWD || true
echo "OK. Dump generado. Ahora: git add database/sistema_atlantico.sql && git commit"
