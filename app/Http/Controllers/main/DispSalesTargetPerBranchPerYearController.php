<?php

namespace App\Http\Controllers\main;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Mst_branch_target_detail;

class DispSalesTargetPerBranchPerYearController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
        $q = Mst_branch_target_detail::where([
            'branch_id' => $request->branch_id,
            'year_per_branch' => $request->year,
            'active' => 'Y',
        ])
        ->first();

        $data = [
            'branch_sales_target' => $q->toArray()
        ];
        return response()->json([
            $data
        ], 200);
    }
}
