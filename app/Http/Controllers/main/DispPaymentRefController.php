<?php

namespace App\Http\Controllers\main;

use App\Http\Controllers\Controller;
use App\Models\Mst_global;
use Illuminate\Http\Request;

class DispPaymentRefController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
        $query = Mst_global::select(
            'id',
            'title_ind',
            'title_eng',
            'slug',
        )
        ->when($request->payment_mode_id==1, function($q){
            // 51: Cash

            $q->whereIn('id',[51]);
        })
        ->when($request->payment_mode_id==2, function($q){
            // 49: EDC
            // 50: Giro
            // 63: Bank Transfer

            $q->whereIn('id',[49,50,63]);
        })
        ->when($request->payment_mode_id==3, function($q){
            // 9999: <empty>
            // MNI: 351; UID: 211
            // MNI: 356; UID: 214

            // $q->whereIn('id',[9999]);
            $q->where([
                'data_cat' => 'payment-ref',
                'slug' => 'customer-deposit',
            ]);
        })
        ->where([
            'data_cat'=>'payment-ref',
            'active'=>'Y',
        ])
        ->orderBy('title_ind','ASC')
        ->get();
        $data = [
            'refs' => $query->toArray()
        ];
        return response()->json([
            $data
        ], 200);
    }
}
