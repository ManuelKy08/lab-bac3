-- ============================================================
-- LAB BAC #3 - Database (Tugas Kuliah) — kikikokok
-- Tema: Portal Mitra multi-cabang (SaaS style, API-first)
-- ============================================================
DROP DATABASE IF EXISTS lab_bac3;
CREATE DATABASE lab_bac3 CHARACTER SET utf8mb4;
USE lab_bac3;

-- Tenant = cabang.
CREATE TABLE cabang (
  id   INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nama VARCHAR(60)  NOT NULL,
  kota VARCHAR(60)  NOT NULL
);

INSERT INTO cabang (id, nama, kota) VALUES
  (1, 'Pusat',   'Jakarta'),
  (2, 'Cabang',  'Bandung');

-- Pengguna punya cabang_id (langsung menempel di akun).
CREATE TABLE users (
  id       INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(30) NOT NULL UNIQUE,
  password VARCHAR(64) NOT NULL,
  nama     VARCHAR(80) NOT NULL,
  role     ENUM('admin','kepala','staf') NOT NULL DEFAULT 'staf',
  cabang_id INT UNSIGNED NOT NULL,
  FOREIGN KEY (cabang_id) REFERENCES cabang(id)
);

INSERT INTO users (id, username, password, nama, role, cabang_id) VALUES
  (1, 'admin', 'admin123', 'Rizky Admin',   'admin',  1),
  (2, 'budi',  'budi123',  'Budi Santoso',  'kepala', 1),
  (3, 'sari',  'sari123',  'Sari Staf',     'staf',   1),
  (4, 'riko',  'riko123',  'Riko Pratama',  'kepala', 2),
  (5, 'dewi',  'dewi123',  'Dewi Lestari',  'staf',   2);

-- Laporan keuangan per cabang. kolom `isi` = catatan internal yang rahasia.
CREATE TABLE laporan (
  id        INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  judul     VARCHAR(120) NOT NULL,
  jumlah    DECIMAL(14,2) NOT NULL,
  isi       TEXT NOT NULL,
  cabang_id INT UNSIGNED NOT NULL,
  penulis_id INT UNSIGNED NOT NULL,
  FOREIGN KEY (cabang_id)  REFERENCES cabang(id),
  FOREIGN KEY (penulis_id) REFERENCES users(id)
);

INSERT INTO laporan (id, judul, jumlah, isi, cabang_id, penulis_id) VALUES
  (1, 'Penjualan POS Elektronik Januari', 25000000, 'Margin rata-rata 22%. Strategi promosi Q1 disetujui pimpinan; target sales dikerek 15%.', 1, 2),
  (2, 'Stok FMCG - Retur Kadaluarsa',      9100000, 'Banyak SKU mendekati kedaluarsa. Minta retur ke distributor sebelum Maret.', 1, 2),
  (3, 'Audit Internal - Selisih Kas',      5000000, 'AUDIT RAHASIA: ditemukan selisih kas Rp5jt di kasir cabang pusat. Ditangani tim internal, jangan dipublikasikan.', 1, 3),
  (4, 'Penjualan Bandung Q1',             18200000, 'Kinerja terbaik se-cabang. Bonus staf bagian penjualan diajukan.', 2, 4),
  (5, 'Retur Barang Rusak Bandung',        3200000, 'Barang rusak dalam pengiriman. Klaim asuransi diproses.', 2, 5);

-- Kode reset lupa password (T6).
CREATE TABLE reset_kode (
  id      INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NOT NULL,
  kode    CHAR(4) NOT NULL,
  FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Link berbagi (T7). token = id baris (berurutan & bisa ditebak).
CREATE TABLE share (
  id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  laporan_id INT UNSIGNED NOT NULL,
  pembuat_id INT UNSIGNED NOT NULL,
  FOREIGN KEY (laporan_id) REFERENCES laporan(id),
  FOREIGN KEY (pembuat_id) REFERENCES users(id)
);

INSERT INTO share (id, laporan_id, pembuat_id) VALUES
  (1, 1, 2),
  (2, 2, 2),
  (3, 3, 3),
  (4, 4, 4),
  (5, 5, 5),
  (6, 3, 3),  -- audit internal ikut ter-share! (jangan)
  (7, 1, 2);