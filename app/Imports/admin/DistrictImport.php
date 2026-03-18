<?php

namespace App\Imports\admin;

use App\Models\Mst_district;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\ToCollection;

class DistrictImport implements ToCollection
{
    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            $id = $row[0];
            $district_name = $row[1];
            $city_id = $row[2];
            $active = $row[6];
            if (strtolower($id) != 'id') {
                $query = Mst_district::where('id', '=', $id)->first();
                if (!$query) {
                    $ins = Mst_district::create([
                        'district_name' => $district_name,
                        'city_id' => $city_id,
                        'active' => $active,
                        'created_by' => Auth::user()->id,
                        'updated_by' => Auth::user()->id
                    ]);
                } else {
                    $upd = Mst_district::where('id', '=', $id)
                        ->update([
                            'district_name' => $district_name,
                            'city_id' => $city_id,
                            'active' => $active,
                            'updated_by' => Auth::user()->id
                        ]);
                }
            }
        }
    }
}
