<?php

namespace App\Http\Controllers\main;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Mst_courier;

class DispSimilarCourierController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
        $query = Mst_courier::where('name', 'LIKE', '%' . $request->courierName . '%',)
            ->where('active', '=', 'Y')
            ->orderBy('name', 'ASC')
            ->get();
        $data = [
            'couriers' => $query->toArray()
        ];
        return response()->json([
            $data
        ], 200);
    }
}
