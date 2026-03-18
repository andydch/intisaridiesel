<?php

namespace App\Http\Controllers\main;

use Illuminate\Http\Request;
use App\Models\Tx_surat_jalan;
use App\Http\Controllers\Controller;
use Illuminate\Database\Query\Builder;

class DispSJbyCustController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
        $qSO = Tx_surat_jalan::select(
            'id',
            'surat_jalan_no'
        )
        ->where('surat_jalan_no','NOT LIKE','%Draft%')
        ->whereNotIn('id', function (Builder $query) {
            $query->select('tsop.surat_jalan_id')
            ->from('tx_nota_retur_part_non_taxes')
            ->leftJoin('tx_surat_jalan_parts AS tsop','tx_nota_retur_part_non_taxes.surat_jalan_part_id','=','tsop.id')
            ->where('tx_nota_retur_part_non_taxes.active','=','Y');
        })
        ->whereIn('id', function ($q1) use($request) {
            $q1->select('sales_order_id')
            ->from('tx_delivery_order_non_tax_parts')
            ->where([
                'delivery_order_id'=>$request->np_id,
                'active'=>'Y',
            ]);
        })
        ->where('active','=','Y')
        ->orderBy('surat_jalan_no','ASC')
        ->get();

        $data = [
            'suratjalans' => $qSO->toArray(),
        ];
        return response()->json([
            $data
        ], 200);
    }
}
