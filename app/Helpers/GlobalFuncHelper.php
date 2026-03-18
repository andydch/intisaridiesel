<?php

namespace App\Helpers;

class GlobalFuncHelper
{
    public static function moneyValidate($val)
    {
        $val = str_replace(",", "", $val);
        return $val;
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
