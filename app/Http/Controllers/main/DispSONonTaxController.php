<?php

namespace App\Http\Controllers\main;

use App\Models\Userdetail;
use Illuminate\Http\Request;
use App\Models\Tx_surat_jalan;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Contracts\Database\Query\Builder;

class DispSONonTaxController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
        $userLogin = Userdetail::where('user_id','=',Auth::user()->id)
        ->first();

        $surat_jalan = Tx_surat_jalan::leftJoin('userdetails AS usr','tx_surat_jalans.created_by','=','usr.user_id')
        ->select('tx_surat_jalans.id','tx_surat_jalans.surat_jalan_no AS order_no')
        ->where('tx_surat_jalans.surat_jalan_no','NOT LIKE','%Draft%')
        ->whereNotIn('tx_surat_jalans.id', function (Builder $queryQ) {
            $queryQ->select('tx_do_part.sales_order_id')
                ->from('tx_delivery_order_non_tax_parts as tx_do_part')
                ->where('tx_do_part.active','=','Y');
        })
        ->where(function($query) {
            $query->where('tx_surat_jalans.approved_by','<>',null)
                ->orWhere(function($queryA) {
                $queryA->where('tx_surat_jalans.approved_by','=',null)
                    ->where('tx_surat_jalans.need_approval','=','N');
            });
        })
        ->where([
            'tx_surat_jalans.customer_id' => $request->customer_id,
            'tx_surat_jalans.active' => 'Y',
        ])
        ->when($request->surat_jalan_date!='#', function($query) use($request) {
            $query->where('tx_surat_jalans.surat_jalan_date','=',$request->surat_jalan_date);
        })
        ->when($userLogin->is_director=='N', function($query) use($userLogin) {
            $query->where('usr.branch_id','=',$userLogin->branch_id);
        })
        ->get();

        $data = [
            'surat_jalan' => $surat_jalan->toArray()
        ];
        return response()->json([
            $data
        ], 200);
    }
}
