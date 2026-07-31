#!/usr/bin/env python3
"""Deploy unified Mateen Laravel app to production (mateen.academy / Apache)."""
from __future__ import annotations

import os
import secrets
import sys
import tarfile
import tempfile
from pathlib import Path

import paramiko

ROOT = Path(__file__).resolve().parents[1]
HOST = os.environ.get("PROD_SSH_HOST", "31.97.122.143")
USER = os.environ.get("PROD_SSH_USER", "root")
PASSWORD = os.environ.get("PROD_SSH_PASSWORD", "")
APP_DIR = os.environ.get("PROD_APP_DIR", "/var/www/mateen")
DB_NAME = os.environ.get("PROD_DB_NAME", "mateen")
DB_USER = os.environ.get("PROD_DB_USER", "mateen")
DB_PASS = os.environ.get("PROD_DB_PASS") or (
    "App_" + secrets.token_urlsafe(12) + "_9Z!"
)
PUBLIC_URL = os.environ.get("PROD_APP_URL", "https://mateen.academy")
FRONTEND_ORIGINS = os.environ.get(
    "PROD_CORS",
    "https://mateen.academy,https://www.mateen.academy,http://mateen.academy,http://www.mateen.academy",
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
    "specs",
    ".specify",
    "codex",
}


def connect() -> paramiko.SSHClient:
    if not PASSWORD:
        raise SystemExit("Set PROD_SSH_PASSWORD")
    client = paramiko.SSHClient()
    client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
    client.connect(HOST, username=USER, password=PASSWORD, timeout=45)
    return client


def run(client: paramiko.SSHClient, cmd: str, check: bool = True) -> str:
    print(f"$ {cmd[:160]}{'...' if len(cmd) > 160 else ''}")
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
            if path.name.startswith("_inspect_") or path.name.startswith("_smoke_"):
                continue
            if "/storage/logs/" in rel or "/storage/framework/" in rel:
                continue
            if path.suffix.lower() in {".apk", ".mp4", ".zip"} and "public/Mateen" not in rel:
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

# Preserve existing .env
if [ -f "$APP_DIR/.env" ]; then
  cp "$APP_DIR/.env" /tmp/mateen.env.bak
elif [ -f "$APP_DIR/backend/.env" ]; then
  cp "$APP_DIR/backend/.env" /tmp/mateen.env.bak
fi

# Backup legacy static front once
if [ -d /var/www/mateen.academy/Mateen ] && [ ! -d /var/www/mateen.academy.static.bak ]; then
  mv /var/www/mateen.academy /var/www/mateen.academy.static.bak
  echo "Backed up legacy static front to /var/www/mateen.academy.static.bak"
fi

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
  --exclude '_obsolete/' \
  --exclude 'specs/' \
  /tmp/mateen-release/ "$APP_DIR/"

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
  'APP_ENV': 'production',
  'APP_DEBUG': 'false',
  'APP_URL': os.environ['APP_URL'],
  'FRONTEND_URL': os.environ['APP_URL'],
  'SESSION_SECURE_COOKIE': 'true',
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
# Always sync DB_PASSWORD from deploy input so policy-safe password is used
if re.search(r'^DB_PASSWORD=', text, flags=re.M):
    text = re.sub(r'^DB_PASSWORD=.*$', f"DB_PASSWORD={os.environ['DB_PASS']}", text, flags=re.M)
else:
    text += f"\nDB_PASSWORD={os.environ['DB_PASS']}\n"
cors = os.environ['CORS']
if re.search(r'^CORS_ALLOWED_ORIGINS=', text, flags=re.M):
    text = re.sub(r'^CORS_ALLOWED_ORIGINS=.*$', f'CORS_ALLOWED_ORIGINS={cors}', text, flags=re.M)
else:
    text += f'\nCORS_ALLOWED_ORIGINS={cors}\n'
p.write_text(text)
print('env configured')
PY

# Create DB + user via debian-sys-maint (root may require password)
DB_PASS_EFFECTIVE=$(python3 - <<'PY'
from pathlib import Path
import re
t = Path('.env').read_text()
m = re.search(r'^DB_PASSWORD=(.*)$', t, re.M)
print(m.group(1).strip() if m else '')
PY
)
# Always set/reset password to match .env (policy-safe)
mysql --defaults-file=/etc/mysql/debian.cnf <<SQL
CREATE DATABASE IF NOT EXISTS \`$DB_NAME\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
DROP USER IF EXISTS '$DB_USER'@'localhost';
DROP USER IF EXISTS '$DB_USER'@'127.0.0.1';
CREATE USER '$DB_USER'@'localhost' IDENTIFIED BY '$DB_PASS_EFFECTIVE';
CREATE USER '$DB_USER'@'127.0.0.1' IDENTIFIED BY '$DB_PASS_EFFECTIVE';
GRANT ALL PRIVILEGES ON \`$DB_NAME\`.* TO '$DB_USER'@'localhost';
GRANT ALL PRIVILEGES ON \`$DB_NAME\`.* TO '$DB_USER'@'127.0.0.1';
FLUSH PRIVILEGES;
SQL

composer install --no-dev --prefer-dist --no-interaction --optimize-autoloader

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
php artisan config:cache || true
php artisan route:cache || true
chown -R www-data:www-data storage bootstrap/cache
chmod -R ug+rwx storage bootstrap/cache

# Apache vhosts → Laravel public/
for conf in /etc/apache2/sites-available/mateen.academy.conf /etc/apache2/sites-available/mateen.academy-le-ssl.conf \
            /etc/apache2/sites-enabled/mateen.academy.conf /etc/apache2/sites-enabled/mateen.academy-le-ssl.conf; do
  if [ -f "$conf" ]; then
    sed -i 's|DocumentRoot /var/www/mateen.academy$|DocumentRoot /var/www/mateen/public|g' "$conf"
    sed -i 's|DocumentRoot /var/www/mateen.academy/public|DocumentRoot /var/www/mateen/public|g' "$conf"
    sed -i 's|<Directory /var/www/mateen.academy>|<Directory /var/www/mateen/public>|g' "$conf"
    sed -i 's|<Directory /var/www/mateen.academy/public>|<Directory /var/www/mateen/public>|g' "$conf"
  fi
done

# Ensure AllowOverride + Laravel rewrite for public/
python3 <<'PY'
from pathlib import Path
import re
for name in [
  '/etc/apache2/sites-available/mateen.academy.conf',
  '/etc/apache2/sites-available/mateen.academy-le-ssl.conf',
]:
  p = Path(name)
  if not p.exists():
    continue
  text = p.read_text()
  text = text.replace('DocumentRoot /var/www/mateen.academy\n', 'DocumentRoot /var/www/mateen/public\n')
  if '<Directory /var/www/mateen/public>' not in text:
    text = re.sub(
      r'(DocumentRoot /var/www/mateen/public\n)',
      r'''\1
    <Directory /var/www/mateen/public>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
''',
      text,
      count=1,
    )
  p.write_text(text)
  print('updated', name)
PY

a2enmod rewrite php8.4 headers || true
apache2ctl configtest
systemctl restart apache2

echo HEALTH=$(curl -s -o /dev/null -w '%{http_code}' -H 'Host: mateen.academy' http://127.0.0.1/up || echo fail)
echo UI=$(curl -s -o /dev/null -w '%{http_code}' -H 'Host: mateen.academy' http://127.0.0.1/Mateen/html/login.html || echo fail)
echo API=$(curl -s -o /dev/null -w '%{http_code}' -X POST -H 'Host: mateen.academy' http://127.0.0.1/api/v1/auth/login -H 'Content-Type: application/json' -H 'Accept: application/json' -d '{"email":"admin@mateen.test","password":"password"}' || echo fail)
echo HTTPS=$(curl -s -o /dev/null -w '%{http_code}' https://mateen.academy/Mateen/html/login.html || echo fail)
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
        )
        with sftp.file("/tmp/mateen-prod-setup.sh", "w") as f:
            f.write(script)
        sftp.close()
        print("Uploaded archive + setup script")
        run(client, "bash /tmp/mateen-prod-setup.sh")
        print("\nProduction deploy finished.")
        print(f"Site:  {PUBLIC_URL}/Mateen/html/home.html")
        print(f"Login: {PUBLIC_URL}/Mateen/html/login.html")
        print(f"API:   {PUBLIC_URL}/api/v1")
        print("Seed password: password (admin@mateen.test and other *@mateen.test)")
    finally:
        client.close()
        try:
            archive.unlink(missing_ok=True)
        except OSError:
            pass


if __name__ == "__main__":
    main()
