<?php

namespace App\Imports\Support;

use Illuminate\Support\Collection;

class KodeWarisan
{
    /**
     * Isi kolom kode (index 0 untuk v2, 1 untuk legacy) yang kosong dengan kode terakhir di atasnya.
     * Sheet #3 (FORMAT_EXCEL) tidak dipanggil — hanya HUTANG/PIUTANG.
     * Header (index 0) tidak diubah. Baris kosong total (semua kolom kosong) tetap skip (tidak diwarisi).
     */
    public static function fill(Collection $rows, int $kodeIndex = 0): Collection
    {
        $last = null;
        return $rows->map(function ($row, $idx) use (&$last, $kodeIndex) {
            if ($idx === 0) {
                // header: update last jika header sendiri berisi kode? tidak, keep
                return $row;
            }
            // simpan last jika row ini punya kode
            $kode = trim((string) ($row[$kodeIndex] ?? ''));
            if ($kode !== '') {
                $last = $kode;
                return $row;
            }
            // kode kosong
            if ($last !== null && !self::isRowEmpty($row)) {
                // warisi
                if (is_array($row)) {
                    $row[$kodeIndex] = $last;
                } else {
                    // Collection row (ArrayAccess)
                    $row[$kodeIndex] = $last;
                }
            }
            return $row;
        });
    }

    private static function isRowEmpty($row): bool
    {
        $arr = is_array($row) ? $row : $row->toArray();
        foreach ($arr as $v) {
            if ($v !== null && trim((string) $v) !== '') {
                return false;
            }
        }
        return true;
    }
}
