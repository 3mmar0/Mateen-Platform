#!/usr/bin/env python3
import os
import paramiko

HOST = os.environ.get("STAGING_SSH_HOST", "187.127.71.130")
PASSWORD = os.environ["STAGING_SSH_PASSWORD"]

CMD = r"""
set -e
echo '=== sites-enabled ==='
ls -la /etc/nginx/sites-enabled/
echo '=== mateen conf ==='
cat /etc/nginx/sites-available/mateen
echo '=== curl root ==='
curl -sI http://127.0.0.1/ | head -20
echo '=== curl index.php ==='
curl -sI http://127.0.0.1/index.php | head -15
echo '=== curl up ==='
curl -sI http://127.0.0.1/up | head -15
echo '=== login via index.php ==='
curl -s -w '\nHTTP %{http_code}\n' -X POST http://127.0.0.1/index.php/api/v1/auth/login \
  -H 'Content-Type: application/json' -H 'Accept: application/json' \
  -d '{"email":"admin@mateen.test","password":"password"}' | tail -c 500
echo
echo '=== login via /api ==='
curl -s -w '\nHTTP %{http_code}\n' -X POST http://127.0.0.1/api/v1/auth/login \
  -H 'Content-Type: application/json' -H 'Accept: application/json' \
  -d '{"email":"admin@mateen.test","password":"password"}' | tail -c 500
echo
echo '=== php socks ==='
ls /run/php/ || true
echo '=== routes ==='
cd /var/www/mateen/backend
php artisan route:list --path=api/v1/auth 2>&1 | head -40
php artisan route:list --path=up 2>&1 | head -20
echo '=== public ==='
ls -la /var/www/mateen/backend/public | head
"""

c = paramiko.SSHClient()
c.set_missing_host_key_policy(paramiko.AutoAddPolicy())
c.connect(HOST, username="root", password=PASSWORD, timeout=30)
stdin, stdout, stderr = c.exec_command(CMD, timeout=120)
print(stdout.read().decode(errors="replace"))
err = stderr.read().decode(errors="replace")
if err.strip():
    print("STDERR:", err)
c.close()
