<?php

namespace App\Http\Controllers\main;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Mst_supplier;

class DispSupplierByIdController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
        $query = Mst_supplier::leftJoin('mst_provinces', 'mst_suppliers.province_id', '=', 'mst_provinces.id')
        ->leftJoin('mst_cities', 'mst_suppliers.city_id', '=', 'mst_cities.id')
        ->leftJoin('mst_districts', 'mst_suppliers.district_id', '=', 'mst_districts.id')
        ->leftJoin('mst_sub_districts', 'mst_suppliers.sub_district_id', '=', 'mst_sub_districts.id')
        ->leftJoin('mst_countries', 'mst_provinces.country_id', '=', 'mst_countries.id')
        ->select(
            'mst_suppliers.supplier_type_id',
            'mst_suppliers.name',
            'mst_suppliers.slug',
            'mst_suppliers.office_address',
            'mst_suppliers.npwp_no',
            'mst_provinces.province_name',
            'mst_cities.city_name',
            'mst_districts.district_name',
            'mst_sub_districts.sub_district_name',
            'mst_sub_districts.post_code',
            'mst_countries.country_name',
        )
        ->where('mst_suppliers.id', '=', $request->id)
        ->where('mst_suppliers.active', '=', 'Y')
        ->first();
        $data = [
            'suppliers' => $query->toArray()
        ];
        return response()->json([
            $data
        ], 200);
    }
}
