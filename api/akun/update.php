<?php
// ============================================================
// LAB BAC #3 - T5: ganti password via API (Tugas Kuliah) — kikikokok
// BUG (IDOR write horizontal -> account takeover):
//   butuh token valid APA SAJA (budi) lalu user_id milik KORBAN
//   (1=admin) ikut diisi dari body — tidak ada cek bahwa user_id
//   == uid yang ada di token. Password korban langsung ditimpa.
// ============================================================
require_once __DIR__ . '/../../includes/koneksi.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    j(['error' => 'POST {"user_id":1,"password":"..."} (dengan header Authorization Bearer)'], 405);
}

$token   = bearer();
$payload = jwt_decode_flawed($token);

if (!$payload) {
    j(['error' => 'token tidak valid (dapatkan dari api/auth/token.php)'], 401);
}

$in      = baca_json();
$userId  = (int)($in['user_id'] ?? 0);
$passBaru = (string)($in['password'] ?? '');

if ($userId <= 0 || $passBaru === '') {
    j(['error' => 'user_id dan password wajib diisi'], 400);
}

// BUG di sini: BUKAN $payload['uid'] yang dijadikan target,
// tapi $userId bebas dari body.
$up = $pdo->prepare('UPDATE users SET password=? WHERE id=?');
$up->execute([$passBaru, $userId]);

j([
    'success' => true,
    'pesan'   => "Password user id=$userId DIGANTI dengan '$passBaru'",
    'action'  => 'ganti password milik siapa pun (IDOR write)',
]);