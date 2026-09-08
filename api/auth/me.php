<?php
// ============================================================
// LAB BAC #3 - T1: endpoint "profil saya" (Tugas Kuliah) — kikikokok
// BUG: cek JWT memakai jwt_decode_flawed() yang:
//   - menerima token alg="none" tanpa verifikasi,
//   - secret statis, tidak cek exp.
// ============================================================
require_once __DIR__ . '/../../includes/koneksi.php';

$token   = bearer();
$payload = jwt_decode_flawed($token);

if (!$payload) {
    j(['error' => 'token tidak valid / kurang Authorization header'], 401);
}

$st = $pdo->prepare('SELECT id,username,nama,role,cabang_id,password FROM users WHERE id=?');
$st->execute([(int)$payload['uid']]);
$u = $st->fetch();

if (!$u) {
    j(['error' => 'user tidak ditemukan'], 404);
}

j(['data' => $u]);