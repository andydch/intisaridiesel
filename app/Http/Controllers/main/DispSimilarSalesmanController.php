<?php

namespace App\Http\Controllers\main;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Mst_salesman;

class DispSimilarSalesmanController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
        $query = Mst_salesman::where('mst_salesmans.name', 'LIKE', '%' . $request->salesmanName . '%',)
            ->where('mst_salesmans.active', '=', 'Y')
            ->orderBy('mst_salesmans.name', 'ASC')
            ->get();
        $data = [
            'salesmans' => $query->toArray()
        ];
        return response()->json([
            $data
        ], 200);
    }
}
