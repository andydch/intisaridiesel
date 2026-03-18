<?php

namespace App\Http\Controllers\main;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Mst_part;

class DispPartInfoRptController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
        $query = Mst_part::leftJoin('mst_globals AS mg01','mst_parts.part_type_id','=','mg01.id')
        ->select(
            'mst_parts.id AS part_id',
            'mst_parts.part_name',
            'mg01.title_ind AS part_type_name'
        )
        ->where([
            'mst_parts.id' => $request->part_id,
            'mst_parts.active' => 'Y'
        ])
            ->orderBy('mst_parts.part_name', 'ASC')
            ->first();
        $data = [
            'parts' => $query->toArray()
        ];
        return response()->json([
            $data
        ], 200);
    }
}
