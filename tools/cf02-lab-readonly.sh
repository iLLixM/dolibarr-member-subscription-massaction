#!/bin/sh
# SPDX-License-Identifier: GPL-3.0-or-later
# Read-only CF-02 lab privilege, session-mode, schema and engine inspection.
set -eu
if [ "${1:-}" != '--check-only' ]; then
  echo 'Usage: CF02_DB_FAMILY=mysql CF02_DB_NAME=... CF02_DB_USER=... CF02_DB_HOST=... CF02_DB_PORT=... sh tools/cf02-lab-readonly.sh --check-only' >&2
  exit 64
fi
: "${CF02_DB_FAMILY:?mysql or mariadb required}"
: "${CF02_DB_NAME:?target schema required}"
: "${CF02_DB_USER:?application account required}"
: "${CF02_DB_HOST:?host required}"
: "${CF02_DB_PORT:?port required}"
case "$CF02_DB_FAMILY" in mysql|mariadb) ;; *) echo 'Unsupported database family' >&2; exit 64;; esac
case "$CF02_DB_PORT" in *[!0-9]*|'') echo 'Invalid port' >&2; exit 64;; esac
case "${CF02_TABLE_PREFIX:-llx_}" in *[!a-zA-Z0-9_]*|'') echo 'Invalid table prefix' >&2; exit 64;; esac
PREFIX="${CF02_TABLE_PREFIX:-llx_}"
if ! command -v mysql >/dev/null 2>&1; then echo 'MySQL CLI unavailable' >&2; exit 69; fi
if ! command -v git >/dev/null 2>&1; then echo 'Git unavailable' >&2; exit 69; fi
printf 'CF02 git HEAD: %s\n' "$(git rev-parse HEAD)"
if [ -n "$(git status --porcelain)" ]; then echo 'WARNING: working tree contains uncommitted changes'; fi
printf 'Target: %s / %s / %s (family %s)\n' "$CF02_DB_HOST" "$CF02_DB_PORT" "$CF02_DB_NAME" "$CF02_DB_FAMILY"
echo 'Step A: enter the database account password (no password is printed).'
mysql --protocol=TCP -h "$CF02_DB_HOST" -P "$CF02_DB_PORT" -u "$CF02_DB_USER" -p -D "$CF02_DB_NAME" --table -e "
SELECT USER() AS login_identity, CURRENT_USER() AS effective_account, CURRENT_ROLE() AS roles,
       DATABASE() AS db_name, VERSION() AS server_version,
       @@session.autocommit AS autocommit, @@session.completion_type AS completion_mode;
SHOW GRANTS;
SELECT TABLE_NAME, ENGINE FROM information_schema.TABLES
 WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME IN ('${PREFIX}adherent','${PREFIX}adherent_type','${PREFIX}subscription','${PREFIX}actioncomm','${PREFIX}actioncomm_resources')
 ORDER BY TABLE_NAME;
"
if [ "$CF02_DB_FAMILY" = 'mysql' ]; then
  echo 'Step B: enter the same password for the expected Performance Schema refusal.'
  temp_out="$(mktemp)"; trap 'rm -f "$temp_out"' EXIT HUP INT TERM
  if mysql --protocol=TCP -h "$CF02_DB_HOST" -P "$CF02_DB_PORT" -u "$CF02_DB_USER" -p -D "$CF02_DB_NAME" -e 'SELECT THREAD_ID FROM performance_schema.events_transactions_current LIMIT 0' > "$temp_out" 2>&1; then
    echo 'FAIL: account can SELECT from performance_schema.events_transactions_current' >&2
    rm -f "$temp_out"; exit 1
  fi
  if ! grep -q 'ERROR 1142 (42000)' "$temp_out"; then
    echo 'STOP: expected 1142 but got a different client/database error (redact before sharing).' >&2
    rm -f "$temp_out"; exit 1
  fi
  echo 'PASS: expected ERROR 1142 (no Performance Schema SELECT).'
  rm -f "$temp_out"
fi
echo 'READ-ONLY CHECK COMPLETED; no business SQL writes, BEGIN, COMMIT or ROLLBACK issued.'
