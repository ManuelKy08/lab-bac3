<?php
// ============================================================
// LAB BAC #3 - T3: Rekap per Cabang (Tugas Kuliah) — kikikokok
// BUG (BOLA / broken object level authorization - tenant):
//   aplikasi multi-cabang TIDAK mengikat sesi ke cabang.
//   siapa pun bisa isi ?org=N untuk lihat cabang lain/tenant lain.
//   Bukan cek "punya cabang berapa?", semuanya diambil dari query.
// ============================================================
require_once __DIR__ . '/../includes/koneksi.php';

$judul = 'LAB BAC #3 · Rekap Cabang';
require_once __DIR__ . '/../includes/header.php';

$org = (int)($_GET['org'] ?? 0);
if ($org < 1) {
    $org = 1;
}

$st = $pdo->prepare('SELECT id,nama,kota FROM cabang');
$st->execute();
$cabangs = $st->fetchAll();

$st = $pdo->prepare('SELECT l.*, u.nama AS penulis
                       FROM laporan l JOIN users u ON u.id=l.penulis_id
                      WHERE l.cabang_id=? ORDER BY l.id');
$st->execute([$org]);
$list = $st->fetchAll();
$total = 0;
foreach ($list as $r) { $total += (float)$r['jumlah']; }

$namaCabang = '';
foreach ($cabangs as $c) { if ((int)$c['id'] === $org) { $namaCabang = $c['nama'] . ' — ' . $c['kota']; } }
?>
<div class="card">
  <h2>📊 Rekap Keuangan — <?= e($namaCabang) ?></h2>
  <?php if ($org > 1): ?>
    <div class="danger">⚠️ Kamu melihat cabang <b><?= e((string)$org) ?></b> hanya dengan mengganti <code>?org=</code> — tidak ada cek kepemilikan tenant.</div>
  <?php else: ?>
    <div class="ok-box">Menampilkan cabang kamu (id=1). Coba ganti <code>?org=2</code> di URL…</div>
  <?php endif; ?>
  <table class="tbl">
    <thead><tr><th>ID</th><th>Judul</th><th>Jumlah</th><th>Penulis</th></tr></thead>
    <tbody>
      <?php foreach ($list as $r): ?>
      <tr>
        <td><?= (int)$r['id'] ?></td>
        <td><?= e($r['judul']) ?></td>
        <td>Rp<?= number_format((float)$r['jumlah'], 2, ',', '.') ?></td>
        <td><?= e($r['penulis']) ?></td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
  <p><b>Total:</b> Rp<?= number_format($total, 2, ',', '.') ?></p>

  <pre class="muted"># tanpa login, ganti cabang:
curl -s "http://127.0.0.1:8094/rekap/list.php?org=2" | grep -oE "Total:</b> Rp[^<]+"</pre>

  <p class="muted"><b>Mitigasi:</b> ambil cabang dari sesi (server-side session/claim),
  bukan dari parameter URL (<code>org</code>/<code>tenant</code>/<code>cabang</code>).</p>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>