<?php

namespace App\Http\Controllers\main;

use App\Http\Controllers\Controller;
use App\Models\Tx_surat_jalan;
use Illuminate\Http\Request;

class DispSuratJalanInfoByIdController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
        $qSJ = Tx_surat_jalan::where('id','=',$request->sj_id)
        ->where('active','=','Y')
        ->first();

        $data = [
            'suratjalans' => $qSJ->toArray(),
        ];
        return response()->json([
            $data
        ], 200);
    }
}
