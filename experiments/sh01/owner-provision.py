#!/usr/bin/env python3
"""OWNER ONLY: provision a new isolated SH-01 schema/account; never alter existing data."""
# SPDX-License-Identifier: GPL-3.0-or-later
import json
import os
from pathlib import Path
import secrets
import subprocess
import sys

SCHEMA = 'msmsh01lab'
ACCOUNT = 'msmsh01lab'
WRAPPER = '/home/marcel/dolibarr-db-p0-20261115/podman-poc'
CONFIG_DIR = Path.home() / '.config/dolibarr-sh01'
REPORT_DIR = Path.home() / 'dolibarr-sh01-20261118'


def main():
    if sys.argv[1:] != ['--run-approved'] or os.getuid() != 1000:
        raise RuntimeError('Run reviewed command as marcel without sudo')
    report = {'scope': 'New SH-01 schema/account only; existing PoC and Dolibarr data unchanged',
              'schema': SCHEMA, 'account': ACCOUNT, 'status': 'STOP'}
    CONFIG_DIR.mkdir(mode=0o700, parents=True, exist_ok=True)
    if CONFIG_DIR.is_symlink() or CONFIG_DIR.stat().st_uid != os.getuid() or CONFIG_DIR.stat().st_mode & 0o077:
        raise RuntimeError('Unsafe credential directory permissions')
    REPORT_DIR.mkdir(mode=0o700, exist_ok=True)
    marker = REPORT_DIR / 'provision-started.json'
    if marker.exists() or (CONFIG_DIR / 'app.json').exists():
        raise RuntimeError('Prior attempt/config exists; inspect without rerunning')
    command = [WRAPPER, 'exec', '-i', 'dolibarr-mysql-poc', 'mysql',
               '--defaults-extra-file=/run/secrets/admin-client.cnf', '--batch', '--skip-column-names']

    def admin(sql):
        p = subprocess.run(command, input=sql + '\n', text=True, capture_output=True, timeout=30)
        if p.returncode:
            raise RuntimeError('Administrative step failed; raw output suppressed; do not retry')
        return p.stdout.strip()

    identity = admin('SELECT @@version,@@version_comment,@@general_log,@@slow_query_log;')
    if identity != '8.4.13\tMySQL Community Server - GPL\t0\t0':
        raise RuntimeError('Unexpected server/logging profile; no mutation')
    if admin("SELECT COUNT(*) FROM information_schema.SCHEMATA WHERE SCHEMA_NAME='"+SCHEMA+"';") != '0':
        raise RuntimeError('Target schema exists; no mutation')
    if admin("SELECT COUNT(*) FROM mysql.user WHERE User='"+ACCOUNT+"';") != '0':
        raise RuntimeError('Target account exists; no mutation')
    with marker.open('x') as f:
        json.dump({'schema': SCHEMA, 'account': ACCOUNT, 'mode': 'Owner provision started; no automatic retry'}, f)
    password = secrets.token_hex(32)
    sql = (f'CREATE DATABASE `{SCHEMA}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;\n'
           f'CREATE TABLE `{SCHEMA}`.`probe` (id INT NOT NULL PRIMARY KEY, label VARCHAR(64) NOT NULL, n BIGINT NOT NULL) ENGINE=InnoDB;\n'
           f"INSERT INTO `{SCHEMA}`.`probe` VALUES (1,'isolated-counter',0),(2,'isolation-counter',0);\n"
           f"CREATE USER '{ACCOUNT}'@'%' IDENTIFIED BY '{password}';\n"
           f"GRANT SELECT,INSERT,UPDATE,DELETE ON `{SCHEMA}`.* TO '{ACCOUNT}'@'%';")
    try:
        admin(sql)
        # No P_S/admin/global grants, existing account changes, global/session configuration or cleanup.
        config = {'host': '127.0.0.1', 'port': 3307, 'database': SCHEMA, 'user': ACCOUNT, 'password': password}
        fd = os.open(CONFIG_DIR/'app.json', os.O_CREAT | os.O_EXCL | os.O_WRONLY, 0o600)
        with os.fdopen(fd, 'w') as f:
            json.dump(config, f)
        report.update({'status': 'PASS', 'version': '8.4.13',
                       'privileges': 'SELECT,INSERT,UPDATE,DELETE on msmsh01lab.* only',
                       'performance_schema_grant': False, 'existing_accounts_changed': False,
                       'synthetic_tables': 1, 'synthetic_seed_rows': 2,
                       'application_config_written_without_output': True})
    except Exception as e:
        report['failure_class'] = type(e).__name__
        raise
    finally:
        (REPORT_DIR/'provision-result.json').write_text(json.dumps(report, indent=2)+'\n')
    print(json.dumps(report))


if __name__ == '__main__':
    try:
        main()
    except Exception as e:
        print(json.dumps({'status': 'STOP', 'reason': str(e) if isinstance(e, RuntimeError) else type(e).__name__,
                          'action': 'Preserve state; do not repeat automatically'}))
        sys.exit(1)
