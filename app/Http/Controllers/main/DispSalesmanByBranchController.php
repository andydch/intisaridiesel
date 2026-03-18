<?php

namespace App\Http\Controllers\main;

use App\Models\Userdetail;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class DispSalesmanByBranchController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
        $query = [];
        $userInfo = Userdetail::where('user_id','=',Auth::user()->id)
        ->first();
        if($userInfo){
            if($userInfo->is_director=='Y' || $userInfo->is_branch_head=='Y' || Auth::user()->id==1){
                // direktur, kepala cabang, superuser
                $query = Userdetail::leftJoin('users','userdetails.user_id','=','users.id')
                ->select(
                    'userdetails.user_id',
                    'users.name AS salesman_name'
                )
                ->where('userdetails.branch_id','=',$request->branch_id)
                ->where('userdetails.is_salesman','=','Y')
                ->where('userdetails.active','=','Y')
                ->get();
            }else{
                // karyawan biasa
                $query = Userdetail::leftJoin('users','userdetails.user_id','=','users.id')
                ->select(
                    'userdetails.user_id',
                    'users.name AS salesman_name'
                )
                ->where('userdetails.user_id','=',Auth::user()->id)
                ->where('userdetails.branch_id','=',$request->branch_id)
                ->where('userdetails.is_salesman','=','Y')
                ->where('userdetails.active','=','Y')
                ->get();
            }
        }


        $data = [
            'salesmans' => $query->toArray()
        ];
        return response()->json([
            $data
        ], 200);
    }
}
