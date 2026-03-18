<?php

namespace App\Imports\admin;

use App\Models\Mst_company;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\ToCollection;
use \Cviebrock\EloquentSluggable\Services\SlugService;

class CompanyImport implements ToCollection
{
    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            $id = $row[0];
            $name = $row[1];
            $office_address = $row[3];
            $province_id = $row[4];
            $city_id = $row[6];
            $district_id = $row[8];
            $sub_district_id = $row[10];
            $post_code = str_replace("'", "", $row[12]);
            $company_email = $row[13];
            $phone1 = str_replace("'", "", $row[14]);
            $phone2 = str_replace("'", "", $row[15]);
            $npwp_no = $row[16];
            $npwp_address = $row[17];
            $npwp_province_id = $row[18];
            $npwp_city_id = $row[20];
            $npwp_district_id = $row[22];
            $npwp_sub_district_id = $row[24];
            $active = $row[26];
            if (strtolower($id) != 'id') {
                $query = Mst_company::where('id', '=', $id)->first();
                if (!$query) {
                    $ins = Mst_company::create([
                        'name' => $name,
                        'office_address' => $office_address,
                        'province_id' => $province_id,
                        'city_id' => $city_id,
                        'district_id' => $district_id,
                        'sub_district_id' => $sub_district_id,
                        'post_code' => $post_code,
                        'company_email' => $company_email,
                        'phone1' => $phone1,
                        'phone2' => $phone2,
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
                    $upd = Mst_company::where('id', '=', $id)
                        ->update([
                            'name' => $name,
                            'slug' => SlugService::createSlug(Mst_company::class, 'slug', $name),
                            'office_address' => $office_address,
                            'province_id' => $province_id,
                            'city_id' => $city_id,
                            'district_id' => $district_id,
                            'sub_district_id' => $sub_district_id,
                            'post_code' => $post_code,
                            'company_email' => $company_email,
                            'phone1' => $phone1,
                            'phone2' => $phone2,
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
