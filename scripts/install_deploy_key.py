#!/usr/bin/env python3
"""Install GitHub Actions deploy public key on staging VPS."""
from __future__ import annotations

import os
from pathlib import Path

import paramiko

HOST = os.environ.get("STAGING_SSH_HOST", "187.127.71.130")
PASSWORD = os.environ["STAGING_SSH_PASSWORD"]
PUBKEY = Path(os.environ.get(
    "DEPLOY_PUBKEY_PATH",
    str(Path.home() / ".ssh" / "mateen-deploy" / "id_ed25519.pub"),
)).read_text().strip()

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
# prepare git checkout for CI deploys
if [ ! -d /var/www/mateen/.git ]; then
  cd /var/www/mateen
  git init
  git checkout -b main || true
fi
echo OK
"""
stdin, stdout, stderr = c.exec_command(cmd, timeout=60)
print(stdout.read().decode())
err = stderr.read().decode()
if err.strip():
    print(err)
code = stdout.channel.recv_exit_status()
c.close()
raise SystemExit(code)
