<?php

namespace App\Imports\admin;

use App\Models\Mst_supplier;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\ToCollection;
use \Cviebrock\EloquentSluggable\Services\SlugService;

class SupplierImport implements ToCollection
{
    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            $supplier_type_id = $row[6]==9999?11:10;
            $entity_type_id = $row[2];
            $supplier_code = $row[3];
            $name = $row[4];
            $office_address = !is_null($row[5])?$row[5]:'<no address>';
            $country_id = $row[6];
            $province_id = is_numeric($row[8])?$row[8]:'9999';
            $city_id = is_numeric($row[10])?$row[10]:'9999';
            $district_id = is_numeric($row[12])?$row[12]:'9999';
            $sub_district_id = is_numeric($row[14])?$row[14]:'9999';
            $post_code = !is_null($row[15])?$row[15]:'00000';
            $supplier_email = !is_null($row[16])?$row[16]:'<no email>';
            $phone1 = !is_null($row[17])?$row[17]:'00000';
            $phone2 = !is_null($row[18])?$row[18]:'00000';
            $pic1_name = !is_null($row[19])?$row[19]:'<no pic>';
            $pic1_phone = !is_null($row[20])?$row[20]:'00000';
            $pic1_email = !is_null($row[21])?$row[21]:'<no email>';
            $pic2_name = !is_null($row[22])?$row[22]:'<no pic>';
            $pic2_phone = !is_null($row[23])?$row[23]:'00000';
            $pic2_email = !is_null($row[24])?$row[24]:'<no email>';
            $npwp_no = !is_null($row[25])?$row[25]:null;
            $npwp_address = null;
            $npwp_province_id = null;
            $npwp_city_id = null;
            $npwp_district_id = null;
            $npwp_sub_district_id = null;
            $credit_limit = is_numeric($row[26])?$row[26]:0;
            $limit_balance = !is_null($row[27])?$row[27]:0;
            $top = is_numeric($row[28])?$row[28]:0;

            if ($supplier_code!='SUPPL CODE' && $supplier_code!=3){
                $qSupplier = Mst_supplier::where([
                    'supplier_code'=>$supplier_code,
                ])
                ->first();
                if ($qSupplier){
                        $insSupplier = Mst_supplier::where([
                            'supplier_code'=>$supplier_code,
                        ])
                        ->update([
                            'supplier_type_id'=>$supplier_type_id,
                            'entity_type_id'=>$entity_type_id,
                            'name'=>$name,
                            'supplier_code'=>$supplier_code,
                            'office_address'=>$office_address,
                            'country_id'=>$country_id,
                            'province_id'=>$province_id,
                            'city_id'=>$city_id,
                            'district_id'=>$district_id,
                            'sub_district_id'=>$sub_district_id,
                            'post_code'=>$post_code,
                            'supplier_email'=>$supplier_email,
                            'phone1'=>$phone1,
                            'phone2'=>$phone2,
                            'pic1_name'=>$pic1_name,
                            'pic1_phone'=>$pic1_phone,
                            'pic1_email'=>$pic1_email,
                            'pic2_name'=>$pic2_name,
                            'pic2_phone'=>$pic2_phone,
                            'pic2_email'=>$pic2_email,
                            'npwp_no'=>$npwp_no,
                            'npwp_address'=>$npwp_address,
                            'npwp_province_id'=>$npwp_province_id,
                            'npwp_city_id'=>$npwp_city_id,
                            'npwp_district_id'=>$npwp_district_id,
                            'npwp_sub_district_id'=>$npwp_sub_district_id,
                            'top'=>$top,
                            'credit_limit'=>$credit_limit,
                            'limit_balance'=>$limit_balance,
                            'active'=>'Y',
                            'updated_by'=>Auth::user()->id,
                        ]);
                }else{
                    $insSupplier = Mst_supplier::create([
                        'supplier_type_id'=>$supplier_type_id,
                        'entity_type_id'=>$entity_type_id,
                        'name'=>$name,
                        'supplier_code'=>$supplier_code,
                        'office_address'=>$office_address,
                        'country_id'=>$country_id,
                        'province_id'=>$province_id,
                        'city_id'=>$city_id,
                        'district_id'=>$district_id,
                        'sub_district_id'=>$sub_district_id,
                        'post_code'=>$post_code,
                        'supplier_email'=>$supplier_email,
                        'phone1'=>$phone1,
                        'phone2'=>$phone2,
                        'pic1_name'=>$pic1_name,
                        'pic1_phone'=>$pic1_phone,
                        'pic1_email'=>$pic1_email,
                        'pic2_name'=>$pic2_name,
                        'pic2_phone'=>$pic2_phone,
                        'pic2_email'=>$pic2_email,
                        'npwp_no'=>$npwp_no,
                        'npwp_address'=>$npwp_address,
                        'npwp_province_id'=>$npwp_province_id,
                        'npwp_city_id'=>$npwp_city_id,
                        'npwp_district_id'=>$npwp_district_id,
                        'npwp_sub_district_id'=>$npwp_sub_district_id,
                        'top'=>$top,
                        'credit_limit'=>$credit_limit,
                        'limit_balance'=>$limit_balance,
                        'active'=>'Y',
                        'created_by'=>Auth::user()->id,
                        'updated_by'=>Auth::user()->id,
                    ]);
                }
            }
        }
    }
}
