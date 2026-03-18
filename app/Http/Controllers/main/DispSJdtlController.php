<?php

namespace App\Http\Controllers\main;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Tx_surat_jalan;

class DispSJdtlController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
        $sj = Tx_surat_jalan::where('surat_jalan_no','=',$request->so_no)
        ->first();

        $data = [
            'surat_jalan' => $sj->toArray()
        ];
        return response()->json([
            $data
        ], 200);
    }
}
