#!/usr/bin/env python3
"""One-shot staging deploy for unified Mateen Laravel app. Passwords via env only."""
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
# Prefer HTTPS subdomain when available
PUBLIC_URL = os.environ.get("STAGING_APP_URL", "https://mateen.ammarelgndy.cloud")
FRONTEND_ORIGINS = os.environ.get(
    "STAGING_CORS",
    "https://mateen.academy,http://187.127.71.130,https://187.127.71.130,https://mateen.ammarelgndy.cloud,http://mateen.ammarelgndy.cloud",
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
    "_obsolete",
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
APP_URL='__APP_URL__'

mkdir -p "$APP_DIR"

# Preserve existing .env (root or legacy backend path)
if [ -f "$APP_DIR/.env" ]; then
  cp "$APP_DIR/.env" /tmp/mateen.env.bak
elif [ -f "$APP_DIR/backend/.env" ]; then
  cp "$APP_DIR/backend/.env" /tmp/mateen.env.bak
fi

# Extract cleanly then sync into APP_DIR
rm -rf /tmp/mateen-release
mkdir -p /tmp/mateen-release
tar -xzf /tmp/mateen-deploy.tar.gz -C /tmp/mateen-release
rsync -a --delete \
  --exclude '.env' \
  --exclude 'storage/logs/' \
  --exclude 'storage/framework/cache/' \
  --exclude 'storage/framework/sessions/' \
  --exclude 'storage/framework/views/' \
  --exclude 'vendor/' \
  --exclude '.git/' \
  /tmp/mateen-release/ "$APP_DIR/"

# Remove legacy nested backend app if present
rm -rf "$APP_DIR/backend"

if [ -f /tmp/mateen.env.bak ]; then
  cp /tmp/mateen.env.bak "$APP_DIR/.env"
else
  cp "$APP_DIR/.env.example" "$APP_DIR/.env"
fi

cd "$APP_DIR"

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
  'FRONTEND_URL': os.environ['APP_URL'],
  'SESSION_SECURE_COOKIE': 'false',
  'DB_CONNECTION': 'mysql',
  'DB_HOST': '127.0.0.1',
  'DB_PORT': '3306',
  'DB_DATABASE': os.environ['DB_NAME'],
  'DB_USERNAME': os.environ['DB_USER'],
}
for k,v in repl.items():
    if re.search(rf'^{k}=', text, flags=re.M):
        text = re.sub(rf'^{k}=.*$', f'{k}={v}', text, flags=re.M)
    else:
        text = re.sub(rf'^#\s*{k}=.*$', f'{k}={v}', text, flags=re.M)
        if not re.search(rf'^{k}=', text, flags=re.M):
            text += f'\n{k}={v}\n'
# Ensure DB_PASSWORD exists
if not re.search(r'^DB_PASSWORD=.+', text, flags=re.M):
    text = re.sub(r'^#\s*DB_PASSWORD=.*$', f"DB_PASSWORD={os.environ['DB_PASS']}", text, flags=re.M)
    if not re.search(r'^DB_PASSWORD=.+', text, flags=re.M):
        text += f"\nDB_PASSWORD={os.environ['DB_PASS']}\n"
cors = os.environ['CORS']
if re.search(r'^CORS_ALLOWED_ORIGINS=', text, flags=re.M):
    text = re.sub(r'^CORS_ALLOWED_ORIGINS=.*$', f'CORS_ALLOWED_ORIGINS={cors}', text, flags=re.M)
else:
    text += f'\nCORS_ALLOWED_ORIGINS={cors}\n'
p.write_text(text)
print('env configured')
PY

# Sync DB user password with .env
mysql -e "CREATE DATABASE IF NOT EXISTS \`$DB_NAME\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;" || true
DB_PASS_EFFECTIVE=$(python3 - <<'PY'
from pathlib import Path
import re
t = Path('.env').read_text()
m = re.search(r'^DB_PASSWORD=(.*)$', t, re.M)
print(m.group(1).strip() if m else '')
PY
)
if [ -n "$DB_PASS_EFFECTIVE" ]; then
  mysql -e "DROP USER IF EXISTS '$DB_USER'@'localhost';" || true
  mysql -e "DROP USER IF EXISTS '$DB_USER'@'127.0.0.1';" || true
  mysql -e "CREATE USER '$DB_USER'@'localhost' IDENTIFIED BY '$DB_PASS_EFFECTIVE';"
  mysql -e "CREATE USER '$DB_USER'@'127.0.0.1' IDENTIFIED BY '$DB_PASS_EFFECTIVE';"
  mysql -e "GRANT ALL PRIVILEGES ON \`$DB_NAME\`.* TO '$DB_USER'@'localhost';"
  mysql -e "GRANT ALL PRIVILEGES ON \`$DB_NAME\`.* TO '$DB_USER'@'127.0.0.1'; FLUSH PRIVILEGES;"
fi

composer install --no-dev --prefer-dist --no-interaction --optimize-autoloader

# Ensure storage framework dirs exist (rsync excludes may omit empty runtime dirs)
mkdir -p storage/framework/{sessions,views,cache/data} storage/logs storage/app/public bootstrap/cache
touch storage/logs/laravel.log

if ! grep -q '^APP_KEY=base64:' .env; then
  php artisan key:generate --force
fi

php artisan migrate --force
php artisan db:seed --force
php artisan storage:link || true
php artisan config:clear
php artisan route:clear
php artisan view:clear || true
chown -R www-data:www-data storage bootstrap/cache
chmod -R ug+rwx storage bootstrap/cache

PHP_SOCK=""
for s in /run/php/php8.4-fpm.sock /run/php/php8.3-fpm.sock /run/php/php-fpm.sock; do
  if [ -S "$s" ]; then PHP_SOCK="$s"; break; fi
done
if [ -z "$PHP_SOCK" ]; then PHP_SOCK="/run/php/php8.4-fpm.sock"; fi

# Default IP vhost
cat >/etc/nginx/sites-available/mateen <<NGINX
server {
    listen 80 default_server;
    listen [::]:80 default_server;
    server_name _;
    root /var/www/mateen/public;
    index index.php index.html;
    client_max_body_size 32M;

    location / {
        try_files \$uri \$uri/ /index.php?\$query_string;
    }

    location ~ \.php\$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:$PHP_SOCK;
        fastcgi_param SCRIPT_FILENAME \$realpath_root\$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_read_timeout 120s;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
NGINX

# Point subdomain at unified app (preserve SSL certbot blocks)
for conf in /etc/nginx/sites-enabled/mateen.ammarelgndy.cloud.conf /etc/nginx/sites-available/mateen.ammarelgndy.cloud.conf; do
  if [ -f "$conf" ]; then
    sed -i 's|root /var/www/clients/mateen.ammarelgndy.cloud/public;|root /var/www/mateen/public;|g' "$conf"
    sed -i 's|root /var/www/mateen/backend/public;|root /var/www/mateen/public;|g' "$conf"
  fi
done

# Remove legacy front-only tree — unified app lives only at /var/www/mateen
if [ -d /var/www/clients/mateen.ammarelgndy.cloud ]; then
  rm -rf /var/www/clients/mateen.ammarelgndy.cloud
  echo "Removed legacy /var/www/clients/mateen.ammarelgndy.cloud"
fi

ln -sfn /etc/nginx/sites-available/mateen /etc/nginx/sites-enabled/mateen
rm -f /etc/nginx/sites-enabled/default
nginx -t
systemctl reload nginx
systemctl reload php8.4-fpm || systemctl reload php8.3-fpm || true

echo HEALTH=$(curl -s -o /dev/null -w '%{http_code}' http://127.0.0.1/up || echo fail)
echo UI=$(curl -s -o /dev/null -w '%{http_code}' http://127.0.0.1/Mateen/html/login.html || echo fail)
echo API=$(curl -s -o /dev/null -w '%{http_code}' -X POST http://127.0.0.1/api/v1/auth/login -H 'Content-Type: application/json' -H 'Accept: application/json' -d '{"email":"admin@mateen.test","password":"password"}' || echo fail)
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
            .replace("__APP_URL__", PUBLIC_URL)
            .replace("__HOST__", HOST)
        )
        with sftp.file("/tmp/mateen-setup.sh", "w") as f:
            f.write(script)
        sftp.close()
        print("Uploaded archive + setup script")
        run(client, "bash /tmp/mateen-setup.sh")
        print("\nStaging deploy finished.")
        print(f"API:       http://{HOST}/api/v1")
        print(f"Health:    http://{HOST}/up")
        print(f"Login UI:  http://{HOST}/Mateen/html/login.html")
        print(f"Subdomain: {PUBLIC_URL}/Mateen/html/login.html")
        print("Seed password for all demo accounts: password")
        print("  admin@mateen.test | supervisor@mateen.test | support@mateen.test")
        print("  teacher@mateen.test | teacher.tafsir@mateen.test (…fiqh/aqeedah/hadeeth/maqraah)")
        print("  student@mateen.test | student2@mateen.test | mateen@mateen.test")
    finally:
        client.close()
        archive.unlink(missing_ok=True)


if __name__ == "__main__":
    main()
