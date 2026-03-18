<?php

namespace App\Imports\admin;

use App\Models\Mst_courier;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\ToCollection;
use \Cviebrock\EloquentSluggable\Services\SlugService;

class CourierImport implements ToCollection
{
    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            $id = $row[0];
            $entityTypeId = $row[1];
            $name = $row[3];
            $office_address = $row[5];
            $province_id = $row[6];
            $city_id = $row[8];
            $district_id = $row[10];
            $sub_district_id = $row[12];
            $post_code = str_replace("'", "", $row[14]);
            $courier_email = $row[15];
            $phone1 = str_replace("'", "", $row[16]);
            $phone2 = str_replace("'", "", $row[17]);
            $pic_name = $row[18];
            $pic_phone = str_replace("'", "", $row[19]);
            $pic_email = $row[20];
            $npwp_no = $row[21];
            $npwp_address = $row[22];
            $npwp_province_id = $row[23];
            $npwp_city_id = $row[25];
            $npwp_district_id = $row[27];
            $npwp_sub_district_id = $row[29];
            $active = $row[31];
            if (strtolower($id) != 'id') {
                $query = Mst_courier::where('id', '=', $id)->first();
                if (!$query) {
                    $ins = Mst_courier::create([
                        'entity_type_id' => $entityTypeId,
                        'name' => $name,
                        'office_address' => $office_address,
                        'province_id' => $province_id,
                        'city_id' => $city_id,
                        'district_id' => $district_id,
                        'sub_district_id' => $sub_district_id,
                        'post_code' => $post_code,
                        'courier_email' => $courier_email,
                        'phone1' => $phone1,
                        'phone2' => $phone2,
                        'pic1_name' => $pic_name,
                        'pic1_phone' => $pic_phone,
                        'pic1_email' => $pic_email,
                        'npwp_no' => $npwp_no,
                        'npwp_address' => $npwp_address,
                        'npwp_province_id' => $npwp_province_id,
                        'npwp_city_id' => $npwp_city_id,
                        'npwp_district_id' => $npwp_district_id,
                        'npwp_sub_district_id' => $npwp_sub_district_id,
                        'active' => $active,
                        'created_by' => Auth::user()->id,
                        'updated_by' => Auth::user()->id,
                    ]);
                } else {
                    $upd = Mst_courier::where('id', '=', $id)
                        ->update([
                            'entity_type_id' => $entityTypeId,
                            'name' => $name,
                            'slug' => SlugService::createSlug(Mst_courier::class, 'slug', $name),
                            'office_address' => $office_address,
                            'province_id' => $province_id,
                            'city_id' => $city_id,
                            'district_id' => $district_id,
                            'sub_district_id' => $sub_district_id,
                            'post_code' => $post_code,
                            'courier_email' => $courier_email,
                            'phone1' => $phone1,
                            'phone2' => $phone2,
                            'pic1_name' => $pic_name,
                            'pic1_phone' => $pic_phone,
                            'pic1_email' => $pic_email,
                            'npwp_no' => $npwp_no,
                            'npwp_address' => $npwp_address,
                            'npwp_province_id' => $npwp_province_id,
                            'npwp_city_id' => $npwp_city_id,
                            'npwp_district_id' => $npwp_district_id,
                            'npwp_sub_district_id' => $npwp_sub_district_id,
                            'active' => $active,
                            'updated_by' => Auth::user()->id,
                        ]);
                }
            }
        }
    }
}
