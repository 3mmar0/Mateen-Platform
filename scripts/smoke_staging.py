#!/usr/bin/env python3
import json
import os
import urllib.request

import paramiko

HOST = "187.127.71.130"
PASSWORD = os.environ["STAGING_SSH_PASSWORD"]


def http_login():
    req = urllib.request.Request(
        f"http://{HOST}/api/v1/auth/login",
        data=json.dumps({"email": "admin@mateen.test", "password": "password"}).encode(),
        headers={"Content-Type": "application/json", "Accept": "application/json"},
        method="POST",
    )
    with urllib.request.urlopen(req, timeout=30) as resp:
        body = json.loads(resp.read().decode())
        print("EXTERNAL_LOGIN", resp.status, body.get("user", {}).get("email"), body.get("token_type"))
        return body["token"]


def http_me(token: str):
    req = urllib.request.Request(
        f"http://{HOST}/api/v1/auth/me",
        headers={"Authorization": f"Bearer {token}", "Accept": "application/json"},
    )
    with urllib.request.urlopen(req, timeout=30) as resp:
        body = json.loads(resp.read().decode())
        print("EXTERNAL_ME", resp.status, body.get("role") or body.get("data", {}).get("role") or body)


def fix_up_and_static():
    c = paramiko.SSHClient()
    c.set_missing_host_key_policy(paramiko.AutoAddPolicy())
    c.connect(HOST, username="root", password=PASSWORD, timeout=30)
    cmd = r"""
cd /var/www/mateen
tail -n 40 storage/logs/laravel.log 2>/dev/null || echo 'no log'
echo '---'
# ensure APP_KEY present and storage writable
php artisan about 2>&1 | head -30
chown -R www-data:www-data storage bootstrap/cache
chmod -R ug+rwx storage bootstrap/cache
# quick static check
ls /var/www/mateen/html/login.html && echo STATIC_OK
curl -s -o /dev/null -w 'STATIC_HTTP %{http_code}\n' http://127.0.0.1/Mateen/html/login.html
curl -s -o /dev/null -w 'UP_HTTP %{http_code}\n' http://127.0.0.1/up
"""
    stdin, stdout, stderr = c.exec_command(cmd, timeout=60)
    print(stdout.read().decode(errors="replace"))
    err = stderr.read().decode(errors="replace")
    if err.strip():
        print("STDERR:", err)
    c.close()


if __name__ == "__main__":
    token = http_login()
    http_me(token)
    fix_up_and_static()
