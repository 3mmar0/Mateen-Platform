#!/usr/bin/env python3
"""Apply production .env keys for mateen.academy (CI-safe, no heredoc)."""
from __future__ import annotations

import re
import sys
from pathlib import Path

ROOT = Path(sys.argv[1] if len(sys.argv) > 1 else ".").resolve()
ENV = ROOT / ".env"
if not ENV.exists():
    raise SystemExit(f"missing {ENV}")

text = ENV.read_text(encoding="utf-8", errors="replace")
repl = {
    "APP_ENV": "production",
    "APP_DEBUG": "false",
    "APP_URL": "https://mateen.academy",
    "FRONTEND_URL": "https://mateen.academy",
    "SESSION_SECURE_COOKIE": "true",
    "CORS_ALLOWED_ORIGINS": "https://mateen.academy,https://www.mateen.academy",
}
for key, value in repl.items():
    if re.search(rf"^{key}=", text, flags=re.M):
        text = re.sub(rf"^{key}=.*$", f"{key}={value}", text, flags=re.M)
    else:
        text += f"\n{key}={value}\n"
ENV.write_text(text, encoding="utf-8")
print("production env keys applied")
