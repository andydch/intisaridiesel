<?php

namespace App\Imports;

class FailureReport
{
    private const DIR = 'import-payment-reports';

    /**
     * Simpan daftar kegagalan; kembalikan nama file (atau null bila tidak ada kegagalan).
     */
    public static function save(array $gagalHutang, array $gagalPiutang, int $lewati2026): ?string
    {
        $semua = array_merge($gagalHutang, $gagalPiutang);
        if ($semua === [] && $lewati2026 === 0) {
            return null;
        }

        $dir = storage_path('app/' . self::DIR);
        if (! is_dir($dir)) { mkdir($dir, 0775, true); }

        $nama = 'catatan-import-' . date('Ymd_His') . '-' . substr(bin2hex(random_bytes(4)), 0, 6) . '.txt';
        $baris = [
            'CATATAN IMPORT PAYMENT - ' . date('d/m/Y H:i:s'),
            str_repeat('=', 60),
            'Total baris gagal diproses  : ' . count($semua),
            'Baris dilewati (tgl >= 2026): ' . $lewati2026,
            str_repeat('-', 60),
        ];
        foreach ($semua as $i => $e) {
            $baris[] = ($i + 1) . '. ' . $e;
        }
        if ($lewati2026 > 0) {
            $baris[] = '';
            $baris[] = 'Catatan: baris dengan RO Date / Invoice Date tahun >= 2026 dilewati sesuai kebijakan (tidak dianggap gagal).';
        }
        $baris[] = str_repeat('=', 60);

        file_put_contents($dir . '/' . $nama, implode("\r\n", $baris));
        return $nama;
    }

    /**
     * Path aman untuk unduh; tolak path traversal & nama di luar pola.
     */
    public static function path(?string $nama): ?string
    {
        if (! $nama || ! preg_match('/^catatan-import-\d{8}_\d{6}-[a-f0-9]{6}\.txt$/', $nama)) {
            return null;
        }
        $path = storage_path('app/' . self::DIR . '/' . $nama);
        return is_file($path) ? $path : null;
    }
}
