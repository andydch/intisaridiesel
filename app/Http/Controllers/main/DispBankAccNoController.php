<?php

namespace App\Http\Controllers\main;

use App\Models\Mst_coa;
use App\Models\Userdetail;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class DispBankAccNoController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
        $supplier_id = $request->supplier_id;
        $payment_group = $request->payment_group;
        $payment_mode_id = $request->payment_mode_id;

        $userLogin = Userdetail::where('user_id','=',Auth::user()->id)
        ->first();

        $query = Mst_coa::select(
            'id',
            'coa_name'
        )
        ->when($payment_mode_id==1, function($q) use($payment_group,$supplier_id,$userLogin){
            $q->whereIn('id', function($q1) use($payment_group,$supplier_id,$userLogin){
                $q1->select('coa_code_id')
                ->from('mst_automatic_journal_details')
                ->where('auto_journal_id', '=', $payment_group)
                ->where('method_id', '=', 1)
                ->when(Auth::user()->id!=1, function($q2) use($userLogin){
                    $q2->where('branch_id', '=', ($userLogin?$userLogin->branch_id:0));
                })                
                ->where('active', '=', 'Y')
                ->whereRaw('LOWER(`desc`)=\'cash\'');
            })
            ->orWhereIn('id', function($q1) use($payment_group,$supplier_id,$userLogin){
                $q1->select('coa_code_id')
                ->from('mst_automatic_journal_detail_exts')
                ->where('auto_journal_id', '=', $payment_group)
                ->where('method_id', '=', 1)
                ->when(Auth::user()->id!=1, function($q2) use($userLogin){
                    $q2->where('branch_id', '=', ($userLogin?$userLogin->branch_id:0));
                })
                ->where('active', '=', 'Y')
                ->whereRaw('LOWER(`desc`)=\'cash\'');
            });
        })
        ->when($payment_mode_id==2, function($q) use($payment_group,$supplier_id,$userLogin){
            $q->whereIn('id', function($q1) use($payment_group,$supplier_id,$userLogin){
                $q1->select('coa_code_id')
                ->from('mst_automatic_journal_details')
                ->where('auto_journal_id', '=', $payment_group)
                ->where('method_id', '=', 2)
                ->when(Auth::user()->id!=1, function($q2) use($userLogin){
                    $q2->where('branch_id', '=', ($userLogin?$userLogin->branch_id:0));
                })
                ->where('active', '=', 'Y')
                ->whereRaw('LOWER(`desc`)=\'bank\'');
            })
            ->orWhereIn('id', function($q1) use($payment_group,$supplier_id,$userLogin){
                $q1->select('coa_code_id')
                ->from('mst_automatic_journal_detail_exts')
                ->where('auto_journal_id', '=', $payment_group)
                ->where('method_id', '=', 2)
                ->when(Auth::user()->id!=1, function($q2) use($userLogin){
                    $q2->where('branch_id', '=', ($userLogin?$userLogin->branch_id:0));
                })
                ->where('active', '=', 'Y')
                ->whereRaw('LOWER(`desc`)=\'bank\'');
            });
        })
        ->when($payment_mode_id==3, function($q) use($payment_group,$supplier_id,$userLogin){
            $q->whereIn('id', function($q1) use($payment_group,$supplier_id,$userLogin){
                $q1->select('coa_code_id')
                ->from('mst_automatic_journal_details')
                ->where('auto_journal_id', '=', $payment_group)
                ->where('method_id', '=', 3)
                ->when(Auth::user()->id!=1, function($q2) use($userLogin){
                    $q2->where('branch_id', '=', ($userLogin?$userLogin->branch_id:0));
                })                
                ->where('active', '=', 'Y')
                ->whereRaw('LOWER(`desc`)=\'advance payment\'');
            })
            ->orWhereIn('id', function($q1) use($payment_group,$supplier_id,$userLogin){
                $q1->select('coa_code_id')
                ->from('mst_automatic_journal_detail_exts')
                ->where('auto_journal_id', '=', $payment_group)
                ->where('method_id', '=', 3)
                ->when(Auth::user()->id!=1, function($q2) use($userLogin){
                    $q2->where('branch_id', '=', ($userLogin?$userLogin->branch_id:0));
                })
                ->where('active', '=', 'Y')
                ->whereRaw('LOWER(`desc`)=\'advance payment\'');
            });
        })
        ->where([
            // 'coa_level' => 5,
            // 'is_master_coa' => 'N',
            'active' => 'Y'
        ])
        ->orderBy('coa_name','ASC')
        ->get();
        $data = [
            'bankaccno' => $query->toArray()
        ];
        return response()->json([
            $data
        ], 200);
    }
}
