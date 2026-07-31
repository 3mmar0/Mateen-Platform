#!/usr/bin/env python3
"""Finish staging MySQL + nginx after package upload."""
from __future__ import annotations

import os
import secrets
import sys

import paramiko

HOST = os.environ.get("STAGING_SSH_HOST", "187.127.71.130")
USER = os.environ.get("STAGING_SSH_USER", "root")
PASSWORD = os.environ.get("STAGING_SSH_PASSWORD", "")
DB_PASS = os.environ.get("STAGING_DB_PASS") or secrets.token_urlsafe(18)

if not PASSWORD:
    raise SystemExit("Set STAGING_SSH_PASSWORD")

SCRIPT = f"""
set -euo pipefail
DB_NAME=mateen
DB_USER=mateen
DB_PASS='{DB_PASS}'

mysql -e "CREATE DATABASE IF NOT EXISTS \\`$DB_NAME\\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
mysql -e "DROP USER IF EXISTS '$DB_USER'@'localhost';"
mysql -e "DROP USER IF EXISTS '$DB_USER'@'127.0.0.1';"
mysql -e "CREATE USER '$DB_USER'@'localhost' IDENTIFIED BY '$DB_PASS';"
mysql -e "CREATE USER '$DB_USER'@'127.0.0.1' IDENTIFIED BY '$DB_PASS';"
mysql -e "GRANT ALL PRIVILEGES ON \\`$DB_NAME\\`.* TO '$DB_USER'@'localhost';"
mysql -e "GRANT ALL PRIVILEGES ON \\`$DB_NAME\\`.* TO '$DB_USER'@'127.0.0.1'; FLUSH PRIVILEGES;"

cd /var/www/mateen
python3 <<'PY'
from pathlib import Path
import re
p = Path('.env')
t = p.read_text()
repl = {{
  'DB_CONNECTION': 'mysql',
  'DB_HOST': '127.0.0.1',
  'DB_PORT': '3306',
  'DB_DATABASE': 'mateen',
  'DB_USERNAME': 'mateen',
  'DB_PASSWORD': '{DB_PASS}',
  'APP_URL': 'http://187.127.71.130',
  'APP_ENV': 'staging',
  'APP_DEBUG': 'true',
  'FRONTEND_URL': 'https://mateen.academy',
  'CORS_ALLOWED_ORIGINS': 'https://mateen.academy,http://187.127.71.130,https://187.127.71.130',
  'SESSION_DRIVER': 'database',
  'QUEUE_CONNECTION': 'database',
  'CACHE_STORE': 'database',
}}
for key in ('DB_HOST', 'DB_PORT', 'DB_DATABASE', 'DB_USERNAME', 'DB_PASSWORD'):
    t = re.sub(rf'^#\\s*({{key}}=)', r'\\1', t, flags=re.M)
for k, v in repl.items():
    if re.search(rf'^{{k}}=', t, flags=re.M):
        t = re.sub(rf'^{{k}}=.*$', f'{{k}}={{v}}', t, flags=re.M)
    else:
        t += f'\\n{{k}}={{v}}\\n'
p.write_text(t)
print('env ok')
PY

php artisan key:generate --force
php artisan migrate --force
php artisan db:seed --force
php artisan storage:link || true
php artisan config:clear
chown -R www-data:www-data storage bootstrap/cache
chmod -R ug+rwx storage bootstrap/cache

cat >/etc/nginx/sites-available/mateen <<'NGINX'
server {{
    listen 80 default_server;
    listen [::]:80 default_server;
    server_name _;
    root /var/www/mateen/public;
    index index.php index.html;
    client_max_body_size 32M;

    location / {{
        try_files $uri $uri/ /index.php?$query_string;
    }}

    location ~ \\.php$ {{
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/run/php/php8.4-fpm.sock;
    }}

    location ~ /\\.(?!well-known).* {{
        deny all;
    }}
}}
NGINX

ln -sfn /etc/nginx/sites-available/mateen /etc/nginx/sites-enabled/mateen
rm -f /etc/nginx/sites-enabled/default
nginx -t
systemctl reload nginx
systemctl reload php8.4-fpm || true

echo HEALTH=$(curl -s -o /dev/null -w '%{{http_code}}' http://127.0.0.1/up || echo fail)
echo LOGIN=$(curl -s -o /dev/null -w '%{{http_code}}' -X POST http://127.0.0.1/api/v1/auth/login -H 'Content-Type: application/json' -H 'Accept: application/json' -d '{{"email":"admin@mateen.test","password":"password"}}' || echo fail)
"""

client = paramiko.SSHClient()
client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
client.connect(HOST, username=USER, password=PASSWORD, timeout=45)
try:
    sftp = client.open_sftp()
    with sftp.file("/tmp/mateen-finish.sh", "w") as f:
        f.write(SCRIPT)
    sftp.close()
    print("$ bash /tmp/mateen-finish.sh")
    _stdin, stdout, stderr = client.exec_command("bash /tmp/mateen-finish.sh", timeout=600)
    out = stdout.read().decode(errors="replace")
    err = stderr.read().decode(errors="replace")
    code = stdout.channel.recv_exit_status()
    print(out)
    if err.strip():
        print(err, file=sys.stderr)
    if code != 0:
        raise SystemExit(f"failed with {code}")
    print("Staging finish OK")
    print(f"API http://{HOST}/api/v1")
    print(f"UI  http://{HOST}/Mateen/html/login.html")
finally:
    client.close()
