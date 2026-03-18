<?php

namespace App\Http\Controllers\dbg;

use App\Http\Controllers\Controller;
use App\Models\Tx_sales_order_part;
use App\Models\Tx_surat_jalan_part;
use App\Models\V_log_avg_cost;
use Illuminate\Http\Request;

class UpdAvgSOSJController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $qPart = Tx_sales_order_part::whereRaw('last_avg_cost IS NULL')
        ->get();
        foreach($qPart as $qP){
            $avg = V_log_avg_cost::where([
                'part_id'=>$qP->part_id,
            ])
            ->whereRaw('updated_at<=\''.$qP->created_at.'\'')
            ->orderBy('updated_at','DESC')
            ->first();
            if ($avg){
                $updPart = Tx_sales_order_part::where('id','=',$qP->id)
                ->update([
                    'last_avg_cost'=>$avg->avg_cost,
                ]);
            }
        }

        $qPart = Tx_surat_jalan_part::whereRaw('last_avg_cost IS NULL')
        ->get();
        foreach($qPart as $qP){
            $avg = V_log_avg_cost::where([
                'part_id'=>$qP->part_id,
            ])
            ->whereRaw('updated_at<=\''.$qP->created_at.'\'')
            ->orderBy('updated_at','DESC')
            ->first();
            if ($avg){
                $updPart = Tx_surat_jalan_part::where('id','=',$qP->id)
                ->update([
                    'last_avg_cost'=>$avg->avg_cost,
                ]);
            }
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
