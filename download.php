<?php
// ============================================================
// LAB BAC #3 - T4: Unduh dokumen (Tugas Kuliah) — kikikokok
// BUG:
//   1) parameter `f` dipakai LANGSUNG ke path (tidak ada basename/path-whitelist)
//      -> path traversal: ../ keluar dari folder private.
//   2) tidak ada autentikasi / cek kepemilikan -> file pribadi
//      pengguna lain (CV, dokumen internal) bisa diunduh semua orang.
// ============================================================
require_once __DIR__ . '/includes/koneksi.php';

$baseUp = __DIR__ . '/files/private/';
$f      = (string)($_GET['f'] ?? '');

if ($f === '') {
    $judul = 'LAB BAC #3 · Unduh Dokumen';
    require_once __DIR__ . '/includes/header.php';
    ?>
    <div class="card" style="max-width:560px">
      <h2>📄 Unduh Dokumen</h2>
      <pre class="muted">curl "http://127.0.0.1:8094/download.php?f=CV-budi.txt"
# coba juga: ?f=CV-riko.txt   (milik orang lain)
#            ?f=../../database/lab_bac3.sql   (escap dari folder private)
</pre>
      <p>Isi <code>files/private/</code>: CV-budi.txt, CV-sari.txt, CV-riko.txt,
      CV-dewi.txt, RATING-internal.txt</p>
      <p class="danger" style="margin-top:10px"><b>Mitigasi:</b>
      simpan file di luar docroot, layani lewat handler yang mapping id →
      path aman, lalu terapkan cek hak akses terhadap pemilik file.</p>
    </div>
    <?php require __DIR__ . '/includes/footer.php';
    exit;
}

$path = $baseUp . $f;

if (!is_file($path)) {
    http_response_code(404);
    header('Content-Type: text/plain; charset=utf-8');
    echo "tidak ditemukan: files/private/$f\n";
    exit;
}

header('Content-Type: text/plain; charset=utf-8');
header('Content-Disposition: attachment; filename="' . basename($path) . '"');
readfile($path);