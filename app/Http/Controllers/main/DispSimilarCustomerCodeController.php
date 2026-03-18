<?php

namespace App\Http\Controllers\main;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Mst_customer;

class DispSimilarCustomerCodeController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
        $query = Mst_customer::leftJoin('mst_cities', 'mst_customers.city_id', '=', 'mst_cities.id')
            ->select(
                'mst_customers.name AS custName',
                'mst_customers.slug',
                'mst_customers.npwp_no',
                'mst_customers.customer_unique_code',
                'mst_cities.city_name',
            )
            ->where('mst_customers.customer_unique_code', '=', $request->custCode)
            // ->where('mst_customers.customer_unique_code', 'LIKE', '%'.$request->custCode.'%',)
            ->where('mst_customers.active', '=', 'Y')
            ->orderBy('mst_customers.name', 'ASC')
            ->get();
        $data = [
            'customers' => $query->toArray()
        ];
        return response()->json([
            $data
        ], 200);
    }
}
