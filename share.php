<?php
// ============================================================
// LAB BAC #3 - T7: halaman share (Tugas Kuliah) — kikikokok
// BUG (share-link IDOR / sequential enumeration):
//   token share = id baris tabel `share` yang BERURUTAN (1,2,3,...).
//   Tidak ada login. Attacker tinggal coba t=1..10 dan menemukan
//   laporan rahasia (mis. audit internal) yang ikut ter-share.
// ============================================================
require_once __DIR__ . '/includes/koneksi.php';

$judul = 'LAB BAC #3 · Dokumen Berbagi';
$t = (int)($_GET['t'] ?? 0);

require_once __DIR__ . '/includes/header.php';
?>
<div class="card" style="max-width:600px">
  <h2>🔗 Dokumen Berbagi (share)</h2>
  <p class="muted">Contoh: <code>share.php?t=1</code> … <code>share.php?t=7</code>.
  Token berupa angka berurutan — tidak ada sistem login di sini.</p>
  <?php if ($t <= 0): ?>
    <div class="notice">Ketik <code>share.php?t=4</code> di URL untuk mencoba.</div>
  <?php else:
    $st = $pdo->prepare('SELECT s.id AS token, l.id AS laporan_id, l.judul, l.jumlah,
                                l.isi, l.cabang_id, u.nama AS pembuat
                           FROM share s
                           JOIN laporan l ON l.id=s.laporan_id
                           JOIN users u  ON u.id=s.pembuat_id
                          WHERE s.id=?');
    $st->execute([$t]);
    $dok = $st->fetch();

    if (!$dok): ?>
      <div class="danger">Tidak ada dokumen dengan token t=<?= (int)$t ?>.</div>
    <?php else: ?>
      <h3><?= e($dok['judul']) ?></h3>
      <p><b>Jumlah:</b> Rp<?= number_format((float)$dok['jumlah'], 2, ',', '.') ?></p>
      <p><b>Dibagikan oleh:</b> <?= e($dok['pembuat']) ?> · cabang <?= (int)$dok['cabang_id'] ?></p>
      <div class="notice"><b>Isi / catatan internal:</b><br>
      <?= nl2br(e($dok['isi'])) ?></div>
      <p class="muted">Token t=6 menampilkan <b>audit internal</b> yang
      seharusnya cuma untuk tim tertentu. Enumerasi t=1..10 akan menemukannya.</p>
    <?php endif;
  endif; ?>
  <pre class="muted">for i in $(seq 1 10); do
  curl -s "http://127.0.0.1:8094/share.php?t=$i" | grep -oE "<h3>[^<]+"
done</pre>
  <p class="danger"><b>Mitigasi:</b> pakai token acak (unpredictable, mis. 128-bit),
  wajib login, dan cek hak akses pembaca terhadap laporan tsb.</p>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>