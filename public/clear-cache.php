<?php
/**
 * clear-cache.php — Pembuka kunci route 404 di production (intimotor.com)
 * 
 * CARA PAKAI:
 * 1. Upload file ini ke public_html/ (sejajar index.php) di hosting
 * 2. Buka: https://intimotor.com/clear-cache.php?token=MNI-CLEAR-20260827-9f3a
 * 3. Jika sukses, hapus file ini dari hosting (penting!)
 * 
 * Keamanan: hanya bisa diakses dengan token yang benar.
 * Tidak perlu login, sengaja bypass route cache yang sedang rusak.
 */

// === GANTI TOKEN INI JIKA INGIN LEBIH RAHASIA ===
define('CLEAR_TOKEN', 'MNI-CLEAR-20260827-9f3a');

if (!isset($_GET['token']) || $_GET['token'] !== CLEAR_TOKEN) {
    http_response_code(403);
    die('<h3>403 Forbidden</h3><p>Token salah. Akses: ?token=' . htmlspecialchars(CLEAR_TOKEN) . '</p><p>Hapus file ini setelah selesai!</p>');
}

// Header agar tidak di-cache browser
header('Content-Type: text/html; charset=UTF-8');
header('Cache-Control: no-store, no-cache, must-revalidate');

echo "<h2>🔧 MNI Clear Cache</h2><pre style='background:#111;color:#0f0;padding:16px;border-radius:8px;overflow:auto'>";

$base = realpath(__DIR__ . '/..');
if (!$base) $base = __DIR__ . '/..';

echo "Base path: " . htmlspecialchars($base) . "\n";
echo "Token OK - mengeksekusi...\n\n";

// 1. Hapus file cache manual (paling ampuh saat route:cache rusak)
$files = [
    $base . '/bootstrap/cache/routes-v7.php',
    $base . '/bootstrap/cache/routes.php',
    $base . '/bootstrap/cache/config.php',
    $base . '/bootstrap/cache/packages.php',
    $base . '/bootstrap/cache/services.php',
];

foreach ($files as $f) {
    if (file_exists($f)) {
        if (@unlink($f)) {
            echo "✓ Dihapus: " . basename($f) . "\n";
        } else {
            echo "✗ Gagal hapus: " . basename($f) . " (cek permission)\n";
        }
    } else {
        echo "- Tidak ada: " . basename($f) . " (sudah bersih)\n";
    }
}

echo "\n";

// 2. Coba panggil artisan via bootstrap (jika file artisan masih ada)
try {
    $artisan = $base . '/artisan';
    if (file_exists($artisan)) {
        // Jalankan artisan command via PHP
        $commands = ['route:clear', 'config:clear', 'cache:clear', 'view:clear', 'optimize:clear'];
        // Bootstrap Laravel untuk Artisan::call
        require $base . '/vendor/autoload.php';
        $app = require $base . '/bootstrap/app.php';
        $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
        foreach ($commands as $cmd) {
            try {
                $kernel->call($cmd);
                $out = trim($kernel->output());
                echo "✓ artisan $cmd : OK" . ($out ? " - $out" : "") . "\n";
            } catch (Throwable $e) {
                echo "✗ artisan $cmd : " . $e->getMessage() . "\n";
            }
        }
    } else {
        echo "- artisan tidak ditemukan, skip Artisan::call (cukup hapus file manual di atas)\n";
    }
} catch (Throwable $e) {
    echo "⚠ Bootstrap artisan gagal: " . $e->getMessage() . "\n";
    echo "  Tidak masalah, hapus manual di atas sudah cukup.\n";
}

echo "\n";

// 3. OPcache reset jika aktif
if (function_exists('opcache_reset')) {
    if (@opcache_reset()) {
        echo "✓ OPcache direset\n";
    } else {
        echo "- OPcache reset gagal (mungkin tidak aktif)\n";
    }
} else {
    echo "- OPcache tidak aktif\n";
}

echo "\n✅ SELESAI! Sekarang coba buka:\n";
echo "   https://intimotor.com/import_payment\n";
echo "   (login sebagai andydch@koidigital.co.id)\n\n";
echo "⚠️  PENTING: Segera hapus file clear-cache.php dari hosting!\n";
echo "</pre>";
echo "<p><a href='/import_payment' style='background:#0d6efd;color:#fff;padding:10px 20px;border-radius:6px;text-decoration:none'>→ Coba buka /import_payment</a></p>";
echo "<p style='color:#888;font-size:12px'>Hapus file ini setelah dipakai. Token: " . htmlspecialchars(CLEAR_TOKEN) . "</p>";
