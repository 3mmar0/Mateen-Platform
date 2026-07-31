#!/usr/bin/env python3
"""Install GitHub Actions deploy public key on a VPS (staging or production)."""
from __future__ import annotations

import os
from pathlib import Path

import paramiko

HOST = os.environ.get("SSH_HOST") or os.environ.get("PROD_SSH_HOST") or os.environ.get("STAGING_SSH_HOST", "")
PASSWORD = os.environ.get("SSH_PASSWORD") or os.environ.get("PROD_SSH_PASSWORD") or os.environ.get("STAGING_SSH_PASSWORD", "")
PUBKEY = Path(
    os.environ.get(
        "DEPLOY_PUBKEY_PATH",
        str(Path.home() / ".ssh" / "mateen-deploy" / "id_ed25519.pub"),
    )
).read_text().strip()

if not HOST or not PASSWORD:
    raise SystemExit("Set SSH_HOST and SSH_PASSWORD (or PROD_/STAGING_ variants)")

c = paramiko.SSHClient()
c.set_missing_host_key_policy(paramiko.AutoAddPolicy())
c.connect(HOST, username="root", password=PASSWORD, timeout=30)
cmd = f"""
set -euo pipefail
mkdir -p /root/.ssh
chmod 700 /root/.ssh
touch /root/.ssh/authorized_keys
chmod 600 /root/.ssh/authorized_keys
grep -qxF '{PUBKEY}' /root/.ssh/authorized_keys || echo '{PUBKEY}' >> /root/.ssh/authorized_keys
echo OK installed on $(hostname) $(hostname -I | awk '{{print $1}}')
"""
_stdin, stdout, stderr = c.exec_command(cmd, timeout=60)
print(stdout.read().decode())
err = stderr.read().decode()
if err.strip():
    print(err)
code = stdout.channel.recv_exit_status()
c.close()
raise SystemExit(code)
