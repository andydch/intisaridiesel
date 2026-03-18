<?php

namespace App\Http\Controllers\main;

use App\Models\Userdetail;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class DispSalesmanPerBranchController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
        $q = Userdetail::leftJoin('users','userdetails.user_id','=','users.id')
        ->select(
            'users.id as user_id',
            'users.name',
        )
        ->where('userdetails.branch_id','=',$request->branch_id)
        ->where('userdetails.is_salesman','=','Y')
        ->where('userdetails.active','=','Y')
        ->get();

        $data = [
            'salesmans' => $q->toArray()
        ];
        return response()->json([
            $data
        ], 200);
    }
}
