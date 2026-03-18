<?php

namespace App\Imports\admin;

use App\Models\Mst_salesman;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\ToCollection;
use \Cviebrock\EloquentSluggable\Services\SlugService;

class SalesmanImport implements ToCollection
{
    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            $id = $row[0];
            $name = $row[1];
            $branch_id = $row[3];
            $address = $row[5];
            $province_id = $row[6];
            $city_id = $row[8];
            $district_id = $row[10];
            $sub_district_id = $row[12];
            $email = $row[14];
            $post_code = str_replace("'", "", $row[15]);
            $IdNo = str_replace("'", "", $row[16]);
            $mobilephone = str_replace("'", "", $row[17]);
            $gender_id = $row[18];
            $birth_date = $row[20];
            $join_date = $row[21];
            $sales_target = $row[22];
            $active = $row[23];
            if (strtolower($id) != 'id') {
                $query = Mst_salesman::where('id', '=', $id)->first();
                if (!$query) {
                    $ins = Mst_salesman::create([
                        'name' => $name,
                        'branch_id' => $branch_id,
                        'province_id' => $province_id,
                        'city_id' => $city_id,
                        'district_id' => $district_id,
                        'sub_district_id' => $sub_district_id,
                        'address' => $address,
                        'post_code' => $post_code,
                        'id_no' => $IdNo,
                        'email' => $email,
                        'gender_id' => $gender_id,
                        'mobilephone' => $mobilephone,
                        'birth_date' => $birth_date,
                        'join_date' => $join_date,
                        'sales_target' => $sales_target,
                        'active' => $active,
                        'created_by' => Auth::user()->id,
                        'updated_by' => Auth::user()->id
                    ]);
                } else {
                    $upd = Mst_salesman::where('id', '=', $id)
                        ->update([
                            'name' => $name,
                            'slug' => SlugService::createSlug(Mst_salesman::class, 'slug', $name),
                            'branch_id' => $branch_id,
                            'province_id' => $province_id,
                            'city_id' => $city_id,
                            'district_id' => $district_id,
                            'sub_district_id' => $sub_district_id,
                            'address' => $address,
                            'post_code' => $post_code,
                            'id_no' => $IdNo,
                            'email' => $email,
                            'gender_id' => $gender_id,
                            'mobilephone' => $mobilephone,
                            'birth_date' => $birth_date,
                            'join_date' => $join_date,
                            'sales_target' => $sales_target,
                            'active' => $active,
                            'updated_by' => Auth::user()->id,
                        ]);
                }
            }
        }
    }
}
