#!/usr/bin/env python3
"""One-shot staging deploy for Mateen monorepo. Passwords via env only."""
from __future__ import annotations

import os
import secrets
import sys
import tarfile
import tempfile
from pathlib import Path

import paramiko

ROOT = Path(__file__).resolve().parents[1]
HOST = os.environ.get("STAGING_SSH_HOST", "187.127.71.130")
USER = os.environ.get("STAGING_SSH_USER", "root")
PASSWORD = os.environ.get("STAGING_SSH_PASSWORD", "")
APP_DIR = os.environ.get("STAGING_APP_DIR", "/var/www/mateen")
DB_NAME = os.environ.get("STAGING_DB_NAME", "mateen")
DB_USER = os.environ.get("STAGING_DB_USER", "mateen")
DB_PASS = os.environ.get("STAGING_DB_PASS") or secrets.token_urlsafe(18)
FRONTEND_ORIGINS = os.environ.get(
    "STAGING_CORS",
    "https://mateen.academy,http://187.127.71.130,https://187.127.71.130",
)

SKIP_NAMES = {
    ".git",
    "node_modules",
    "vendor",
    ".cursor",
    "__pycache__",
    "terminals",
    "mcps",
    "agent-transcripts",
}


def connect() -> paramiko.SSHClient:
    if not PASSWORD:
        raise SystemExit("Set STAGING_SSH_PASSWORD")
    client = paramiko.SSHClient()
    client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
    client.connect(HOST, username=USER, password=PASSWORD, timeout=45)
    return client


def run(client: paramiko.SSHClient, cmd: str, check: bool = True) -> str:
    print(f"$ {cmd}")
    _stdin, stdout, stderr = client.exec_command(cmd, timeout=900)
    out = stdout.read().decode(errors="replace")
    err = stderr.read().decode(errors="replace")
    code = stdout.channel.recv_exit_status()
    if out.strip():
        print(out.rstrip())
    if err.strip():
        print(err.rstrip(), file=sys.stderr)
    if check and code != 0:
        raise SystemExit(f"Command failed ({code}): {cmd}")
    return out


def make_archive() -> Path:
    tmp = Path(tempfile.mkstemp(suffix=".tar.gz")[1])
    with tarfile.open(tmp, "w:gz") as tar:
        for path in ROOT.rglob("*"):
            if not path.is_file():
                continue
            rel = path.relative_to(ROOT).as_posix()
            parts = set(rel.split("/"))
            if parts & SKIP_NAMES:
                continue
            if path.name.startswith(".env") and path.name != ".env.example":
                continue
            if "/storage/logs/" in rel or "/storage/framework/" in rel:
                continue
            tar.add(path, arcname=rel)
    return tmp


REMOTE_SETUP = r"""
set -euo pipefail
APP_DIR='__APP_DIR__'
DB_NAME='__DB_NAME__'
DB_USER='__DB_USER__'
DB_PASS='__DB_PASS__'
CORS='__CORS__'
APP_URL='http://__HOST__'

mkdir -p "$APP_DIR"
cd "$APP_DIR"
tar -xzf /tmp/mateen-deploy.tar.gz

mysql -e "CREATE DATABASE IF NOT EXISTS \`$DB_NAME\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
mysql -e "DROP USER IF EXISTS '$DB_USER'@'localhost';"
mysql -e "DROP USER IF EXISTS '$DB_USER'@'127.0.0.1';"
mysql -e "CREATE USER '$DB_USER'@'localhost' IDENTIFIED BY '$DB_PASS';"
mysql -e "CREATE USER '$DB_USER'@'127.0.0.1' IDENTIFIED BY '$DB_PASS';"
mysql -e "GRANT ALL PRIVILEGES ON \`$DB_NAME\`.* TO '$DB_USER'@'localhost';"
mysql -e "GRANT ALL PRIVILEGES ON \`$DB_NAME\`.* TO '$DB_USER'@'127.0.0.1'; FLUSH PRIVILEGES;"

cd "$APP_DIR/backend"
[ -f .env ] || cp .env.example .env

export APP_URL DB_NAME DB_USER DB_PASS CORS
python3 <<'PY'
from pathlib import Path
import re, os
p = Path('.env')
text = p.read_text()
repl = {
  'APP_NAME': 'Mateen',
  'APP_ENV': 'staging',
  'APP_DEBUG': 'true',
  'APP_URL': os.environ['APP_URL'],
  'DB_CONNECTION': 'mysql',
  'DB_HOST': '127.0.0.1',
  'DB_PORT': '3306',
  'DB_DATABASE': os.environ['DB_NAME'],
  'DB_USERNAME': os.environ['DB_USER'],
  'DB_PASSWORD': os.environ['DB_PASS'],
  'FRONTEND_URL': 'https://mateen.academy',
  'SESSION_DRIVER': 'database',
  'QUEUE_CONNECTION': 'database',
  'CACHE_STORE': 'database',
  'SESSION_SECURE_COOKIE': 'false',
}
for key in ('DB_HOST','DB_PORT','DB_DATABASE','DB_USERNAME','DB_PASSWORD'):
    text = re.sub(rf'^#\s*({key}=)', r'\1', text, flags=re.M)
for k,v in repl.items():
    if re.search(rf'^{k}=', text, flags=re.M):
        text = re.sub(rf'^{k}=.*$', f'{k}={v}', text, flags=re.M)
    else:
        text += f'\n{k}={v}\n'
cors = os.environ['CORS']
if re.search(r'^CORS_ALLOWED_ORIGINS=', text, flags=re.M):
    text = re.sub(r'^CORS_ALLOWED_ORIGINS=.*$', f'CORS_ALLOWED_ORIGINS={cors}', text, flags=re.M)
else:
    text += f'\nCORS_ALLOWED_ORIGINS={cors}\n'
p.write_text(text)
print('env updated')
PY

composer install --no-dev --prefer-dist --no-interaction --optimize-autoloader
php artisan key:generate --force
php artisan migrate --force
php artisan db:seed --force
php artisan storage:link || true
php artisan config:clear
php artisan route:clear
chown -R www-data:www-data storage bootstrap/cache
chmod -R ug+rwx storage bootstrap/cache

cat >/etc/nginx/sites-available/mateen <<'NGINX'
server {
    listen 80 default_server;
    listen [::]:80 default_server;
    server_name _;
    root /var/www/mateen/backend/public;
    index index.php index.html;
    client_max_body_size 32M;

    location /Mateen/ {
        alias /var/www/mateen/;
        try_files $uri $uri/ =404;
    }

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/run/php/php8.4-fpm.sock;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
NGINX

ln -sfn /etc/nginx/sites-available/mateen /etc/nginx/sites-enabled/mateen
rm -f /etc/nginx/sites-enabled/default
nginx -t
systemctl reload nginx
systemctl reload php8.4-fpm || true

echo HEALTH=$(curl -s -o /dev/null -w '%{http_code}' http://127.0.0.1/up || echo fail)
echo API=$(curl -s -o /dev/null -w '%{http_code}' http://127.0.0.1/api/v1/auth/login || echo fail)
"""


def main() -> None:
    print(f"Packaging from {ROOT} ...")
    archive = make_archive()
    print(f"Archive: {archive} ({archive.stat().st_size // 1024} KB)")

    client = connect()
    try:
        sftp = client.open_sftp()
        sftp.put(str(archive), "/tmp/mateen-deploy.tar.gz")
        script = (
            REMOTE_SETUP.replace("__APP_DIR__", APP_DIR)
            .replace("__DB_NAME__", DB_NAME)
            .replace("__DB_USER__", DB_USER)
            .replace("__DB_PASS__", DB_PASS)
            .replace("__CORS__", FRONTEND_ORIGINS)
            .replace("__HOST__", HOST)
        )
        with sftp.file("/tmp/mateen-setup.sh", "w") as f:
            f.write(script)
        sftp.close()
        print("Uploaded archive + setup script")
        run(client, "bash /tmp/mateen-setup.sh")
        print("\nStaging deploy finished.")
        print(f"API:     http://{HOST}/api/v1")
        print(f"Health:  http://{HOST}/up")
        print(f"Login UI: http://{HOST}/Mateen/html/login.html")
        print("Seed users: admin@mateen.test / password (and other roles@mateen.test)")
    finally:
        client.close()
        archive.unlink(missing_ok=True)


if __name__ == "__main__":
    main()
