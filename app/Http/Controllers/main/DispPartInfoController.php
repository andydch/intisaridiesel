<?php

namespace App\Http\Controllers\main;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Mst_part;

class DispPartInfoController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
        $query = Mst_part::where([
            'id' => $request->part_id,
            'active' => 'Y'
        ])
        ->orderBy('part_name', 'ASC')
        ->get();
        $data = [
            'part' => $query->toArray()
        ];
        return response()->json([
            $data
        ], 200);
    }
}
