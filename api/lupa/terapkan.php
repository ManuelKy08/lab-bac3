<?php
// ============================================================
// LAB BAC #3 - T6b: terapkan kode reset (Tugas Kuliah) — kikikokok
// BUG (broken token binding):
//   kode reset VALID berdasarkan keberadaan di tabel, tapi TIDAK
//   dicek kode itu milik username yang dikirim. Query UPDATE memakai
//   `username` dari body (korban), padahal baris reset_kode yang
//   ditemukan punya user_id lain (pemanggil). => pakai kode milik
//   sendiri untuk reset password AKUN ORANG LAIN (takeover).
// ============================================================
require_once __DIR__ . '/../../includes/koneksi.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    j(['error' => 'POST {"username":"admin","kode":"1234","password":"..."}'], 405);
}

$in       = baca_json();
$username = (string)($in['username'] ?? '');
$kode     = (string)($in['kode'] ?? '');
$password = (string)($in['password'] ?? '');

if ($username === '' || $kode === '' || $password === '') {
    j(['error' => 'username, kode, password wajib diisi'], 400);
}

// cari baris reset_kode berdasarkan KODE saja
$st = $pdo->prepare('SELECT rk.user_id, u.nama AS pemilik_kode
                       FROM reset_kode rk JOIN users u ON u.id=rk.user_id
                      WHERE rk.kode=? ORDER BY rk.id DESC LIMIT 1');
$st->execute([$kode]);
$row = $st->fetch();

if (!$row) {
    j(['error' => 'kode tidak ditemukan'], 400);
}

// BUG: baris yang cocok utk user_id = $row['user_id'], tapi password
// AKUN $username (body) yang direset — ikatan antara kode & akun hilang.
$up = $pdo->prepare('UPDATE users SET password=? WHERE username=?');
$up->execute([$password, $username]);

j([
    'success' => true,
    'pesan'   => "Password '$username' direset (kode itu aslinya milik: {$row['pemilik_kode']})",
    'demo'    => 'kode milik akun A dipakai untuk reset akun B',
]);