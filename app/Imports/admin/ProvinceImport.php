<?php

namespace App\Imports\admin;

use App\Models\Mst_province;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\ToCollection;

class ProvinceImport implements ToCollection
{
    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            $id = $row[0];
            $province_name = $row[1];
            $country_id = $row[2];
            $active = $row[4];
            if (strtolower($id) != 'id') {
                $query = Mst_province::where('id', '=', $id)->first();
                if (!$query) {
                    $ins = Mst_province::create([
                        'province_name' => $province_name,
                        'country_id' => $country_id,
                        'active' => $active,
                        'created_by' => Auth::user()->id,
                        'updated_by' => Auth::user()->id
                    ]);
                } else {
                    $upd = Mst_province::where('id', '=', $id)
                        ->update([
                            'province_name' => $province_name,
                            'country_id' => $country_id,
                            'active' => $active,
                            'updated_by' => Auth::user()->id
                        ]);
                }
            }
        }
    }
}
