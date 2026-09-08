<?php
// ============================================================
// LAB BAC #3 - T6a: minta kode reset (Tugas Kuliah) — kikikokok
// Alur lupa password. (Bug-nya ada di terapkan.php, bukan di sini.)
// ============================================================
require_once __DIR__ . '/../../includes/koneksi.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    j(['error' => 'POST {"username":"budi"}'], 405);
}

$in  = baca_json();
$username = (string)($in['username'] ?? '');

$st = $pdo->prepare('SELECT id FROM users WHERE username=?');
$st->execute([$username]);
$u = $st->fetch();

if (!$u) {
    j(['error' => 'username tidak ditemukan'], 404);
}

$kode = str_pad((string)random_int(0, 9999), 4, '0', STR_PAD_LEFT);

$ins = $pdo->prepare('INSERT INTO reset_kode (user_id, kode) VALUES (?,?)');
$ins->execute([(int)$u['id'], $kode]);

// (dalam produksi kode dikirim lewat email — di lab sengaja ditampilkan
//  supaya alur eksploitasinya mudah dicoba)
j(['status' => 'kode terkirim', 'user' => $username, 'kode' => $kode]);