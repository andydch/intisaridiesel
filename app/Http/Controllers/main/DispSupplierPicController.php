<?php

namespace App\Http\Controllers\main;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Mst_supplier;
use App\Models\Mst_supplier_bank_information;

class DispSupplierPicController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
        $query = Mst_supplier::leftJoin('mst_sub_districts', 'mst_suppliers.sub_district_id', '=', 'mst_sub_districts.id')
        ->leftJoin('mst_districts', 'mst_suppliers.district_id', '=', 'mst_districts.id')
        ->leftJoin('mst_cities', 'mst_suppliers.city_id', '=', 'mst_cities.id')
        ->leftJoin('mst_provinces', 'mst_suppliers.province_id', '=', 'mst_provinces.id')
        ->leftJoin('mst_countries', 'mst_suppliers.country_id', '=', 'mst_countries.id')
        ->leftJoin('mst_globals AS supplierType', 'mst_suppliers.supplier_type_id', '=', 'supplierType.id')
        ->leftJoin('mst_globals AS entityType', 'mst_suppliers.entity_type_id', '=', 'entityType.id')
        ->select(
            'mst_suppliers.id AS supplier_id',
            'mst_suppliers.name AS supplier_name',
            'mst_suppliers.office_address',
            'mst_suppliers.pic1_name',
            'mst_suppliers.pic2_name',
            'mst_suppliers.supplier_type_id',
            'mst_sub_districts.sub_district_name',
            'mst_sub_districts.post_code',
            'mst_districts.district_name',
            'mst_cities.city_name',
            'mst_cities.city_type',
            'mst_provinces.province_name',
            'mst_countries.country_name',
            'supplierType.title_ind AS supplier_type_name',
            'entityType.title_ind AS entity_type_name',
        )
        ->where([
            'mst_suppliers.id' => $request->supplier_id,
            'mst_suppliers.active' => 'Y'
        ])
        ->orderBy('mst_suppliers.name', 'ASC')
        ->get();

        $qCurr = Mst_supplier_bank_information::leftJoin('mst_globals as curr','mst_supplier_bank_information.currency_id','=','curr.id')
        ->select(
            'curr.title_ind as curr_name',
            'curr.string_val as curr_code',
            'curr.id as curr_id'
            )
        ->where([
            'mst_supplier_bank_information.supplier_id' => $request->supplier_id,
        ])
        ->orderBy('mst_supplier_bank_information.created_at','ASC')
        ->first();

        $data = [
            'supplier_pic' => $query->toArray(),
            'supplier_curr' => (!is_null($qCurr)?$qCurr->toArray():''),
        ];
        return response()->json([
            $data
        ], 200);
    }
}
