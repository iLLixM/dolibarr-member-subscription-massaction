#!/bin/sh
# SPDX-License-Identifier: GPL-3.0-or-later
# No database, mail, HTTP requests or business-object writes.
set -eu
root="$(CDPATH= cd -- "$(dirname -- "$0")/.." && pwd)"
cd "$root"
echo "CF-02 source: $(git rev-parse HEAD)"
find class lib test/unit test/fixtures -name '*.php' -type f -print0 | xargs -0 -n1 php -l > /dev/null
php test/unit/coreguarded.php
php test/unit/f29-rights.php
php test/unit/coreguarded-session.php
php test/unit/cf02-contribution-witness.php
php test/unit/cf03-lab.php
php test/unit/cf03-reader.php
php test/unit/cf03-runbook.php
php test/unit/cf03-evidence.php
php test/unit/cf03-source.php
php test/unit/run.php
php test/unit/database.php
node test/unit/loading.js
if [ -n "${DOLIBARR_TEST_ROOT:-}" ]; then
  php test/unit/booking.php
  php test/unit/f29-book-flow.php
  php test/unit/invoice.php
  php test/unit/postcommit.php
  php test/unit/profile.php
  php test/unit/request.php
  php test/unit/userreload.php
else
  echo 'NOT EXECUTED: pinned-Core fixture tests (set DOLIBARR_TEST_ROOT to the checkout containing htdocs/).'
fi
echo 'CF-02 and CF-03 pure synthetics complete; CF-03 live A-D preflight/HTTP/business/fault work remains NOT EXECUTED.'
