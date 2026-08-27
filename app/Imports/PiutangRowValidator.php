<?php

namespace App\Imports;

use Carbon\Carbon;

class PiutangRowValidator
{
    /**
     * Validasi satu baris worksheet kartu-piutang (layout asli file, index 0-9).
     * Murni format/tipe — lookup referensi DB dilakukan di sheet importer (pre-flight).
     */
    public static function validate(array $row, int $barisKe): object
    {
        $kode = trim((string) ($row[1] ?? ''));
        if ($kode === '') {
            $kosong = true;
            foreach (range(1, 9) as $i) {
                if (isset($row[$i]) && trim((string) $row[$i]) !== '') { $kosong = false; break; }
            }
            if ($kosong) return (object) ['ok' => false, 'skip' => true, 'error' => "Baris {$barisKe}: kosong"];
        }

        $tanggal = self::parseTanggal($row[2] ?? null);
        if (! $tanggal) return self::err($barisKe, 'Invoice Date harus DD/MM/YYYY atau tanggal Excel valid');

        $br = trim((string) ($row[3] ?? ''));
        if ($br === '') return self::err($barisKe, 'Branch wajib terisi');

        $e = self::toFloat($row[4] ?? null);
        if ($e === null) return self::err($barisKe, 'DPP harus terisi angka');
        $f = self::toFloat($row[5] ?? null);
        if ($f === null) return self::err($barisKe, 'PPN harus terisi angka (boleh 0)');
        $g = self::toFloat($row[6] ?? null);
        if ($g === null) return self::err($barisKe, 'TOTAL harus terisi angka');

        $jenis = strtoupper(trim((string) ($row[7] ?? '')));
        if (! in_array($jenis, ['P', 'N'], true)) return self::err($barisKe, 'PPN/NON PPN harus P atau N');

        $metode = trim((string) ($row[8] ?? ''));
        if ($metode === '') return self::err($barisKe, 'Metode Pembayaran wajib terisi');

        $coa = self::normCoa($row[9] ?? null);
        if ($coa === '') return self::err($barisKe, 'COA Code wajib terisi');

        return (object) ['ok' => true, 'skip' => false, 'data' => (object) [
            'customerCode' => $kode,
            'tanggal'      => $tanggal,
            'branchName'   => $br,
            'dppE'         => $e,
            'ppnF'         => $f,
            'totalG'       => $g,
            'jenis'        => $jenis,
            'metodeNm'     => $metode,
            'coaCode'      => $coa,
        ]];
    }

    private static function parseTanggal(mixed $v): ?Carbon
    {
        // Mendukung: DateTime asli, serial angka Excel, dan teks beberapa format umum
        if ($v instanceof \DateTimeInterface) return Carbon::instance($v);
        if (is_numeric($v)) {
            $ts = ((float) $v - 25569) * 86400;          // serial Excel -> UNIX (base 1970-01-01)
            return $ts > 0 ? Carbon::createFromTimestampUTC((int) $ts)->startOfDay() : null;
        }
        $s = trim((string) $v);
        if ($s === '') return null;
        foreach (['d/m/Y', 'Y-m-d', 'Y-m-d H:i:s', 'd/m/Y H:i:s'] as $fmt) {
            try {
                $d = Carbon::hasFormat($s, $fmt) ? Carbon::createFromFormat($fmt, $s) : null;
            } catch (\Throwable) { $d = null; }
            if ($d) return $d->startOfDay();
        }
        return null;
    }

    private static function err(int $n, string $m): object
    {
        return (object) ['ok' => false, 'skip' => false, 'error' => "Baris {$n}: {$m}"];
    }

    private static function toFloat(mixed $v): ?float
    {
        if ($v === null || trim((string) $v) === '') return null;
        $raw = trim((string) $v);
        if ($raw[0] === '=') return null;   // sel berformula ditolak (aturan template: tanpa formula)
        $s = preg_replace('/[^\d.\-]/', '', $raw);
        return is_numeric($s) ? (float) $s : null;
    }

    private static function normCoa(mixed $v): string
    {
        if ($v === null || trim((string) $v) === '') return '';
        $s = preg_replace('/[^\d]/', '', (string) $v);
        return $s !== '' ? $s : trim((string) $v);
    }
}
