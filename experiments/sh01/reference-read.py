#!/usr/bin/env python3
"""Unauthenticated reference reachability only; no login, bootstrap script or business action."""
# SPDX-License-Identifier: GPL-3.0-or-later
import json
from pathlib import Path
import sys
import urllib.request
opener = urllib.request.build_opener(urllib.request.ProxyHandler({}))
result = {'configured_url': 'http://i105-v24.localhost:8091/', 'authenticated': False,
          'business_requests': 0, 'database_before_after_audit': 'NOT EXECUTED'}
for label, url in [('configured_name','http://i105-v24.localhost:8091/'), ('loopback_with_host','http://127.0.0.1:8091/')]:
    try:
        request = urllib.request.Request(url, headers={'Host':'i105-v24.localhost:8091'})
        with opener.open(request, timeout=6) as r:
            body = r.read(150000)
            result[label] = {'status':r.status, 'Dolibarr_marker':b'Dolibarr' in body,
                             'version_24_0_3_marker':b'24.0.3' in body}
    except Exception as e:
        result[label] = {'error_class':type(e).__name__, 'reason_class':type(getattr(e,'reason',None)).__name__}
Path(sys.argv[1]).write_text(json.dumps(result, indent=2)+'\n')
print(json.dumps(result))
