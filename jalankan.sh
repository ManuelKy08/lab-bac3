#!/usr/bin/env bash
# ============================================================
# LAB BAC #3 - Peluncur (Tugas Kuliah) — kikikokok
#   ./jalankan.sh          # import DB + nyalakan server :8094
#   ./jalankan.sh stop     # matikan
# ============================================================
set -e
DIR="$(cd "$(dirname "$0")" && pwd)"
PORT=8094

if [ "$1" = "stop" ]; then
  fuser -k "${PORT}/tcp" 2>/dev/null || true
  pkill -f "php -S 127.0.0.1:${PORT}" 2>/dev/null || true
  echo "[lab-bac3] server mati."
  exit 0
fi

echo "[lab-bac3] import database lab_bac3 ..."
mariadb -uroot < "$DIR/database/lab_bac3.sql"

echo "[lab-bac3] jalankan http://127.0.0.1:$PORT ..."
nohup setsid php -S "127.0.0.1:$PORT" -t "$DIR" > "$DIR/php-server.log" 2>&1 < /dev/null &
sleep 2
curl -s -o /dev/null -w "[lab-bac3] cek server: HTTP %{http_code}\n" "http://127.0.0.1:$PORT/index.php"
echo "[lab-bac3] selesai. Buka http://127.0.0.1:$PORT"