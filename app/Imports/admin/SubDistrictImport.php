<?php

namespace App\Imports\admin;

use App\Models\Mst_sub_district;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\ToCollection;

class SubDistrictImport implements ToCollection
{
    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            $id = $row[0];
            $sub_district_name = $row[1];
            $post_code = str_replace("'", "", $row[2]);
            $district_id = $row[3];
            $active = $row[8];
            if (strtolower($id) != 'id') {
                $query = Mst_sub_district::where('id', '=', $id)
                    ->first();
                if (!$query) {
                    $ins = Mst_sub_district::create([
                        'sub_district_name' => $sub_district_name,
                        'post_code' => $post_code,
                        'district_id' => $district_id,
                        'active' => $active,
                        'created_by' => Auth::user()->id,
                        'updated_by' => Auth::user()->id
                    ]);
                } else {
                    $upd = Mst_sub_district::where('id', '=', $id)
                        ->update([
                            'sub_district_name' => $sub_district_name,
                            'post_code' => $post_code,
                            'district_id' => $district_id,
                            'active' => $active,
                            'updated_by' => Auth::user()->id
                        ]);
                }
            }
        }
    }
}
