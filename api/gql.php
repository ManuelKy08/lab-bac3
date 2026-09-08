<?php
// ============================================================
// LAB BAC #3 - T2: endpoint "GraphQL"-lite (Tugas Kuliah) — kikikokok
// BUG:
//   1) TIDAK ada autentikasi/otorisasi sama sekali.
//   2) over-fetch: seluruh kolom baris dikembalikan (termasuk
//      catatan rahasia `isi` / `password`), apa pun yang diminta.
//   3) alias ("a:", "b:", ...) membolehkan ambil objek massal
//      dalam satu request — persis trik exploit di GraphQL.
// ============================================================
require_once __DIR__ . '/../includes/koneksi.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    j(['error' => 'kirim POST dengan {"query":"{ a:laporan(id:1){judul} }"}'], 405);
}

$in = baca_json();
$q  = (string)($in['query'] ?? '');

$cocok = [];
$pattern = '/\b([A-Za-z_][A-Za-z0-9_]*)\s*:\s*([A-Za-z_][A-Za-z0-9_]*)\s*\(\s*id\s*:\s*(\d+)\s*\)/';
preg_match_all($pattern, $q, $m, PREG_SET_ORDER);
foreach ($m as $x) {
    $cocok[$x[1]] = [$x[2], (int)$x[3]];
}

if (!$cocok) {
    // coba tanpa alias: laporan(id:1){...}
    $simple = '/([A-Za-z_][A-Za-z0-9_]*)\s*\(\s*id\s*:\s*(\d+)\s*\)/';
    if (preg_match_all($simple, $q, $m2, PREG_SET_ORDER)) {
        foreach ($m2 as $x) {
            $cocok[$x[1]] = [$x[1], (int)$x[2]];
        }
    }
}

if (!$cocok) {
    j(['error' => 'query tidak dipahami. contoh: {"query":"{ a:laporan(id:1){judul} b:profil(id:1){nama} }"}'], 400);
}

$hasil = [];
foreach ($cocok as $alias => [$tipe, $id]) {
    if ($tipe === 'laporan') {
        $st = $pdo->prepare('SELECT l.*, u.nama AS penulis, c.nama AS cabang_nama
                               FROM laporan l
                               JOIN users u ON u.id=l.penulis_id
                               JOIN cabang c ON c.id=l.cabang_id
                              WHERE l.id=?');
        $st->execute([$id]);
        $row = $st->fetch();
        if ($row) $hasil[$alias] = $row;
    } elseif ($tipe === 'profil') {
        $st = $pdo->prepare('SELECT u.*, c.nama AS cabang_nama FROM users u JOIN cabang c ON c.id=u.cabang_id WHERE u.id=?');
        $st->execute([$id]);
        $row = $st->fetch();
        if ($row) $hasil[$alias] = $row;
    }
}

j(['query' => $q, 'data' => $hasil]);