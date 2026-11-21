#!/usr/bin/env python3
"""Drop one selected COM_QUERY reply on a dedicated loopback lab socket; never log packets."""
# SPDX-License-Identifier: GPL-3.0-or-later
import json
from pathlib import Path
import socket
import subprocess
import sys
import threading


def packet(s):
    def read(n):
        buf = bytearray()
        while len(buf) < n:
            b = s.recv(n - len(buf))
            if not b:
                raise EOFError()
            buf.extend(b)
        return bytes(buf)
    header = read(4)
    length = int.from_bytes(header[:3], 'little')
    if length > 1024 * 1024:
        raise ValueError('Lab packet too large; no packet logged')
    return header, read(length)


def run(kind, output):
    path = Path(output)
    if path.exists() or Path(output+'.started').exists():
        raise RuntimeError('Prior evidence exists; do not repeat automatically')
    Path(output+'.started').write_text(json.dumps({'kind': kind, 'target': 'isolated cf03_restricted_lab_fixture connection only'}))
    listener = socket.socket()
    listener.bind(('127.0.0.1', 0))
    listener.listen(1)
    listener.settimeout(15)
    pending = threading.Event()
    stop = threading.Event()
    state = {'forwarded_target': 0, 'server_ok_discarded': 0, 'raw_packets_logged': 0,
             'scope': 'one dedicated loopback client; no KILL or server restart'}
    sockets = []

    def close_sockets():
        stop.set()
        for s in sockets:
            try:
                s.shutdown(socket.SHUT_RDWR)
            except OSError:
                pass

    def client_to_server(client, server):
        try:
            while not stop.is_set():
                header, body = packet(client)
                # Auth and other packets are forwarded as opaque bytes, never decoded/stored.
                if body == b'\x03'+kind.encode('ascii'):
                    pending.set()
                    state['forwarded_target'] += 1
                server.sendall(header+body)
        except (EOFError, OSError):
            close_sockets()

    def server_to_client(server, client):
        try:
            while not stop.is_set():
                header, body = packet(server)
                if pending.is_set():
                    if not body or body[0] != 0:
                        state['unexpected_target_reply'] = True
                    else:
                        state['server_ok_discarded'] += 1
                    close_sockets()
                    return
                client.sendall(header+body)
        except (EOFError, OSError):
            close_sockets()

    command = ['php', '-d', 'xdebug.mode=off', str(Path(__file__).with_suffix('.php')),
               '--run-approved', kind, str(listener.getsockname()[1])]
    worker = subprocess.Popen(command, stdout=subprocess.PIPE, stderr=subprocess.PIPE, text=True)
    try:
        client, _ = listener.accept()
        server = socket.create_connection(('127.0.0.1', 3307), timeout=8)
        sockets.extend([client, server])
        threads = [threading.Thread(target=client_to_server, args=(client, server), daemon=True),
                   threading.Thread(target=server_to_client, args=(server, client), daemon=True)]
        for t in threads:
            t.start()
        stdout, stderr = worker.communicate(timeout=25)
        data = json.loads(stdout)
        data['proxy'] = state
        path.write_text(json.dumps(data, indent=2)+'\n')
        if worker.returncode or data.get('status') != 'PASS' or state['forwarded_target'] != 1 or state['server_ok_discarded'] != 1:
            raise RuntimeError('Reply-loss worker stopped; preserve state and do not rerun')
        print(json.dumps({'case': 'D01-LOST-'+kind, 'status': 'PASS (real discarded reply)', 'Core_return': data['Core_return'], 'persisted': data['persisted'], 'proxy': state}))
    finally:
        close_sockets()
        for s in sockets:
            s.close()
        listener.close()
        if worker.poll() is None:
            worker.terminate()
            worker.wait(timeout=5)


if __name__ == '__main__':
    if len(sys.argv) != 4 or sys.argv[1] != '--run-approved' or sys.argv[2] not in ('COMMIT', 'ROLLBACK'):
        raise SystemExit('Usage: lost-ack.py --run-approved COMMIT|ROLLBACK output.json')
    try:
        run(sys.argv[2], sys.argv[3])
    except Exception as e:
        print(json.dumps({'status': 'STOP', 'reason': str(e) if isinstance(e, RuntimeError) else type(e).__name__,
                          'action': 'Preserve lab state; do not rerun automatically'}))
        raise SystemExit(1)
