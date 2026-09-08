<?php
// ============================================================
// LAB BAC #3 - Beranda (Tugas Kuliah) — kikikokok
// ============================================================
require_once __DIR__ . '/includes/koneksi.php';
$judul = 'LAB BAC #3 · Beranda';
require_once __DIR__ . '/includes/header.php';
?>
<div class="card">
  <h2>🧩 Latihan Praktikum — Broken Access Control (Bagian 3)</h2>
  <p>Lab #1 &amp; #2 fokus ke IDOR & pola otorisasi klasik. Lab #3
  <b>gaya bug bounty modern</b>: aplikasi bertema API &amp; multi-cabang (SaaS)
  dengan pola temuan yang sering muncul di program bug bounty:</p>

  <table class="tbl">
    <thead><tr><th>#</th><th>Endpoint</th><th>Pola / Kategori BBP</th></tr></thead>
    <tbody>
      <tr><td>1</td><td><code>api/auth/token.php</code> → <code>api/auth/me.php</code></td>
          <td>JWT forgery (alg <code>none</code>) / secret lemah</td></tr>
      <tr><td>2</td><td><code>api/gql.php</code></td>
          <td>GraphQL over-fetch &amp; alias batching (kebocoran kolom rahasia)</td></tr>
      <tr><td>3</td><td><a href="rekap/list.php?org=2">rekap/list.php?org=2</a></td>
          <td>BOLA — pindah tenant/cabang cuma ganti id (multi-tenant)</td></tr>
      <tr><td>4</td><td><code>download.php?f=…</code></td>
          <td>Path traversal + akses file pribadi pengguna lain (IDOR read)</td></tr>
      <tr><td>5</td><td><code>api/akun/update.php</code></td>
          <td>IDOR change-password → account takeover (horizontal)</td></tr>
      <tr><td>6</td><td><code>api/lupa/req.php</code> + <code>api/lupa/terapkan.php</code></td>
          <td>Reset code tidak terikat akun (token binding broken)</td></tr>
      <tr><td>7</td><td><a href="share.php?t=4">share.php?t=…</a></td>
          <td>Share-link token berurutan → enumerasi akses "unlisted"</td></tr>
      <tr><td>8</td><td><code>admin/aksi.php</code> (key ada di <code>assets/app.js</code>)</td>
          <td>Hardcoded API key di JS → akses fungsi admin</td></tr>
    </tbody>
  </table>

  <div class="notice" style="margin-top:12px">
    Akun demo: <code>admin/admin123</code> (pusat) · <code>budi/budi123</code> (pusat) ·
    <code>riko/riko123</code> (cabang Bandung).<br>
    Lihat alur &amp; mitigasi di <code>LAPORAN.md</code> · server: port <b>8094</b>.
  </div>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>