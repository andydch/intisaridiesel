<?php

namespace App\Imports\admin;

use App\Models\Mst_city;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\ToCollection;

class CityImport implements ToCollection
{
    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            $id = $row[0];
            $city_name = $row[1];
            $city_type = $row[2];
            $province_id = $row[3];
            $country_id = $row[5];
            $active = $row[7];
            if (strtolower($id) != 'id') {
                $query = Mst_city::where('id', '=', $id)->first();
                if (!$query) {
                    $ins = Mst_city::create([
                        'city_name' => $city_name,
                        'city_type' => $city_type,
                        'province_id' => $province_id,
                        'country_id' => $country_id,
                        'active' => $active,
                        'created_by' => Auth::user()->id,
                        'updated_by' => Auth::user()->id
                    ]);
                } else {
                    $upd = Mst_city::where('id', '=', $id)
                        ->update([
                            'city_name' => $city_name,
                            'city_type' => $city_type,
                            'province_id' => $province_id,
                            'country_id' => $country_id,
                            'active' => $active,
                            'updated_by' => Auth::user()->id
                        ]);
                }
            }
        }
    }
}
