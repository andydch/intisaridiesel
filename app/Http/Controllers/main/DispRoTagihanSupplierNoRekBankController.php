<?php

namespace App\Http\Controllers\main;

use App\Http\Controllers\Controller;
use App\Models\Mst_coa;
use App\Models\Userdetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DispRoTagihanSupplierNoRekBankController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
        $userLogin = Userdetail::where('user_id', '=', Auth::user()->id)
        ->first();
        
        $qCoas = Mst_coa::where(function($q){
            $q->where('coa_code_complete','LIKE','111%')
            ->orWhere('coa_code_complete','LIKE','112%')
            ->orWhere('coa_code_complete','LIKE','116%');
        })
        ->where('is_master_coa', '=', 'N')
        ->when($request->lc=='A', function($q) {
            $q->whereIn('local', ['A', 'P', 'N']);
        })
        ->when($request->lc=='P', function($q) {
            $q->whereIn('local', ['A', 'P']);
        })
        ->when($request->lc=='N', function($q) {
            $q->whereIn('local', ['A', 'N']);
        })
        ->when($request->lc!='A' && $request->lc!='P' && $request->lc!='N', function($q) {
            $q->where('local', '=', 'X');
        })
        ->when($userLogin->is_director!='Y' && Auth::user()->email!='ekadessyarfianti@gmail.com' && Auth::user()->id!=16 && Auth::user()->id!=1, function($q) use($userLogin) {
            $q->where('branch_id', $userLogin->branch_id);
        })
        ->where('active', '=', 'Y')
        ->orderBy('coa_name', 'ASC')
        ->get();

        $data = [
            'acc_nos' => $qCoas->toArray(),
        ];
        return response()->json([
            $data
        ], 200);
    }
}
