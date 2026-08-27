<?php

namespace App\Imports;

use Carbon\Carbon;

class HutangRowValidator
{
    /**
     * Validasi satu baris worksheet kartu-hutang (layout revisi, index 0-15).
     * Murni format/tipe — lookup referensi DB dilakukan di sheet importer (pre-flight).
     */
    public static function validate(array $row, int $barisKe): object
    {
        $kode = trim((string) ($row[1] ?? ''));
        if ($kode === '' && self::semuaKosong($row)) {
            return (object) ['ok' => false, 'skip' => true, 'error' => "Baris {$barisKe}: kosong"];
        }

        $tanggal = self::parseTanggal($row[2] ?? null);
        if (! $tanggal) return self::err($barisKe, 'RO Date harus DD/MM/YYYY atau tanggal Excel valid');

        $jt = strtoupper(trim((string) ($row[3] ?? '')));
        if (! in_array($jt, ['P', 'N'], true)) return self::err($barisKe, 'Jurnal Type harus P atau N');

        $cur = strtoupper(trim((string) ($row[4] ?? '')));
        if (! in_array($cur, ['RP', 'USD'], true)) return self::err($barisKe, "Currency harus RP atau USD");

        $wajibAngka = ['F(DPP)' => 5, 'G(PPN)' => 6, 'H(DPP+PPN)' => 7, 'I(DPP FOB)' => 8, 'J(PPN FOB)' => 9, 'K(DPP+PPN FOB)' => 10];
        $angka = [];
        foreach ($wajibAngka as $label => $idx) {
            $v = self::toFloat($row[$idx] ?? null);
            if ($v === null) return self::err($barisKe, "Kolom {$label} harus terisi angka");
            $angka[$idx] = $v;
        }

        $branch = trim((string) ($row[11] ?? ''));
        if ($branch === '') return self::err($barisKe, 'Branch wajib terisi');

        $invoiceNo = trim((string) ($row[12] ?? ''));
        $invoiceNo = $invoiceNo !== '' ? $invoiceNo : null;

        $coa = self::normCoa($row[13] ?? null);
        if ($coa === '') return self::err($barisKe, 'CTS COA Code wajib terisi');

        $planDate = self::parseTanggal($row[14] ?? null);
        if (! $planDate) return self::err($barisKe, 'CTS Payment Plan Date tidak valid');

        $kodeTrans = trim((string) ($row[15] ?? ''));
        if ($kodeTrans === '') return self::err($barisKe, 'Kode Transaksi wajib terisi');

        return (object) ['ok' => true, 'skip' => false, 'data' => (object) [
            'supplierCode' => $kode,
            'tanggal'      => $tanggal,
            'journalType'  => $jt,
            'currencyKode' => $cur,
            'dppF'         => $angka[5],
            'ppnG'         => $angka[6],
            'dppPpnH'      => $angka[7],
            'dppI'         => $angka[8],
            'ppnJ'         => $angka[9],
            'dppPpnK'      => $angka[10],
            'branchName'   => $branch,
            'invoiceNo'    => $invoiceNo,
            'coaCode'      => $coa,
            'planDate'     => $planDate,
            'kodeTrans'    => $kodeTrans,
        ]];
    }

    private static function err(int $n, string $m): object
    {
        return (object) ['ok' => false, 'skip' => false, 'error' => "Baris {$n}: {$m}"];
    }

    private static function semuaKosong(array $row): bool
    {
        foreach (range(1, 15) as $i) {
            if (isset($row[$i]) && trim((string) $row[$i]) !== '') return false;
        }
        return true;
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
        // 21512.0 -> "21512" ; nilai non-numerik dikembalikan apa adanya (untuk pesan error jelas)
        $s = preg_replace('/[^\d]/', '', (string) $v);
        return $s !== '' ? $s : trim((string) $v);
    }
}
