<?php

namespace App\Helpers;

class GlobalFuncHelper
{
    public static function moneyValidate($val)
    {
        $val = trim(str_replace(",", "", $val));
        // Nilai kosong/non-numerik dikembalikan sebagai 0 (int), bukan string ""
        // agar aman dipakai di floor()/round() (PHP 8 menolak string non-numerik)
        // dan tidak mengubah nilai yang valid.
        return $val === '' ? 0 : $val;
    }

    // public function startQueryLog()
    // {
    //     \DB::enableQueryLog();
    // }

    // public function showQueries()
    // {
    //     dd(\DB::getQueryLog());
    // }

    // public static function instance()
    // {
    //     return new GlobalFuncHelper();
    // }
}
