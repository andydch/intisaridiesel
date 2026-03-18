<?php

namespace App\Http\Controllers\main;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Mst_part;
use App\Models\Tx_surat_jalan_part;
use App\Models\Tx_delivery_order_non_tax;

class DispQuotationSuratJalanPartRefController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
        $query = Mst_part::leftJoin('tx_qty_parts', 'mst_parts.id', '=', 'tx_qty_parts.part_id')
        ->leftJoin('mst_globals as mg01', 'mst_parts.part_type_id', '=', 'mg01.id')
        ->leftJoin('mst_globals as mg02', 'mst_parts.quantity_type_id', '=', 'mg02.id')
        ->leftJoin('mst_globals as mg03', 'mst_parts.brand_id', '=', 'mg03.id')
        ->select(
            'mst_parts.id as part_id',
            'mst_parts.part_number',
            'mst_parts.part_name',
            'mst_parts.price_list',
            'mst_parts.final_cost',
            'mst_parts.final_price',
            'mst_parts.avg_cost',
            'tx_qty_parts.qty as total_qty',
            'mg01.title_ind as part_type_name',
            'mg02.title_ind as part_unit_name',
            'mg03.title_ind as brand_name',
        )
        ->where([
            'mst_parts.id' => $request->part_id,
            'mst_parts.active' => 'Y',
            'tx_qty_parts.branch_id' => $request->branch_id,
        ])
        ->get();

        $soQty = 0;
        $qSO = Tx_surat_jalan_part::leftJoin('tx_surat_jalans AS txso','tx_surat_jalan_parts.surat_jalan_id','=','txso.id')
        ->leftJoin('userdetails AS usr','txso.created_by','=','usr.user_id')
        ->select(
            'tx_surat_jalan_parts.qty',
            'txso.surat_jalan_no',
        )
        ->where([
            'tx_surat_jalan_parts.part_id' => $request->part_id,
            'tx_surat_jalan_parts.active' => 'Y',
            'txso.active' => 'Y',
            'usr.branch_id' => $request->branch_id,
        ])
        ->where('txso.surat_jalan_no','NOT LIKE','%Draft%')
        ->get();
        foreach($qSO as $q){
            $qDO = Tx_delivery_order_non_tax::where('delivery_order_no','NOT LIKE','%Draft%')
            ->where('sales_order_no_all','LIKE','%'.$q->surat_jalan_no.'%')
            ->where('active','=','Y')
            ->first();
            if(!$qDO){
                $soQty += $q->qty;
            }
        }

        $data = [
            'parts' => $query->toArray(),
            'soQty' => $soQty
        ];
        return response()->json([
            $data
        ], 200);
    }
}
