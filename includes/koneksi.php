<?php
// ============================================================
// LAB BAC #3 - Koneksi Database & Util API (Tugas Kuliah)
// Nama: kikikokok
// ============================================================

declare(strict_types=1);

$dsn = 'mysql:host=127.0.0.1;dbname=lab_bac3;charset=utf8mb4';
$pdo = new PDO($dsn, 'root', '', [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
]);

function e(mixed $v): string
{
    return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
}

function j(mixed $data, int $kode = 200): never
{
    http_response_code($kode);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function baca_json(): array
{
    $raw = file_get_contents('php://input') ?: '';
    $d   = json_decode($raw, true);
    return is_array($d) ? $d : [];
}

function bearer(): string
{
    $h = $_SERVER['HTTP_AUTHORIZATION']
        ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? '';
    if ($h === '') {
        $hdr = function_exists('getallheaders') ? getallheaders() : [];
        $h   = $hdr['Authorization'] ?? $hdr['authorization'] ?? '';
    }
    return (string)preg_replace('/^Bearer\s+/i', '', trim($h));
}

// ------------------------------------------------------------
// JWT — helper (T1). JANGAN dipakai prod; ini untuk praktikum.
// ------------------------------------------------------------
define('JWT_SECRET', 'KIKI_TOKEN_2026'); // secret statis & lemah (bisa dicrack)

function jwt_b64url(string $s): string
{
    return rtrim(strtr(base64_encode($s), '+/', '-_'), '=');
}

function jwt_b64url_dec(string $s): string
{
    $s .= str_repeat('=', (4 - strlen($s) % 4) % 4);
    return (string)base64_decode(strtr($s, '-_', '+/'));
}

function jwt_encode(array $payload, string $alg = 'HS256'): string
{
    $hdr = jwt_b64url(json_encode(['alg' => $alg, 'typ' => 'JWT']));
    $pl  = jwt_b64url(json_encode($payload));
    $seg = $hdr . '.' . $pl;
    $sig = hash_hmac('sha256', $seg, JWT_SECRET, true);
    return $seg . '.' . jwt_b64url($sig);
}

/**
 * BUG (dibuat sengaja untuk latihan T1):
 *  - kalau header `alg` = "none", tanda tangan TIDAK diverifikasi
 *    alias attacker bisa membuat token sendiri (token forgery).
 *  - secret statis, lemah, dan tidak ada pemeriksaan `exp`.
 */
function jwt_decode_flawed(string $token): ?array
{
    $parts = explode('.', $token);
    if (count($parts) !== 3) {
        return null;
    }
    $hdr = json_decode(jwt_b64url_dec($parts[0]), true);
    if (!is_array($hdr) || !isset($hdr['alg'])) {
        return null;
    }
    // --- BUG UTAMA #1: algoritma "none" dipercaya begitu saja ---
    if (strtolower((string)$hdr['alg']) === 'none') {
        $p = json_decode(jwt_b64url_dec($parts[1]), true);
        return is_array($p) ? $p : null;
    }
    // --- jalur HS256: verifikasi dgn secret statis ---
    $sig = hash_hmac('sha256', $parts[0] . '.' . $parts[1], JWT_SECRET, true);
    if (!hash_equals(jwt_b64url_dec($parts[2]), $sig)) {
        return null;
    }
    $p = json_decode(jwt_b64url_dec($parts[1]), true);
    return is_array($p) ? $p : null; // BUG #2: `exp` tidak dicek
}