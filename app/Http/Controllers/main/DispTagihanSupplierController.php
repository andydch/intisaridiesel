<?php

namespace App\Http\Controllers\main;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Tx_tagihan_supplier;

class DispTagihanSupplierController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
        $is_vat = 'X';
        switch ($request->payment_type_id) {
            case 'P':
                $is_vat = 'Y';
                break;
            case 'N':
                $is_vat = 'N';
                break;
            default:
                $is_vat = 'X';
        }

        $query = Tx_tagihan_supplier::select(
            'id',
            'tagihan_supplier_no',
        )
        ->where([
            'supplier_id' => $request->supplier_id,
            'is_vat' => $is_vat,
            'active' => 'Y',
        ])
        ->when($request->payment_mode_id==1, function($q1){
            $q1->whereIn('bank_id', function($q2){
                $q2->select('id')
                ->from('mst_coas')
                ->where('coa_code_complete', 'LIKE', '111%')
                ->where('active', '=', 'Y');
            });
        })
        ->when($request->payment_mode_id==2, function($q1){
            $q1->whereIn('bank_id', function($q2){
                $q2->select('id')
                ->from('mst_coas')
                ->where('coa_code_complete', 'LIKE', '112%')
                ->where('active', '=', 'Y');
            });
        })
        ->when($request->payment_mode_id==3, function($q1){
            $q1->whereIn('bank_id', function($q2){
                $q2->select('id')
                ->from('mst_coas')
                ->where('coa_code_complete', 'LIKE', '116%')
                ->where('active', '=', 'Y');
            });
        })
        ->when($request->pv_id==0, function($q1) use($request) {
            $q1->whereNotIn('id', function($q2) use($request) {
                $q2->select('tagihan_supplier_id')
                ->from('tx_payment_vouchers')
                ->where('supplier_id', $request->supplier_id)
                ->whereRaw('tagihan_supplier_id IS NOT NULL')
                ->where('is_full_payment', 'Y')
                ->where('active', 'Y');
            });
        })
        ->when($request->pv_id!=0 && is_numeric($request->pv_id), function($qX) use($request) {
            $qX->where(function($q) use($request) {
                $q->where(function($q1) use($request){
                    $q1->whereNotIn('id', function($q2) use($request) {
                        $q2->select('tagihan_supplier_id')
                        ->from('tx_payment_vouchers')
                        ->when($request->pv_id!=0 && is_numeric($request->pv_id), function($q) use($request) {
                            $q->whereRaw('id<>'.$request->pv_id);
                        })
                        ->where('supplier_id', $request->supplier_id)
                        ->whereRaw('tagihan_supplier_id IS NOT NULL')
                        ->where('is_full_payment', 'Y')
                        ->where('active', 'Y');
                    });
                })
                ->orWhere(function($q1) use($request){
                    $q1->whereIn('id', function($q2) use($request) {
                        $q2->select('tagihan_supplier_id')
                        ->from('tx_payment_vouchers')
                        ->when($request->pv_id!=0 && is_numeric($request->pv_id), function($q) use($request) {
                            $q->whereRaw('id<>'.$request->pv_id);
                        })
                        ->where('supplier_id', $request->supplier_id)
                        ->whereRaw('tagihan_supplier_id IS NOT NULL')
                        ->where('is_full_payment', 'N')
                        ->where('active', 'Y');
                    });
                });
            });            
        })
        ->orderBy('id', 'DESC')
        ->get();
        // dd($query);
        $data = [
            'tagihan_suppliers' => $query->toArray()
        ];
        return response()->json([
            $data
        ], 200);
    }
}
