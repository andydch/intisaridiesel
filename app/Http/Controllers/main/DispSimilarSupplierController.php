<?php

namespace App\Http\Controllers\main;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Mst_supplier;

class DispSimilarSupplierController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
        $query = Mst_supplier::where('mst_suppliers.name','=',$request->supplierName)
        // ->where('mst_suppliers.name', 'LIKE', '%'.$request->supplierName.'%')
        ->where('mst_suppliers.active', '=', 'Y')
        ->orderBy('mst_suppliers.name', 'ASC')
        ->get();
        $data = [
            'suppliers' => $query->toArray()
        ];
        return response()->json([
            $data
        ], 200);
    }
}
