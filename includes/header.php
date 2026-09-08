<?php
// ============================================================
// LAB BAC #3 - Header bersama (Tugas Kuliah) — kikikokok
// ============================================================
require_once __DIR__ . '/koneksi.php';
$judul = $judul ?? 'LAB BAC #3 - Portal Mitra';
$base  = $base ?? '';
?>
<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($judul) ?></title>
<link rel="stylesheet" href="<?= $base ?>assets/style.css">
</head>
<body>
<div class="topbar">
  <div class="wrap">
    <div class="logo">🧩 <b>LAB BAC&nbsp;#3</b> <span class="muted">— Portal Mitra (API &amp; multi-cabang)</span></div>
    <nav>
      <a href="<?= $base ?>index.php">Beranda</a>
      <a href="<?= $base ?>rekap/list.php">Rekap Cabang</a>
      <a href="<?= $base ?>api/auth/token.php">Login API</a>
    </nav>
  </div>
</div>
<div class="wrap">