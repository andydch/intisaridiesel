<?php

namespace App\Imports\admin;

use App\Models\Mst_customer_shipment_address;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\ToCollection;

class CustomerShipmentImport implements ToCollection
{
    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            $id = $row[0];
            $customer_id = $row[1];
            $address = $row[3];
            $province_id = $row[4];
            $city_id = $row[6];
            $district_id = $row[8];
            $sub_district_id = $row[10];
            $phone = str_replace("'", "", $row[12]);
            $active = $row[13];
            if (strtolower($id) != 'id') {
                $query = Mst_customer_shipment_address::where('id', '=', $id)->first();
                if (!$query) {
                    $ins = Mst_customer_shipment_address::create([
                        'customer_id' => $customer_id,
                        'address' => $address,
                        'province_id' => $province_id,
                        'city_id' => $city_id,
                        'district_id' => $district_id,
                        'sub_district_id' => $sub_district_id,
                        'phone' => $phone,
                        'active' => $active,
                        'created_by' => Auth::user()->id,
                        'updated_by' => Auth::user()->id
                    ]);
                } else {
                    $upd = Mst_customer_shipment_address::where('id', '=', $id)
                        ->update([
                            'customer_id' => $customer_id,
                            'address' => $address,
                            'province_id' => $province_id,
                            'city_id' => $city_id,
                            'district_id' => $district_id,
                            'sub_district_id' => $sub_district_id,
                            'phone' => $phone,
                            'active' => $active,
                            'updated_by' => Auth::user()->id
                        ]);
                }
            }
        }
    }
}
