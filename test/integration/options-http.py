#!/usr/bin/env python3
# Copyright (C) 2026 iLLixM
# SPDX-License-Identifier: GPL-3.0-or-later
"""Focused, approved local read-only integration; no JavaScript or business writes."""
import argparse
import getpass
import http.cookiejar
import json
import re
import sys
import urllib.error
import urllib.parse
import urllib.request
from datetime import datetime, timezone
from html.parser import HTMLParser
from pathlib import Path


class Page(HTMLParser):
    def __init__(self, body):
        super().__init__()
        self.inputs, self.options, self.text = [], [], []
        self.feed(body)

    def handle_starttag(self, tag, attrs):
        attrs = dict(attrs)
        if tag == 'input':
            self.inputs.append(attrs)
        if tag == 'option':
            self.options.append(attrs.get('value'))

    def handle_data(self, data):
        self.text.append(data)

    def field(self, name):
        return next((x for x in self.inputs if x.get('name') == name), {})

    def form(self):
        return {x['name']: x.get('value', '') for x in self.inputs
                if 'name' in x and 'disabled' not in x
                and x.get('type') not in ('submit', 'button')
                and (x.get('type') not in ('checkbox', 'radio') or 'checked' in x)}

    def contains(self, pattern):
        return bool(re.search(pattern, ' '.join(self.text), re.I))

    def denied(self):
        return self.contains(r'Access forbidden|Zugriff verweigert|Access denied')


class Client:
    def __init__(self, base):
        self.base = base
        self.jar = http.cookiejar.CookieJar()
        self.opener = urllib.request.build_opener(urllib.request.HTTPCookieProcessor(self.jar))

    def request(self, path, data=None):
        if not path.startswith('/') or path.startswith('//'):
            raise RuntimeError('Unexpected local path')
        req = urllib.request.Request(self.base + path, data=None if data is None else urllib.parse.urlencode(data).encode())
        try:
            response = self.opener.open(req, timeout=30)
        except urllib.error.HTTPError as error:
            response = error
        destination = urllib.parse.urlsplit(response.geturl())
        if destination.netloc != urllib.parse.urlsplit(self.base).netloc:
            raise RuntimeError('Unexpected redirect origin')
        page = Page(response.read().decode('utf-8', 'replace'))
        return destination.path + ('?' + destination.query if destination.query else ''), page

    def login(self, name):
        _, page = self.request('/index.php')
        form = page.form()
        password = getpass.getpass('Password for ' + name + ': ')
        form.update(username=name, password=password, actionlogin='login', loginfunction='loginfunction')
        _, page = self.request('/index.php', form)
        del password, form
        if page.field('password'):
            raise RuntimeError('Authentication failed')

    def logout(self):
        self.request('/user/logout.php')
        self.jar.clear()


def main():
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument('--approved-read-only', action='store_true', required=True)
    parser.add_argument('--url', required=True)
    parser.add_argument('--admin', required=True)
    parser.add_argument('--member-reader', required=True)
    parser.add_argument('--restricted', required=True)
    parser.add_argument('--output', type=Path, required=True)
    args = parser.parse_args()
    base = args.url.rstrip('/')
    parsed = urllib.parse.urlsplit(base)
    if parsed.hostname not in ('127.0.0.1', 'localhost', '::1') or parsed.scheme not in ('http', 'https') or parsed.path or parsed.query or parsed.fragment or parsed.username:
        parser.error('Use an approved loopback installation URL without credentials or a path.')
    results = []

    def record(case, ok):
        results.append({'case': case, 'result': 'PASS' if ok else 'FAIL'})
        args.output.parent.mkdir(parents=True, exist_ok=True)
        args.output.write_text(json.dumps({'executed_at': datetime.now(timezone.utc).isoformat(),
            'transport': 'Real HTTP sessions; no JavaScript execution or browser rendering', 'cases': results}, indent=2) + '\n')
        print(case + ': ' + results[-1]['result'], flush=True)
        if not ok:
            raise RuntimeError('Assertion failed: ' + case)

    wizard = '/custom/membersubscriptionmassaction/subscription.php'
    client = Client(base)
    try:
        client.login(args.admin)
        _, page = client.request('/adherents/list.php')
        selected = page.field('toselect[]').get('value')
        if not selected or 'memberSubscriptionMassactionStart' not in page.options:
            raise RuntimeError('No authorized existing selection available')
        path, page = client.request('/adherents/list.php', {'token': page.field('token')['value'],
            'massaction': 'memberSubscriptionMassactionStart', 'confirmmassaction': 'Confirm', 'toselect[]': selected})
        if not path.startswith(wizard + '?preview=') or not page.field('preview'):
            raise RuntimeError('Preview unavailable')

        def update(changes):
            nonlocal page, path
            data = page.form()
            data['action'] = 'refresh'
            for key, value in changes.items():
                if value is None:
                    data.pop(key, None)
                else:
                    data[key] = value
            path, page = client.request(wizard, data)

        update({'invoice': '1', 'autocreate': '1'})
        record('OPT-HTTP-01', 'checked' in page.field('autocreate') and 'disabled' not in page.field('autocreate'))
        # Normal native form: previously checked autocreate is still submitted.
        update({'invoice': None})
        record('OPT-HTTP-02', 'checked' not in page.field('invoice') and not page.contains(r'Invalid or outdated form|Ungültiges oder veraltetes Formular'))
        record('OPT-HTTP-03', 'disabled' in page.field('autocreate') and 'checked' not in page.field('autocreate'))
        update({'invoice': '1'})
        record('OPT-HTTP-04', 'disabled' not in page.field('autocreate') and 'checked' not in page.field('autocreate'))
        update({'autocreate[]': '1'})
        record('OPT-HTTP-05', page.contains(r'Invalid or outdated form|Ungültiges oder veraltetes Formular'))
        update({'amount': '999'})
        record('OPT-HTTP-06', page.contains(r'Invalid or outdated form|Ungültiges oder veraltetes Formular'))
        preview_path = path
        revision = page.field('revision').get('value')
        data = page.form(); data.update(action='refresh', token='invalid-local-test-token')
        _, denied = client.request(wizard, data)
        csrf_denied = not denied.field('preview') and (denied.denied() or denied.contains(r'Invalid.*token|token.*invalid|CSRF|token.*ungültig|This selection is unavailable|Diese Auswahl ist nicht verfügbar'))
        path, page = client.request(preview_path)
        csrf_denied = csrf_denied and page.field('revision').get('value') == revision
        data = page.form(); data['action'] = 'cancel'
        path, page = client.request(wizard, data)
        record('OPT-HTTP-08', csrf_denied and path.split('?')[0] == '/adherents/list.php')
    finally:
        client.logout()

    for name, list_allowed, suffix in [(args.member_reader, True, 'reader'), (args.restricted, False, 'restricted')]:
        client = Client(base)
        try:
            client.login(name)
            _, page = client.request('/adherents/list.php')
            list_ok = page.denied() != list_allowed and 'memberSubscriptionMassactionStart' not in page.options
            _, page = client.request(wizard + '?preview=' + 'a' * 32)
            record('OPT-HTTP-07-' + suffix, list_ok and page.denied())
        finally:
            client.logout()
    print('Focused options integration passed; no business-write action submitted.')


if __name__ == '__main__':
    try:
        main()
    except Exception:
        # Do not leak response bodies, session URLs, member details or authentication data.
        print('Focused integration failed; inspect case results privately.', file=sys.stderr)
        sys.exit(1)
