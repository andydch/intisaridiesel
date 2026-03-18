<?php

namespace App\Http\Controllers\main;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Tx_surat_jalan_part;

class DispSuratJalanWithInfoController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
        $parts = Tx_surat_jalan_part::leftJoin('mst_parts AS mp','tx_surat_jalan_parts.part_id','=','mp.id')
        ->leftJoin('mst_globals AS mg01','mp.quantity_type_id','=','mg01.id')
        ->leftJoin('mst_globals AS mg02','mp.weight_id','=','mg02.id')
        ->select(
            'tx_surat_jalan_parts.id AS surat_jalan_part_id',
            'tx_surat_jalan_parts.surat_jalan_id AS surat_jalan_id',
            'tx_surat_jalan_parts.part_id',
            'tx_surat_jalan_parts.part_no',
            'tx_surat_jalan_parts.qty',
            'tx_surat_jalan_parts.price',
            'mp.part_name',
            'mg01.string_val AS part_unit',
            'mp.weight',
            'mg02.string_val AS weight_unit',
        )
        ->where([
            'tx_surat_jalan_parts.surat_jalan_id' => $request->order_id,
            'tx_surat_jalan_parts.active' => 'Y'
        ])
        ->get();

        $data = [
            'parts' => $parts->toArray(),
        ];
        return response()->json([
            $data
        ], 200);
    }
}
