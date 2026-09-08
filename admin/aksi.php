<?php
// ============================================================
// LAB BAC #3 - T8: aksi admin (Tugas Kuliah) — kikikokok
// BUG (hardcoded credential / function-level access control):
//   "otentikasi" = mencocokkan API key statis. Key itu NYATA
//   tercetak di assets/app.js (bundel frontend) sehingga siapa pun
//   yang membuka DevTools bisa memakainya untuk hapus laporan.
//
//   Endpoint ini juga TIDAK di-link dari menu (security by obscurity).
// ============================================================
require_once __DIR__ . '/../includes/koneksi.php';

define('ADMIN_API_KEY', 'bx7-9f3-KIKIKOKOK'); // sama dgn assets/app.js

$key = bearer();

if ($key !== ADMIN_API_KEY) {
    http_response_code(401);
    header('Content-Type: text/plain; charset=utf-8');
    echo "401 - butuh Authorization: Bearer <API_KEY>\n";
    exit;
}

// --- ADMIN ---
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $st = $pdo->query('SELECT l.id, l.judul, l.jumlah, l.cabang_id
                         FROM laporan l ORDER BY l.id');
    echo "LAPORAN (admin):\n";
    foreach ($st->fetchAll() as $r) {
        printf("#%d  %-40s Rp%s  cabang %d\n",
            (int)$r['id'], $r['judul'],
            number_format((float)$r['jumlah'], 0, ',', '.'), (int)$r['cabang_id']);
    }
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ids = $_POST['ids'] ?? [];
    if (!is_array($ids)) {
        $ids = [$ids];
    }
    $del = 0;
    $sp  = $pdo->prepare('DELETE FROM share WHERE laporan_id=?');
    $st  = $pdo->prepare('DELETE FROM laporan WHERE id=?');
    foreach ($ids as $id) {
        $id = (int)$id;
        if ($id <= 0) continue;
        $sp->execute([$id]);
        $st->execute([$id]);
        if ($st->rowCount() > 0) $del++;
    }
    http_response_code(200);
    header('Content-Type: text/plain; charset=utf-8');
    echo "OK: $del laporan dihapus. (admin/aksi.php)\n";
    exit;
}

http_response_code(405);
echo "405\n";