<?php

namespace App\Imports\admin;

use App\Models\Mst_branch;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\ToCollection;
use \Cviebrock\EloquentSluggable\Services\SlugService;

class BranchImport implements ToCollection
{
    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            $id = $row[0];
            $initial = $row[1];
            $name = $row[2];
            $address = $row[4];
            $province_id = $row[5];
            $city_id = $row[7];
            $district_id = $row[9];
            $sub_district_id = $row[11];
            $post_code = str_replace("'", "", $row[13]);
            $phone1 = str_replace("'", "", $row[14]);
            $phone2 = str_replace("'", "", $row[15]);
            $active = $row[16];
            if (strtolower($id) != 'id') {
                $query = Mst_branch::where('id', '=', $id)->first();
                if (!$query) {
                    $ins = Mst_branch::create([
                        'initial' => $initial,
                        'name' => $name,
                        'province_id' => $province_id,
                        'city_id' => $city_id,
                        'district_id' => $district_id,
                        'sub_district_id' => $sub_district_id,
                        'address' => $address,
                        'post_code' => $post_code,
                        'phone1' => $phone1,
                        'phone2' => $phone2,
                        'active' => $active,
                        'created_by' => Auth::user()->id,
                        'updated_by' => Auth::user()->id
                    ]);
                } else {
                    $upd = Mst_branch::where('id', '=', $id)
                        ->update([
                            'initial' => $initial,
                            'name' => $name,
                            'slug' => SlugService::createSlug(Mst_branch::class, 'slug', $name),
                            'province_id' => $province_id,
                            'city_id' => $city_id,
                            'district_id' => $district_id,
                            'sub_district_id' => $sub_district_id,
                            'address' => $address,
                            'post_code' => $post_code,
                            'phone1' => $phone1,
                            'phone2' => $phone2,
                            'active' => $active,
                            'updated_by' => Auth::user()->id
                        ]);
                }
            }
        }
    }
}
