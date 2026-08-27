<?php

namespace App\Imports\Support;

use Illuminate\Support\Facades\DB;

class DocNumber
{
    /**
     * Penomoran dokumen berbasis MAX-scan (aturan backdate R1–R4):
     * cari nomor terakhir dengan prefix yang diberikan (abaikan yang mengandung
     * kata "Draft"), ambil angka setelah tanda minus lalu +1, pad 5 digit.
     * Tabel auto_incs TIDAK disentuh sama sekali.
     */
    public static function nextNo(string $table, string $column, string $prefix): string
    {
        $last = DB::table($table)
            ->where($column, 'LIKE', $prefix . '%')
            ->where($column, 'NOT LIKE', '%Draft%')
            ->max($column);

        $num = $last ? ((int) substr($last, strlen($prefix))) + 1 : 1;

        return $prefix . str_pad((string) $num, 5, '0', STR_PAD_LEFT);
    }
}
