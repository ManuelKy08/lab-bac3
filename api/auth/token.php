<?php
// ============================================================
// LAB BAC #3 - T1: Login -> terbitkan JWT (Tugas Kuliah) — kikikokok
// ============================================================
require_once __DIR__ . '/../../includes/koneksi.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $judul = 'LAB BAC #3 · Login API';
    require_once __DIR__ . '/../../includes/header.php';
    ?>
    <div class="card" style="max-width:480px">
      <h2>🔑 Login API (terbitkan JWT)</h2>
      <pre class="muted">curl -s -X POST http://127.0.0.1:8094/api/auth/token.php \
  -H "Content-Type: application/json" \
  -d '{"username":"budi","password":"budi123"}'</pre>
      <form method="post">
        <label>Body JSON</label>
        <textarea name="body" rows="3">{"username":"budi","password":"budi123"}</textarea>
        <button class="btn">Kirim</button>
      </form>
    </div>
    <?php require __DIR__ . '/../../includes/footer.php';
    exit;
}

$in   = baca_json();
$user = (string)($in['username'] ?? '');
$pass = (string)($in['password'] ?? '');

$st = $pdo->prepare('SELECT * FROM users WHERE username=?');
$st->execute([$user]);
$u = $st->fetch();

if (!$u || $u['password'] !== $pass) {
    j(['error' => 'kredensial salah'], 401);
}

$token = jwt_encode([
    'uid'      => (int)$u['id'],
    'username' => $u['username'],
    'role'     => $u['role'],
    'cabang_id'=> (int)$u['cabang_id'],
    'exp'      => time() + 3600,
]);

j(['token' => $token, 'user' => $u['username'], 'role' => $u['role']]);